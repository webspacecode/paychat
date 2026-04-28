<?php

namespace App\Http\Controllers\Api\Tenant;

use Illuminate\Http\Request;
use App\Models\Tenant\Payment;
use App\Services\Payments\PaymentService;

class PhonePeController extends Controller
{
    public function callback(Request $request)
    {
        try {

            if (!$request->has('response')) {
                return response()->json(['status' => 'ignored']);
            }

            $decoded = json_decode(
                base64_decode($request->input('response')),
                true
            );

            $txnId = $decoded['data']['merchantTransactionId'] ?? null;
            $state = $decoded['data']['state'] ?? null;

            $payment = Payment::where('provider_ref', $txnId)->first();

            if (!$payment) {
                return response()->json(['status' => 'not found']);
            }

            if ($state === 'COMPLETED') {

                app(PaymentService::class)->markPaymentSuccess($payment);

            } elseif ($state === 'FAILED') {

                $payment->update(['status' => 'failed']);
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {

            \Log::error("PhonePe Callback Error", [
                'error' => $e->getMessage()
            ]);

            return response()->json(['status' => 'error']);
        }
    }

    public function checkPhonePeStatus($txnId, $config)
    {
        $merchantId = $config['merchant_id'];
        $saltKey    = $config['salt_key'];
        $saltIndex  = $config['salt_index'];

        $endpoint = "/pg/v1/status/$merchantId/$txnId";

        $checksum = hash('sha256', $endpoint . $saltKey) . "###" . $saltIndex;

        $response = \Http::withHeaders([
            "X-VERIFY" => $checksum,
            "X-MERCHANT-ID" => $merchantId
        ])->get("https://api.phonepe.com/apis/hermes" . $endpoint);

        return $response->json();
    }

    public function checkStatus($txnId, $config)
    {
        $baseUrl   = config('services.phonepe.base_url');
        $merchantId = $config['merchant_id'];
        $saltKey    = $config['salt_key'];
        $saltIndex  = $config['salt_index'];

        $endpoint = "/pg/v1/status/$merchantId/$txnId";

        $checksum = hash('sha256', $endpoint . $saltKey)
            . "###" . $saltIndex;

        $url = $baseUrl . $endpoint;

        $response = \Http::withHeaders([
            "X-VERIFY" => $checksum,
            "X-MERCHANT-ID" => $merchantId
        ])->get($url);

        return $response->json();
    }
}