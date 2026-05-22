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
    public function isEnabled(): bool
    {
        return (bool) Setting::get('token_system_enabled', null, false);
    }

    public function generate($order)
    {
        if (!$this->isEnabled()) {
            Log::debug('Token generation skipped: token management disabled', [
                'order_id' => $order?->id,
            ]);
            return null;
        }

        return $this->createForOrder($order);
    }

    public function generateInlineKitchenToken(Order $order): OrderToken
    {
        return $this->createForOrder($order);
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
                    'order_id' => $lockedOrder->id,
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

            Log::info('Token generated for order', [
                'order_id' => $lockedOrder->id,
                'token_id' => $token->id,
                'token_code' => $token->token_code,
            ]);

            return $token;
        });
    }
}
