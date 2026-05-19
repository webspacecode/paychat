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
    private const STATUSES = ['waiting', 'preparing', 'ready', 'served', 'cancelled'];

    public function sendFreshItems(Order $order): KitchenBatch
    {
        return DB::transaction(function () use ($order) {
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
            $nextNumber = $this->nextBatchNumber($lockedOrder->location_id, $businessDate);
            $batch = KitchenBatch::create([
                'location_id' => $lockedOrder->location_id,
                'order_id' => $lockedOrder->id,
                'table_session_id' => $lockedOrder->table_session_id,
                'table_id' => $lockedOrder->table_id,
                'batch_number' => $nextNumber,
                'batch_code' => $this->batchCode($nextNumber, $businessDate),
                'business_date' => $businessDate,
                'status' => 'waiting',
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

        $batch->update(['status' => $status]);

        return $batch->fresh(['order.table', 'table', 'tableSession', 'items.product']);
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

    private function nextBatchNumber(int $locationId, string $businessDate): int
    {
        $last = KitchenBatch::where('location_id', $locationId)
            ->whereDate('business_date', $businessDate)
            ->lockForUpdate()
            ->latest('id')
            ->first();

        return $last ? ((int) $last->batch_number + 1) : 1;
    }

    private function batchCode(int $number, string $businessDate): string
    {
        return 'KB'.Carbon::parse($businessDate)->format('ymd').'-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }
}
