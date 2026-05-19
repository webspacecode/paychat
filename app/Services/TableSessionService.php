<?php

namespace App\Services;

use App\Models\Tenant\Order;
use App\Models\Tenant\Resource;
use App\Models\Tenant\TableSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TableSessionService
{
    public function create(array $data): TableSession
    {
        return DB::transaction(function () use ($data) {
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

            $table->update(['status' => 'occupied']);

            if (!empty($data['order_id'])) {
                $order = Order::whereKey($data['order_id'])->lockForUpdate()->firstOrFail();
                $this->linkOrder($session, $order, $session->guest_count);
            }

            return $session->fresh(['table', 'order']);
        });
    }

    public function assignOrder(Order $order, int $tableId, ?int $guestCount = null): TableSession
    {
        return DB::transaction(function () use ($order, $tableId, $guestCount) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedOrder->status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Completed or cancelled order cannot be assigned to a table.',
                ]);
            }

            $table = $this->lockTable($tableId);

            if ((int) $table->location_id !== (int) $lockedOrder->location_id) {
                throw ValidationException::withMessages([
                    'table_id' => 'Table does not belong to the order location.',
                ]);
            }

            $session = $lockedOrder->tableSession;

            if ($session && $session->status === 'active') {
                if ((int) $session->table_id !== $tableId) {
                    $this->ensureNoActiveSession($tableId);
                    optional($session->table)->update(['status' => 'available']);
                    $session->update(['table_id' => $tableId]);
                }

                if ($guestCount !== null) {
                    $session->update(['guest_count' => $guestCount]);
                }
            } else {
                $this->ensureNoActiveSession($tableId);

                $session = TableSession::create([
                    'location_id' => $lockedOrder->location_id,
                    'table_id' => $tableId,
                    'order_id' => $lockedOrder->id,
                    'guest_count' => $guestCount,
                    'status' => 'active',
                    'opened_at' => now(),
                ]);
            }

            $table->update(['status' => 'occupied']);
            $this->linkOrder($session->fresh(), $lockedOrder, $guestCount);

            return $session->fresh(['table', 'order']);
        });
    }

    public function close(TableSession $session): TableSession
    {
        return DB::transaction(function () use ($session) {
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

            optional($lockedSession->table)->update(['status' => 'available']);

            return $lockedSession->fresh(['table', 'order']);
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
        return DB::transaction(function () use ($table, $force) {
            $lockedTable = $this->lockTable($table->id);
            $activeSession = TableSession::where('table_id', $lockedTable->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

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
            }

            $lockedTable->update(['status' => 'available']);

            return $lockedTable->fresh();
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

    private function ensureNoActiveSession(int $tableId): void
    {
        $exists = TableSession::where('table_id', $tableId)
            ->where('status', 'active')
            ->lockForUpdate()
            ->exists();

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
