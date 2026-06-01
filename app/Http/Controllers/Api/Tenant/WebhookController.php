<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\WebhookLog;
use App\Models\Tenant\Payment;
use App\Models\Tenant\Transaction;
use App\Support\Observability;
use Throwable;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            $log = WebhookLog::create([
                'provider'=>$request->provider ?? 'unknown',
                'payload'=>json_encode($request->all()),
                'status'=>'received'
            ]);

            if($request->payment_id && $request->status){
                $payment = Payment::find($request->payment_id);
                $payment->update(['status'=>$request->status]);
                Transaction::create([
                    'payment_id'=>$payment->id,
                    'transaction_ref'=>$request->transaction_ref ?? null,
                    'status'=>$request->status
                ]);

                $order = $payment->order;
                if($request->status === 'success'){
                    $order->update(['payment_status'=>'paid','status'=>'paid']);
                }
            }

            $log->update(['status'=>'processed']);
            return response()->json(['success'=>true]);
        } catch (Throwable $e) {
            Observability::logFailure('webhook.failed', $e, [
                'module' => 'payment',
                'safe_message' => 'Webhook processing failed',
                'provider' => $request->provider ?? 'unknown',
                'payment_id' => $request->payment_id,
                'webhook_status' => $request->status,
            ], $request);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'support_code' => Observability::requestId($request),
            ], 422);
        }
    }
}
