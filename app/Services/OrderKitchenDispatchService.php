<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Tenant\Order;
use App\Models\Tenant\OrderToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderKitchenDispatchService
{
    private const DISPATCHED_META_KEY = 'kitchen_token_dispatched_at';
    private const DISPATCH_REASON_META_KEY = 'kitchen_token_dispatch_reason';

    public function __construct(private TokenService $tokenService)
    {
    }

    public function ensureTokenAndDispatchWhenReady(Order $order, string $reason): ?OrderToken
    {
        if (! $order->exists) {
            Log::warning('Kitchen token dispatch skipped: order does not exist');
            return null;
        }

        $token = $this->tokenService->generate($order);

        if (! $token) {
            Log::debug('Kitchen token dispatch skipped: token unavailable', [
                'order_id' => $order->id,
                'reason' => $reason,
            ]);
            return null;
        }

        $this->dispatchOnceWhenReady($order, $token, $reason);

        return $token;
    }

    private function dispatchOnceWhenReady(Order $order, OrderToken $token, string $reason): void
    {
        DB::transaction(function () use ($order, $token, $reason) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder) {
                Log::warning('Kitchen token dispatch skipped: order not found', [
                    'order_id' => $order->id,
                    'reason' => $reason,
                ]);
                return;
            }

            if (! $this->tokenService->isEnabled()) {
                Log::debug('Kitchen token dispatch skipped: token management disabled', [
                    'order_id' => $lockedOrder->id,
                    'reason' => $reason,
                ]);
                return;
            }

            $currentToken = $lockedOrder->token()->lockForUpdate()->first();

            if (! $currentToken) {
                Log::debug('Kitchen token dispatch skipped: order has no token', [
                    'order_id' => $lockedOrder->id,
                    'reason' => $reason,
                ]);
                return;
            }

            if ((int) $currentToken->id !== (int) $token->id) {
                $token = $currentToken;
            }

            if ($lockedOrder->items()->where('quantity', '>', 0)->count() === 0) {
                Log::debug('Kitchen token dispatch skipped: order has no items yet', [
                    'order_id' => $lockedOrder->id,
                    'token_id' => $token->id,
                    'reason' => $reason,
                ]);
                return;
            }

            $meta = $lockedOrder->meta ?? [];

            if (! empty($meta[self::DISPATCHED_META_KEY])) {
                Log::debug('Kitchen token dispatch skipped: already dispatched', [
                    'order_id' => $lockedOrder->id,
                    'token_id' => $token->id,
                    'reason' => $reason,
                ]);
                return;
            }

            $meta[self::DISPATCHED_META_KEY] = now()->toISOString();
            $meta[self::DISPATCH_REASON_META_KEY] = $reason;

            $lockedOrder->forceFill(['meta' => $meta])->save();

            $dispatchOrder = $lockedOrder->fresh([
                'items.product',
                'customer',
                'location',
                'payments',
                'table',
                'tableSession',
                'token',
            ]);
            $dispatchToken = $token->fresh();

            DB::afterCommit(function () use ($dispatchOrder, $dispatchToken, $reason) {
                event(new OrderCreated($dispatchOrder, $dispatchToken));

                Log::info('token.generation.dispatched', [
                    'request_id' => $this->requestId(),
                    'order_id' => $dispatchOrder->id,
                    'order_no' => $dispatchOrder->order_no,
                    'order_type' => $dispatchOrder->order_type,
                    'dining_flow' => $dispatchOrder->dining_flow,
                    'item_count' => $dispatchOrder->items->count(),
                    'token_id' => $dispatchToken->id,
                    'token_code' => $dispatchToken->token_code,
                    'reason' => $reason,
                ]);
            });
        });
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
