<?php

namespace App\Http\Controllers\Api\Tenant;

use Illuminate\Http\Request;
use App\Models\Tenant\WebhookLog;
use App\Models\Tenant\Payment;
use App\Models\Tenant\Order;
use App\Models\Tenant\Transaction;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
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
    }
}
