<?php

namespace App\Services;

use App\Models\Tenant\KitchenBatch;
use App\Models\Tenant\Order;
use App\Models\Tenant\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class KitchenBatchService
{
    public const MODE_DEDICATED_KDS = 'dedicated_kds';
    public const MODE_INLINE = 'inline';
    public const CHANNEL_BOARD = 'board';
    public const CHANNEL_PRINT = 'print';
    public const CHANNEL_BOARD_AND_PRINT = 'board_and_print';

    private const STATUSES = ['waiting', 'pending', 'preparing', 'ready', 'served', 'cancelled'];
    private const OPERATION_MODES = [self::MODE_DEDICATED_KDS, self::MODE_INLINE];
    private const DISPATCH_CHANNELS = [self::CHANNEL_BOARD, self::CHANNEL_PRINT, self::CHANNEL_BOARD_AND_PRINT];
    private const CANCELLABLE_STATUSES = ['waiting', 'pending', 'sent', 'in_kitchen'];

    public function sendFreshItems(Order $order, string $dispatchChannel = self::CHANNEL_BOARD): KitchenBatch
    {
        return DB::transaction(function () use ($order, $dispatchChannel) {
            $lockedOrder = Order::with(['items.product', 'table', 'tableSession'])
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->dining_flow !== 'table_service') {
                throw ValidationException::withMessages([
                    'order' => 'Send to kitchen is only available for table-service orders.',
                ]);
            }

            if (in_array($lockedOrder->status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'Completed or cancelled order cannot be sent to kitchen.',
                ]);
            }

            if (!$lockedOrder->table_id || !$lockedOrder->tableSession || $lockedOrder->tableSession->status !== 'active') {
                throw ValidationException::withMessages([
                    'table_session' => 'Order must have an active table session before sending to kitchen.',
                ]);
            }

            $freshItems = $lockedOrder->items()
                ->where(function ($query) {
                    $query->whereNull('kitchen_status')
                        ->orWhere('kitchen_status', 'pending');
                })
                ->whereNull('kitchen_batch_id')
                ->where(function ($query) {
                    $query->whereNull('item_status')
                        ->orWhereNotIn('item_status', ['cancelled', 'voided']);
                })
                ->where('quantity', '>', 0)
                ->lockForUpdate()
                ->get();

            if ($freshItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'No new items to send to kitchen.',
                ]);
            }

            $businessDate = $this->resolveBusinessDate($lockedOrder);
            $nextNumber = $this->nextBatchNumber($businessDate);
            $batch = KitchenBatch::create([
                'location_id' => $lockedOrder->location_id,
                'order_id' => $lockedOrder->id,
                'table_session_id' => $lockedOrder->table_session_id,
                'table_id' => $lockedOrder->table_id,
                'batch_number' => $nextNumber,
                'batch_code' => $this->batchCode($nextNumber),
                'business_date' => $businessDate,
                'status' => 'waiting',
                'dispatch_channel' => $this->normalizeDispatchChannel($dispatchChannel),
                'sent_at' => now(),
            ]);

            $freshItems->each(function ($item) use ($batch) {
                $item->update([
                    'kitchen_batch_id' => $batch->id,
                    'kitchen_status' => 'sent',
                    'sent_to_kitchen_at' => now(),
                ]);
            });

            return $batch->fresh(['order.table', 'table', 'tableSession', 'items.product']);
        });
    }

    public function updateStatus(KitchenBatch $batch, string $status): KitchenBatch
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => 'Invalid kitchen batch status.',
            ]);
        }

        DB::transaction(function () use ($batch, $status) {
            $lockedBatch = KitchenBatch::whereKey($batch->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedBatch->update(['status' => $status]);

            $lockedBatch->items()->update([
                'kitchen_status' => $status,
            ]);
        });

        return $batch->fresh(['order.table', 'table', 'tableSession', 'items.product']);
    }

    public function cancelBatch(KitchenBatch $batch): KitchenBatch
    {
        DB::transaction(function () use ($batch) {
            $lockedBatch = KitchenBatch::with('items')
                ->whereKey($batch->id)
                ->lockForUpdate()
                ->firstOrFail();

            $status = strtolower((string) $lockedBatch->status);
            if (! in_array($status, self::CANCELLABLE_STATUSES, true)) {
                throw ValidationException::withMessages([
                    'batch' => 'Only waiting kitchen batches can be cancelled safely.',
                ]);
            }

            $lockedBatch->items()->lockForUpdate()->get()->each(function ($item) {
                $item->update([
                    'kitchen_batch_id' => null,
                    'kitchen_status' => 'pending',
                    'sent_to_kitchen_at' => null,
                ]);
            });

            $lockedBatch->update(['status' => 'cancelled']);
        });

        return $batch->fresh(['order.table', 'table', 'tableSession', 'items.product']);
    }

    public function printPayload(KitchenBatch $batch): array
    {
        $batch->loadMissing([
            'location',
            'order.location',
            'order.tableSession.tables',
            'table',
            'tableSession.tables',
            'tableSession.primaryTable',
            'tableSession.linkedTables',
            'items.product',
        ]);

        $order = $batch->order;

        return [
            'id' => $batch->id,
            'batch_id' => $batch->id,
            'batch_code' => $batch->batch_code,
            'batch_number' => $batch->batch_number,
            'dispatch_channel' => $batch->dispatch_channel ?? self::CHANNEL_BOARD,
            'business_date' => optional($batch->business_date)->toDateString(),
            'status' => $batch->status,
            'sent_at' => optional($batch->sent_at)->toISOString(),
            'created_at' => optional($batch->created_at)->toISOString(),
            'order' => [
                'id' => $order?->id,
                'order_no' => $order?->order_no,
                'notes' => $order?->notes,
                'guest_count' => $order?->guest_count,
            ],
            'location' => [
                'id' => $batch->location_id,
                'name' => optional($order?->location ?: $batch->location)->name,
            ],
            'table' => $batch->table ? [
                'id' => $batch->table->id,
                'name' => $batch->table->name,
                'code' => $batch->table->code,
            ] : null,
            'table_display' => $this->tableDisplayForPayload($batch),
            'items' => $batch->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => optional($item->product)->name,
                'name' => optional($item->product)->name,
                'sku' => optional($item->product)->sku,
                'quantity' => $item->quantity,
                'qty' => $item->quantity,
                'kitchen_status' => $item->kitchen_status,
                'item_status' => $item->item_status,
                'notes' => $item->notes ?? $item->note ?? null,
                'variant' => $item->variant ?? null,
                'modifiers' => $item->modifiers ?? null,
            ])->values()->all(),
        ];
    }

    public function operationMode(): string
    {
        $mode = (string) Setting::get('kitchen_operation_mode', null, self::MODE_DEDICATED_KDS);

        return in_array($mode, self::OPERATION_MODES, true)
            ? $mode
            : self::MODE_DEDICATED_KDS;
    }

    public function shouldBroadcastToKds(?KitchenBatch $batch = null): bool
    {
        return $this->operationMode() === self::MODE_DEDICATED_KDS
            && $this->shouldDispatchToBoard($batch?->dispatch_channel);
    }

    public function normalizeDispatchChannel(?string $channel): string
    {
        $channel = strtolower(trim((string) $channel));

        return in_array($channel, self::DISPATCH_CHANNELS, true)
            ? $channel
            : self::CHANNEL_BOARD;
    }

    public function shouldDispatchToBoard(?string $channel): bool
    {
        return in_array($this->normalizeDispatchChannel($channel), [
            self::CHANNEL_BOARD,
            self::CHANNEL_BOARD_AND_PRINT,
        ], true);
    }

    public function resolveBusinessDate(?Order $order = null): string
    {
        if ($order && $order->business_date) {
            return Carbon::parse($order->business_date)->toDateString();
        }

        $date = Setting::get('current_business_date')
            ?? Setting::get('business_date')
            ?? Setting::get('shift_date');

        if ($date) {
            return Carbon::parse($date)->toDateString();
        }

        $businessDayStart = Setting::get('business_day_start_time')
            ?? Setting::get('day_start_time');

        if ($businessDayStart) {
            $now = now();
            $start = Carbon::parse($now->toDateString().' '.$businessDayStart);

            return $now->lt($start)
                ? $now->copy()->subDay()->toDateString()
                : $now->toDateString();
        }

        return today()->toDateString();
    }

    private function nextBatchNumber(string $businessDate): int
    {
        $last = KitchenBatch::whereDate('business_date', $businessDate)
            ->lockForUpdate()
            ->latest('id')
            ->first();

        return $last ? ((int) $last->batch_number + 1) : 1;
    }

    private function batchCode(int $number): string
    {
        return 'KOT-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    private function tableDisplayForPayload(KitchenBatch $batch): ?string
    {
        $session = $batch->tableSession ?: $batch->order?->tableSession;

        if ($session) {
            $session->loadMissing(['tables']);

            if ($session->table_display) {
                return $session->table_display;
            }

            $names = $session->tables
                ->map(fn ($table) => $table->name ?: $table->code)
                ->filter()
                ->values();

            if ($names->isNotEmpty()) {
                return $names->join(', ');
            }
        }

        return $batch->table ? ($batch->table->name ?: $batch->table->code) : null;
    }
}
