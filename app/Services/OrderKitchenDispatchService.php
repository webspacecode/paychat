<?php

namespace App\Services;

use App\Events\OrderCreated;
use App\Models\Tenant\Order;
use App\Models\Tenant\OrderToken;
use App\Support\Observability;
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
            Observability::logWarningMessage('token.dispatch.warning', [
                'safe_message' => 'Kitchen token dispatch skipped: order does not exist',
                'module' => 'token',
                'reason' => $reason,
            ]);
            return null;
        }

        $stage = $this->stageFromReason($reason);
        $token = $this->tokenService->generate($order, $stage);

        if (! $token) {
            Log::debug('Kitchen token dispatch skipped: token unavailable', [
                'order_id' => $order->id,
                'reason' => $reason,
                'stage' => $stage,
            ]);
            return null;
        }

        $this->dispatchOnceWhenReady($order, $token, $reason, $stage);

        return $token;
    }

    private function dispatchOnceWhenReady(Order $order, OrderToken $token, string $reason, string $stage): void
    {
        DB::transaction(function () use ($order, $token, $reason, $stage) {
            $lockedOrder = Order::whereKey($order->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder) {
                Observability::logWarningMessage('token.dispatch.warning', [
                    'safe_message' => 'Kitchen token dispatch skipped: order not found',
                    'module' => 'token',
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
            $lastDispatchReason = $meta[self::DISPATCH_REASON_META_KEY] ?? null;
            $isFinalDispatch = in_array($stage, [
                TokenService::STAGE_PAYMENT_SUCCESS,
                TokenService::STAGE_OFFLINE_COMPLETED,
            ], true);
            $alreadyFinalDispatched = in_array($lastDispatchReason, [
                'payment_success',
                'offline_order_synced',
            ], true);

            if (
                ! empty($meta[self::DISPATCHED_META_KEY])
                && (! $isFinalDispatch || $alreadyFinalDispatched || $lastDispatchReason === $reason)
            ) {
                Log::debug('Kitchen token dispatch skipped: already dispatched', [
                    'order_id' => $lockedOrder->id,
                    'token_id' => $token->id,
                    'reason' => $reason,
                    'stage' => $stage,
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

            DB::afterCommit(function () use ($dispatchOrder, $dispatchToken, $reason, $stage) {
                try {
                    event(new OrderCreated($dispatchOrder, $dispatchToken));
                } catch (\Throwable $e) {
                    Log::warning('token.generation.broadcast_failed', [
                        'request_id' => $this->requestId(),
                        'order_id' => $dispatchOrder->id,
                        'order_no' => $dispatchOrder->order_no,
                        'token_id' => $dispatchToken->id,
                        'token_code' => $dispatchToken->token_code,
                        'reason' => $reason,
                        'stage' => $stage,
                        'error_code' => 'broadcast_failed',
                        'error' => $e->getMessage(),
                    ]);
                }

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
                    'stage' => $stage,
                ]);

                Log::info('token.dispatch.after_payment', [
                    'request_id' => $this->requestId(),
                    'order_id' => $dispatchOrder->id,
                    'order_no' => $dispatchOrder->order_no,
                    'order_type' => $dispatchOrder->order_type,
                    'dining_flow' => $dispatchOrder->dining_flow,
                    'stage' => $stage,
                    'payment_status' => $dispatchOrder->payment_status,
                    'item_count' => $dispatchOrder->items->count(),
                    'token_id' => $dispatchToken->id,
                    'token_code' => $dispatchToken->token_code,
                ]);
            });
        });
    }

    private function stageFromReason(string $reason): string
    {
        return match ($reason) {
            'classic_pos_order_created' => TokenService::STAGE_DRAFT_CREATED,
            'classic_pos_items_synced' => TokenService::STAGE_ITEMS_SYNCED,
            'self_pos_submitted' => TokenService::STAGE_SELF_POS_SUBMITTED,
            'offline_order_synced' => TokenService::STAGE_OFFLINE_COMPLETED,
            default => TokenService::STAGE_PAYMENT_SUCCESS,
        };
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
