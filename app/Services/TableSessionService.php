<?php

namespace App\Services;

use App\Models\Tenant\Order;
use App\Models\Tenant\Resource;
use App\Models\Tenant\TableSession;
use App\Models\Tenant\TableSessionTable;
use App\Support\Observability;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class TableSessionService
{
    private const TABLE_GROUP_RELATIONS = ['table', 'tables', 'primaryTable', 'linkedTables', 'order'];

    public function create(array $data): TableSession
    {
        $startedAt = microtime(true);

        return DB::transaction(function () use ($data, $startedAt) {
            $table = $this->lockTable((int) $data['table_id']);
            $locationId = (int) $data['location_id'];

            if ((int) $table->location_id !== $locationId) {
                throw ValidationException::withMessages([
                    'table_id' => 'Table does not belong to the selected location.',
                ]);
            }

            $this->ensureNoActiveSession($table->id);

            $session = TableSession::create([
                'location_id' => $locationId,
                'table_id' => $table->id,
                'order_id' => $data['order_id'] ?? null,
                'guest_count' => $data['guest_count'] ?? null,
                'status' => 'active',
                'opened_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->ensurePrimarySessionTable($session, $table->id, $session->opened_at);
            $table->update(['status' => 'occupied']);

            if (!empty($data['order_id'])) {
                $order = Order::whereKey($data['order_id'])->lockForUpdate()->firstOrFail();
                $this->linkOrder($session, $order, $session->guest_count);
            }

            $session = $session->fresh(['table', 'order']);

            Observability::logInfo('table.session.created', [
                'location_id' => $session->location_id,
                'table_id' => $session->table_id,
                'table_session_id' => $session->id,
                'order_id' => $session->order_id,
                'guest_count' => $session->guest_count,
                'duration_ms' => Observability::durationMs($startedAt),
            ]);

            return $session;
        });
    }

    public function assignOrder(Order $order, int $tableId, ?int $guestCount = null): TableSession
    {
        return $this->assignOrderTables($order, $tableId, [], $guestCount, true, false);
    }

    public function assignOrderTables(
        Order $order,
        int $primaryTableId,
        array $linkedTableIds = [],
        ?int $guestCount = null,
        bool $allowPrimaryTransfer = false,
        bool $requireDineIn = true
    ): TableSession
    {
        $startedAt = microtime(true);

        return DB::transaction(function () use ($order, $primaryTableId, $linkedTableIds, $guestCount, $allowPrimaryTransfer, $requireDineIn, $startedAt) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedOrder->status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Completed or cancelled order cannot be assigned to a table.',
                ]);
            }

            if ($requireDineIn && ! $this->isTableServiceEligible($lockedOrder)) {
                throw ValidationException::withMessages([
                    'order' => 'Only dine-in table-service orders can link multiple tables.',
                ]);
            }

            $linkedTableIds = $this->normalizeLinkedTableIds($primaryTableId, $linkedTableIds);
            if (! $this->hasTableSessionTables() && ! empty($linkedTableIds)) {
                throw ValidationException::withMessages([
                    'linked_table_ids' => 'Linked tables require the table session tables migration.',
                ]);
            }

            $activeTableIds = array_values(array_unique(array_merge([$primaryTableId], $linkedTableIds)));
            $tables = $this->lockTables($activeTableIds);

            $session = $lockedOrder->tableSession;

            if (
                $session &&
                $session->status === 'active' &&
                ! $allowPrimaryTransfer &&
                (int) $session->table_id !== $primaryTableId
            ) {
                throw ValidationException::withMessages([
                    'primary_table_id' => 'Changing the primary table for an active table group is not supported.',
                ]);
            }

            foreach ($tables as $table) {
                if ((int) $table->location_id !== (int) $lockedOrder->location_id) {
                    throw ValidationException::withMessages([
                        'table_ids' => 'All tables must belong to the order location.',
                    ]);
                }
            }

            $currentSessionId = $session && $session->status === 'active' ? (int) $session->id : null;
            $this->ensureTablesAvailableForSession($activeTableIds, $currentSessionId);

            if ($session && $session->status === 'active') {
                if ((int) $session->table_id !== $primaryTableId) {
                    $session->update(['table_id' => $primaryTableId]);
                }

                if ($guestCount !== null) {
                    $session->update(['guest_count' => $guestCount]);
                }
            } else {
                $session = TableSession::create([
                    'location_id' => $lockedOrder->location_id,
                    'table_id' => $primaryTableId,
                    'order_id' => $lockedOrder->id,
                    'guest_count' => $guestCount,
                    'status' => 'active',
                    'opened_at' => now(),
                ]);
            }

            $session = $session->fresh();
            $this->syncSessionTables($session, $primaryTableId, $linkedTableIds);
            $this->markTablesOccupied($activeTableIds);
            $this->linkOrder($session, $lockedOrder, $guestCount);

            $session = $session->fresh(self::TABLE_GROUP_RELATIONS);

            Observability::logInfo('table.group.assigned', [
                'order_id' => $lockedOrder->id,
                'location_id' => $lockedOrder->location_id,
                'table_id' => $primaryTableId,
                'table_ids' => $activeTableIds,
                'linked_table_ids' => $linkedTableIds,
                'table_session_id' => $session->id,
                'guest_count' => $guestCount ?? $session->guest_count,
                'duration_ms' => Observability::durationMs($startedAt),
            ]);

            return $session;
        });
    }

    public function close(TableSession $session): TableSession
    {
        $startedAt = microtime(true);

        return DB::transaction(function () use ($session, $startedAt) {
            $lockedSession = TableSession::whereKey($session->id)->lockForUpdate()->firstOrFail();

            if ($lockedSession->status === 'closed') {
                return $lockedSession->fresh(['table', 'order']);
            }

            $order = $lockedSession->order;

            if ($order && $order->payment_status !== 'paid') {
                throw ValidationException::withMessages([
                    'order' => 'Cannot close table session while linked order is unpaid.',
                ]);
            }

            $lockedSession->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            $this->releaseSessionTables($lockedSession);

            $closed = $lockedSession->fresh(self::TABLE_GROUP_RELATIONS);

            Observability::logInfo('table.session.closed', [
                'location_id' => $closed->location_id,
                'table_id' => $closed->table_id,
                'table_session_id' => $closed->id,
                'order_id' => $closed->order_id,
                'duration_ms' => Observability::durationMs($startedAt),
            ]);

            return $closed;
        });
    }

    public function closeForOrder(Order $order): ?TableSession
    {
        $session = $order->tableSession;

        if (!$session || $session->status !== 'active') {
            return $session;
        }

        return $this->close($session);
    }

    public function release(Resource $table, bool $force = false): Resource
    {
        $startedAt = microtime(true);

        return DB::transaction(function () use ($table, $force, $startedAt) {
            $lockedTable = $this->lockTable($table->id);
            $activeSession = $this->findActiveSessionForTable($lockedTable->id);
            $this->releaseInactiveSessionLinksForTable($lockedTable->id);
            $releaseSource = $activeSession && (int) $activeSession->table_id !== (int) $lockedTable->id
                ? 'linked'
                : ($activeSession ? 'primary' : 'none');

            if ($activeSession && !$force) {
                $order = $activeSession->order;

                if (!$order || $order->payment_status !== 'paid') {
                    throw ValidationException::withMessages([
                        'table' => 'Cannot release table while an unpaid active order exists.',
                    ]);
                }
            }

            if ($activeSession) {
                $activeSession->update([
                    'status' => $force ? 'cancelled' : 'closed',
                    'closed_at' => now(),
                ]);

                $this->releaseSessionTables($activeSession);
            }

            if (! $activeSession && ! $this->hasActiveSessionLink($lockedTable->id)) {
                $lockedTable->update(['status' => 'available']);
            }

            $released = $lockedTable->fresh();

            Observability::logInfo('table.released', [
                'location_id' => $released->location_id,
                'table_id' => $released->id,
                'table_session_id' => $activeSession?->id,
                'order_id' => $activeSession?->order_id,
                'release_source' => $releaseSource,
                'forced' => $force,
                'duration_ms' => Observability::durationMs($startedAt),
            ]);

            return $released;
        });
    }

    private function linkOrder(TableSession $session, Order $order, ?int $guestCount = null): void
    {
        $session->update(['order_id' => $order->id]);

        $order->update([
            'table_id' => $session->table_id,
            'table_session_id' => $session->id,
            'guest_count' => $guestCount ?? $session->guest_count,
            'dining_flow' => 'table_service',
        ]);
    }

    private function ensurePrimarySessionTable(TableSession $session, int $tableId, $joinedAt = null): void
    {
        if (! Schema::connection('tenant')->hasTable('table_session_tables')) {
            return;
        }

        TableSessionTable::query()
            ->where('table_session_id', $session->id)
            ->where('role', 'primary')
            ->whereNull('released_at')
            ->where('table_id', '!=', $tableId)
            ->update([
                'released_at' => now(),
                'updated_at' => now(),
            ]);

        $this->upsertSessionTable($session, $tableId, 'primary', $joinedAt ?? now());
    }

    private function syncSessionTables(TableSession $session, int $primaryTableId, array $linkedTableIds): void
    {
        if (! $this->hasTableSessionTables()) {
            return;
        }

        $now = now();
        $activeTableIds = array_values(array_unique(array_merge([$primaryTableId], $linkedTableIds)));

        $releasedTableIds = TableSessionTable::query()
            ->where('table_session_id', $session->id)
            ->whereNull('released_at')
            ->whereNotIn('table_id', $activeTableIds)
            ->pluck('table_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        TableSessionTable::query()
            ->where('table_session_id', $session->id)
            ->whereNull('released_at')
            ->whereNotIn('table_id', $activeTableIds)
            ->update([
                'released_at' => $now,
                'updated_at' => $now,
            ]);

        if (! empty($releasedTableIds)) {
            Resource::query()
                ->whereIn('id', $releasedTableIds)
                ->where('type', 'table')
                ->update(['status' => 'available']);
        }

        $this->releaseDuplicateActiveSessionTables($session, $activeTableIds, $now);
        $this->upsertSessionTable($session, $primaryTableId, 'primary', $now);

        foreach ($linkedTableIds as $tableId) {
            $this->upsertSessionTable($session, $tableId, 'linked', $now);
        }
    }

    private function upsertSessionTable(TableSession $session, int $tableId, string $role, $joinedAt): void
    {
        $activeLinks = TableSessionTable::query()
            ->where('table_session_id', $session->id)
            ->where('table_id', $tableId)
            ->whereNull('released_at')
            ->orderBy('id')
            ->get();

        $link = $activeLinks->first();

        if ($activeLinks->count() > 1) {
            $duplicateIds = $activeLinks->skip(1)->pluck('id')->values()->all();

            TableSessionTable::query()
                ->whereIn('id', $duplicateIds)
                ->update([
                    'released_at' => now(),
                    'updated_at' => now(),
                ]);

            Observability::logWarningMessage('table.session.duplicate_active_pivots_released', [
                'table_session_id' => $session->id,
                'table_id' => $tableId,
                'kept_pivot_id' => $link?->id,
                'released_pivot_ids' => $duplicateIds,
            ]);
        }

        if (! $link) {
            $link = TableSessionTable::firstOrNew([
                'table_session_id' => $session->id,
                'table_id' => $tableId,
            ]);
        }

        if (! $link->exists || ! $link->joined_at || $link->released_at) {
            $link->joined_at = $joinedAt;
        }

        $link->role = $role;
        $link->released_at = null;
        $link->save();
    }

    private function normalizeLinkedTableIds(int $primaryTableId, array $linkedTableIds): array
    {
        $linkedTableIds = collect($linkedTableIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (in_array($primaryTableId, $linkedTableIds, true)) {
            throw ValidationException::withMessages([
                'linked_table_ids' => 'Linked tables cannot include the primary table.',
            ]);
        }

        return $linkedTableIds;
    }

    private function lockTables(array $tableIds)
    {
        $tableIds = collect($tableIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $tables = Resource::query()
            ->whereIn('id', $tableIds)
            ->where('type', 'table')
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($tables->count() !== count($tableIds)) {
            throw ValidationException::withMessages([
                'table_ids' => 'One or more selected tables were not found.',
            ]);
        }

        $inactive = $tables->contains(fn ($table) => in_array($table->status, ['inactive', 'deleted'], true));

        if ($inactive) {
            throw ValidationException::withMessages([
                'table_ids' => 'One or more selected tables are not active.',
            ]);
        }

        return $tables;
    }

    private function ensureTablesAvailableForSession(array $tableIds, ?int $currentSessionId = null): void
    {
        $occupiedTableIds = [];

        if ($this->hasTableSessionTables()) {
            $occupiedTableIds = TableSessionTable::query()
                ->whereIn('table_id', $tableIds)
                ->whereNull('released_at')
                ->whereHas('tableSession', fn ($query) => $query->where('status', 'active'))
                ->when($currentSessionId, fn ($query) => $query->where('table_session_id', '!=', $currentSessionId))
                ->lockForUpdate()
                ->pluck('table_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $legacyOccupiedTableIds = TableSession::query()
            ->whereIn('table_id', $tableIds)
            ->where('status', 'active')
            ->when($currentSessionId, fn ($query) => $query->whereKeyNot($currentSessionId))
            ->lockForUpdate()
            ->pluck('table_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $occupiedTableIds = array_values(array_unique(array_merge(
            $occupiedTableIds,
            $legacyOccupiedTableIds
        )));

        if (! empty($occupiedTableIds)) {
            Observability::logWarningMessage('table.session.selected_tables_occupied', [
                'table_ids' => $tableIds,
                'conflicting_table_ids' => $occupiedTableIds,
                'current_table_session_id' => $currentSessionId,
            ]);

            throw ValidationException::withMessages([
                'table_ids' => 'One or more selected tables already have an active table session.',
            ]);
        }
    }

    private function markTablesOccupied(array $tableIds): void
    {
        Resource::query()
            ->whereIn('id', $tableIds)
            ->where('type', 'table')
            ->update(['status' => 'occupied']);
    }

    public function hydrateTableGroupContext($tables): void
    {
        if ($tables->isEmpty()) {
            return;
        }

        $tablesById = $tables->keyBy('id');
        $tableIds = $tablesById->keys()->map(fn ($id) => (int) $id)->values()->all();
        $sessionsByTableId = collect();

        if ($this->hasTableSessionTables()) {
            $links = TableSessionTable::query()
                ->with([
                    'tableSession' => function ($query) {
                        $query->where('status', 'active')
                            ->with(self::TABLE_GROUP_RELATIONS);
                    },
                ])
                ->whereIn('table_id', $tableIds)
                ->whereNull('released_at')
                ->whereHas('tableSession', fn ($query) => $query->where('status', 'active'))
                ->orderByRaw("case when role = 'primary' then 0 else 1 end")
                ->orderBy('id')
                ->get()
                ->groupBy('table_id');

            $links->each(function ($tableLinks, $tableId) use (&$sessionsByTableId) {
                if ($tableLinks->count() > 1) {
                    Observability::logWarningMessage('table.session.duplicate_active_table_links_detected', [
                        'table_id' => (int) $tableId,
                        'table_session_ids' => $tableLinks->pluck('table_session_id')->unique()->values()->all(),
                        'pivot_ids' => $tableLinks->pluck('id')->values()->all(),
                    ]);
                }

                $session = $tableLinks->first()?->tableSession;

                if ($session) {
                    $sessionsByTableId->put((int) $tableId, $session);
                }
            });
        }

        $tables->each(function ($table) use ($sessionsByTableId) {
            $session = $sessionsByTableId->get((int) $table->id)
                ?: ($table->relationLoaded('activeTableSession') ? $table->getRelation('activeTableSession') : null);

            if (! $session) {
                return;
            }

            $table->setRelation('activeTableSession', $session);

            if ($table->status === 'available') {
                $table->status = 'occupied';
            }
        });
    }

    private function findActiveSessionForTable(int $tableId): ?TableSession
    {
        if ($this->hasTableSessionTables()) {
            $this->releaseDuplicateActiveLinksForTable($tableId);

            $links = TableSessionTable::query()
                ->where('table_id', $tableId)
                ->whereNull('released_at')
                ->whereHas('tableSession', fn ($query) => $query->where('status', 'active'))
                ->orderByRaw("case when role = 'primary' then 0 else 1 end")
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($links->count() > 1) {
                Observability::logWarningMessage('table.session.duplicate_active_table_links_detected', [
                    'table_id' => $tableId,
                    'table_session_ids' => $links->pluck('table_session_id')->unique()->values()->all(),
                    'pivot_ids' => $links->pluck('id')->values()->all(),
                ]);
            }

            $sessionId = $links->first()?->table_session_id;

            if ($sessionId) {
                return TableSession::whereKey($sessionId)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();
            }
        }

        return TableSession::where('table_id', $tableId)
            ->where('status', 'active')
            ->lockForUpdate()
            ->first();
    }

    private function releaseSessionTables(TableSession $session): void
    {
        if (! $this->hasTableSessionTables()) {
            optional($session->table)->update(['status' => 'available']);
            return;
        }

        $now = now();
        $this->releaseDuplicateActiveSessionLinks($session, $now);

        $tableIds = TableSessionTable::query()
            ->where('table_session_id', $session->id)
            ->whereNull('released_at')
            ->pluck('table_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($tableIds) && $session->table_id) {
            $tableIds = [(int) $session->table_id];
        }

        TableSessionTable::query()
            ->where('table_session_id', $session->id)
            ->whereNull('released_at')
            ->update([
                'released_at' => $now,
                'updated_at' => $now,
            ]);

        if (! empty($tableIds)) {
            Resource::query()
                ->whereIn('id', $tableIds)
                ->where('type', 'table')
                ->whereDoesntHave('tableSessionLinks', function ($query) use ($session) {
                    $query->whereNull('released_at')
                        ->where('table_session_id', '!=', $session->id)
                        ->whereHas('tableSession', fn ($sessionQuery) => $sessionQuery->where('status', 'active'));
                })
                ->update(['status' => 'available']);
        }
    }

    private function releaseDuplicateActiveSessionTables(TableSession $session, array $tableIds, $releasedAt): void
    {
        $duplicates = TableSessionTable::query()
            ->where('table_session_id', $session->id)
            ->whereIn('table_id', $tableIds)
            ->whereNull('released_at')
            ->orderBy('table_id')
            ->orderBy('id')
            ->get()
            ->groupBy('table_id')
            ->filter(fn ($links) => $links->count() > 1);

        $duplicates->each(function ($links, $tableId) use ($session, $releasedAt) {
            $duplicateIds = $links->skip(1)->pluck('id')->values()->all();

            TableSessionTable::query()
                ->whereIn('id', $duplicateIds)
                ->update([
                    'released_at' => $releasedAt,
                    'updated_at' => now(),
                ]);

            Observability::logWarningMessage('table.session.duplicate_active_pivots_released', [
                'table_session_id' => $session->id,
                'table_id' => (int) $tableId,
                'kept_pivot_id' => $links->first()?->id,
                'released_pivot_ids' => $duplicateIds,
            ]);
        });
    }

    private function releaseDuplicateActiveLinksForTable(int $tableId): void
    {
        if (! $this->hasTableSessionTables()) {
            return;
        }

        $duplicates = TableSessionTable::query()
            ->where('table_id', $tableId)
            ->whereNull('released_at')
            ->whereHas('tableSession', fn ($query) => $query->where('status', 'active'))
            ->orderBy('table_session_id')
            ->orderByRaw("case when role = 'primary' then 0 else 1 end")
            ->orderBy('id')
            ->get()
            ->groupBy('table_session_id')
            ->filter(fn ($links) => $links->count() > 1);

        $duplicates->each(function ($links, $sessionId) {
            $duplicateIds = $links->skip(1)->pluck('id')->values()->all();

            TableSessionTable::query()
                ->whereIn('id', $duplicateIds)
                ->update([
                    'released_at' => now(),
                    'updated_at' => now(),
                ]);

            Observability::logWarningMessage('table.session.duplicate_active_pivots_released', [
                'table_session_id' => (int) $sessionId,
                'table_id' => (int) $links->first()->table_id,
                'kept_pivot_id' => $links->first()?->id,
                'released_pivot_ids' => $duplicateIds,
            ]);
        });
    }

    private function releaseDuplicateActiveSessionLinks(TableSession $session, $releasedAt): void
    {
        if (! $this->hasTableSessionTables()) {
            return;
        }

        $tableIds = TableSessionTable::query()
            ->where('table_session_id', $session->id)
            ->whereNull('released_at')
            ->pluck('table_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($tableIds)) {
            return;
        }

        $this->releaseDuplicateActiveSessionTables($session, $tableIds, $releasedAt);
    }

    private function releaseInactiveSessionLinksForTable(int $tableId): void
    {
        if (! $this->hasTableSessionTables()) {
            return;
        }

        $staleLinks = TableSessionTable::query()
            ->where('table_id', $tableId)
            ->whereNull('released_at')
            ->whereHas('tableSession', fn ($query) => $query->where('status', '!=', 'active'))
            ->get();

        if ($staleLinks->isEmpty()) {
            return;
        }

        TableSessionTable::query()
            ->whereIn('id', $staleLinks->pluck('id')->values()->all())
            ->update([
                'released_at' => now(),
                'updated_at' => now(),
            ]);

        Observability::logWarningMessage('table.session.stale_inactive_pivots_released', [
            'table_id' => $tableId,
            'table_session_ids' => $staleLinks->pluck('table_session_id')->unique()->values()->all(),
            'pivot_ids' => $staleLinks->pluck('id')->values()->all(),
        ]);
    }

    private function hasActiveSessionLink(int $tableId): bool
    {
        if (! $this->hasTableSessionTables()) {
            return false;
        }

        return TableSessionTable::query()
            ->where('table_id', $tableId)
            ->whereNull('released_at')
            ->whereHas('tableSession', fn ($query) => $query->where('status', 'active'))
            ->exists();
    }

    private function isTableServiceEligible(Order $order): bool
    {
        return strtolower((string) $order->order_type) === 'dine_in'
            || $order->dining_flow === 'table_service';
    }

    private function hasTableSessionTables(): bool
    {
        return Schema::connection('tenant')->hasTable('table_session_tables');
    }

    private function ensureNoActiveSession(int $tableId): void
    {
        $exists = TableSession::where('table_id', $tableId)
            ->where('status', 'active')
            ->lockForUpdate()
            ->exists();

        if (! $exists && $this->hasTableSessionTables()) {
            $exists = TableSessionTable::query()
                ->where('table_id', $tableId)
                ->whereNull('released_at')
                ->whereHas('tableSession', fn ($query) => $query->where('status', 'active'))
                ->lockForUpdate()
                ->exists();
        }

        if ($exists) {
            throw ValidationException::withMessages([
                'table_id' => 'Table already has an active session.',
            ]);
        }
    }

    private function lockTable(int $tableId): Resource
    {
        $table = Resource::whereKey($tableId)
            ->where('type', 'table')
            ->lockForUpdate()
            ->first();

        if (!$table) {
            throw ValidationException::withMessages([
                'table_id' => 'Table not found.',
            ]);
        }

        return $table;
    }
}
