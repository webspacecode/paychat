<?php

namespace App\Services;

use App\Models\Tenant\Order;
use App\Models\Tenant\OrderToken;
use App\Models\Tenant\Setting;
use App\Constants\TokenStatus;
use App\Support\Observability;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TokenService
{
    public const STAGE_DRAFT_CREATED = 'draft_created';
    public const STAGE_ITEMS_SYNCED = 'items_synced';
    public const STAGE_SELF_POS_SUBMITTED = 'self_pos_submitted';
    public const STAGE_PAYMENT_SUCCESS = 'payment_success';
    public const STAGE_OFFLINE_COMPLETED = 'offline_completed';

    private const QSR_ORDER_TYPES = ['takeaway', 'delivery'];
    private const DINE_IN_ORDER_TYPE = 'dine_in';
    private const QUICK_COUNTER_FLOW = 'quick_counter';
    private const TABLE_SERVICE_FLOW = 'table_service';
    private const GENERATION_STAGES = [self::STAGE_PAYMENT_SUCCESS, self::STAGE_OFFLINE_COMPLETED];

    public function isEnabled(): bool
    {
        return (bool) Setting::get('token_system_enabled', null, false);
    }

    public function generate($order, string $stage = self::STAGE_PAYMENT_SUCCESS)
    {
        if (! $order instanceof Order) {
            return null;
        }

        if (! $this->shouldGenerateQsrToken($order, $stage)) {
            return null;
        }

        return $this->createForOrder($order, $stage);
    }

    public function generateInlineKitchenToken(Order $order): ?OrderToken
    {
        return $this->generate($order, self::STAGE_PAYMENT_SUCCESS);
    }

    public function generateForSelfPosSubmission(Order $order): ?OrderToken
    {
        return $this->generate($order, self::STAGE_SELF_POS_SUBMITTED);
    }

    public function shouldGenerateQsrToken(Order $order, string $stage): bool
    {
        $context = $this->logContext($order, $stage);

        $isSelfPosSubmission = $stage === self::STAGE_SELF_POS_SUBMITTED && $this->isSelfPosOrder($order);

        if (! $isSelfPosSubmission && ! in_array($stage, self::GENERATION_STAGES, true)) {
            Log::debug('token.generation.skipped_stage', $context);
            return false;
        }

        if (! $this->isEnabled()) {
            Log::debug('token.generation.skipped_disabled', $context);
            return false;
        }

        if ($order->status === 'cancelled') {
            Log::debug('token.generation.skipped_cancelled_order', $context);
            return false;
        }

        if ($this->isTableService($order) && ! $isSelfPosSubmission) {
            Log::debug('token.generation.skipped_table_service', $context);
            return false;
        }

        if (! $isSelfPosSubmission && ! $this->isQsrOrderType($order)) {
            Log::debug('token.generation.skipped_order_type', $context);
            return false;
        }

        if ($this->itemCount($order) === 0) {
            Log::debug('token.generation.skipped_empty_order', $context);
            return false;
        }

        if (! $isSelfPosSubmission && $order->payment_status !== 'paid') {
            Log::debug('token.generation.skipped_payment_status', $context);
            return false;
        }

        return true;
    }

    private function createForOrder(Order $order, string $stage): OrderToken
    {
        return DB::transaction(function () use ($order, $stage) {
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

                Log::info('token.generation.reused_after_payment', [
                    ...$this->logContext($lockedOrder, $stage),
                    'token_id' => $existingToken->id,
                    'token_code' => $existingToken->token_code,
                ]);

                Observability::logInfo('token.generated', [
                    ...$this->logContext($lockedOrder, $stage),
                    'token_id' => $existingToken->id,
                    'token_code' => $existingToken->token_code,
                    'reused' => true,
                ]);

                return $existingToken;
            }

            $prefix = Setting::get('token_prefix', null, 'A');
            $start = (int) Setting::get('token_start_number', null, 100);
            $resetDaily = Setting::get('token_reset_daily', null, true);
            $businessDate = $lockedOrder->business_date
                ? $lockedOrder->business_date->toDateString()
                : today()->toDateString();

            $query = OrderToken::query();

            if ($resetDaily) {
                $query->whereDate('token_date', $businessDate);
            }

            $last = $query->lockForUpdate()->latest('id')->first();

            $nextNumber = $last ? ((int) $last->token_number + 1) : $start;

            $token = OrderToken::create([
                'order_id' => $lockedOrder->id,
                'token_number' => $nextNumber,
                'token_code' => $prefix . $nextNumber,
                'token_date' => $businessDate,
                'status' => TokenStatus::WAITING
            ]);

            $lockedOrder->update([
                'token_id' => $token->id
            ]);

            Log::info('token.generation.created_after_payment', [
                ...$this->logContext($lockedOrder, $stage),
                'token_id' => $token->id,
                'token_code' => $token->token_code,
            ]);

            Observability::logInfo('token.generated', [
                ...$this->logContext($lockedOrder, $stage),
                'token_id' => $token->id,
                'token_code' => $token->token_code,
                'reused' => false,
            ]);

            return $token;
        });
    }

    private function isQsrOrderType(Order $order): bool
    {
        $orderType = strtolower(trim((string) $order->order_type));
        $diningFlow = strtolower(trim((string) $order->dining_flow));

        if (in_array($orderType, self::QSR_ORDER_TYPES, true)) {
            return true;
        }

        return $orderType === self::DINE_IN_ORDER_TYPE
            && $diningFlow === self::QUICK_COUNTER_FLOW;
    }

    private function isTableService(Order $order): bool
    {
        return strtolower(trim((string) $order->dining_flow)) === self::TABLE_SERVICE_FLOW;
    }

    private function isSelfPosOrder(Order $order): bool
    {
        return strtolower(trim((string) $order->source)) === 'self_pos'
            || data_get($order->meta, 'source') === 'self_pos'
            || data_get($order->meta, 'self_pos.submitted') === true;
    }

    private function itemCount(Order $order): int
    {
        return $order->items()
            ->where('quantity', '>', 0)
            ->count();
    }

    private function logContext(Order $order, ?string $stage = null): array
    {
        return [
            'request_id' => $this->requestId(),
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'order_type' => $order->order_type,
            'dining_flow' => $order->dining_flow,
            'stage' => $stage,
            'payment_status' => $order->payment_status,
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
