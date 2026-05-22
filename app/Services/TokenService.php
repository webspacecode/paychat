<?php

namespace App\Services;

use App\Models\Tenant\Order;
use App\Models\Tenant\OrderToken;
use App\Models\Tenant\Setting;
use App\Constants\TokenStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TokenService
{
    private const QSR_ORDER_TYPES = ['takeaway', 'delivery'];
    private const DINE_IN_ORDER_TYPE = 'dine_in';
    private const TABLE_SERVICE_FLOW = 'table_service';

    public function isEnabled(): bool
    {
        return (bool) Setting::get('token_system_enabled', null, false);
    }

    public function generate($order)
    {
        if (! $order instanceof Order) {
            return null;
        }

        if (! $this->shouldGenerateQsrToken($order)) {
            return null;
        }

        return $this->createForOrder($order);
    }

    public function generateInlineKitchenToken(Order $order): ?OrderToken
    {
        return $this->generate($order);
    }

    public function shouldGenerateQsrToken(Order $order): bool
    {
        if (! $this->isEnabled()) {
            $this->logSkipped('token.generation.skipped_disabled', $order);
            return false;
        }

        if ($order->status === 'cancelled') {
            $this->logSkipped('token.generation.skipped_cancelled_order', $order);
            return false;
        }

        if ($this->isTableService($order)) {
            $this->logSkipped('token.generation.skipped_table_service', $order);
            return false;
        }

        if (! $this->isQsrOrderType($order)) {
            $this->logSkipped('token.generation.skipped_order_type', $order);
            return false;
        }

        if ($this->itemCount($order) === 0) {
            $this->logSkipped('token.generation.skipped_empty_order', $order);
            return false;
        }

        return true;
    }

    private function createForOrder(Order $order): OrderToken
    {
        return DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingToken = OrderToken::where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->first();

            if ($existingToken) {
                if ((int) $lockedOrder->token_id !== (int) $existingToken->id) {
                    $lockedOrder->update([
                        'token_id' => $existingToken->id,
                    ]);
                }

                Log::debug('Token generation skipped: token already exists', [
                    ...$this->logContext($lockedOrder),
                    'token_id' => $existingToken->id,
                    'token_code' => $existingToken->token_code,
                ]);

                return $existingToken;
            }

            $prefix = Setting::get('token_prefix', null, 'A');
            $start = (int) Setting::get('token_start_number', null, 100);
            $resetDaily = Setting::get('token_reset_daily', null, true);

            $query = OrderToken::query();

            if ($resetDaily) {
                $query->whereDate('created_at', today());
            }

            $last = $query->lockForUpdate()->latest('id')->first();

            $nextNumber = $last ? ((int) $last->token_number + 1) : $start;

            $token = OrderToken::create([
                'order_id' => $lockedOrder->id,
                'token_number' => $nextNumber,
                'token_code' => $prefix . $nextNumber,
                'token_date' => today(),
                'status' => TokenStatus::WAITING
            ]);

            $lockedOrder->update([
                'token_id' => $token->id
            ]);

            Log::info('token.generation.created', [
                ...$this->logContext($lockedOrder),
                'token_id' => $token->id,
                'token_code' => $token->token_code,
            ]);

            return $token;
        });
    }

    private function isQsrOrderType(Order $order): bool
    {
        $orderType = strtolower((string) $order->order_type);

        return in_array($orderType, self::QSR_ORDER_TYPES, true)
            || $orderType === self::DINE_IN_ORDER_TYPE;
    }

    private function isTableService(Order $order): bool
    {
        return strtolower((string) $order->dining_flow) === self::TABLE_SERVICE_FLOW;
    }

    private function itemCount(Order $order): int
    {
        return $order->items()
            ->where('quantity', '>', 0)
            ->count();
    }

    private function logSkipped(string $event, Order $order): void
    {
        Log::debug($event, $this->logContext($order));
    }

    private function logContext(Order $order): array
    {
        return [
            'request_id' => $this->requestId(),
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'order_type' => $order->order_type,
            'dining_flow' => $order->dining_flow,
            'item_count' => $this->itemCount($order),
        ];
    }

    private function requestId(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request->attributes->get('request_id')
            ?: $request->headers->get('X-Request-ID');
    }
}
