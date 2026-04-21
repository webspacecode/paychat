<?php

namespace App\Services;

use App\Models\Tenant\OrderToken;
use App\Models\Tenant\Setting;
use App\Constants\TokenStatus;
use Illuminate\Support\Facades\DB;

class TokenService
{
    public function generate($order)
    {
        $enabled = Setting::get('token_system_enabled', null, false);

        if (!$enabled) {
            return null;
        }

        return DB::transaction(function () use ($order) {

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
                'order_id' => $order->id,
                'token_number' => $nextNumber,
                'token_code' => $prefix . $nextNumber,
                'token_date' => today(),
                'status' => TokenStatus::WAITING
            ]);

            $order->update([
                'token_id' => $token->id
            ]);

            return $token;
        });
    }
}