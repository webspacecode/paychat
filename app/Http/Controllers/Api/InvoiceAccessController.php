<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceAccessService;
use Illuminate\Http\Request;

class InvoiceAccessController extends Controller
{
    public function status(string $uuid, InvoiceAccessService $access)
    {
        $invoice = Invoice::where('uuid', $uuid)->firstOrFail();

        return response()->json([
            'requires_verification' => $access->requiresVerification($invoice),
            'invoice_age_hours' => $invoice->created_at?->diffInHours(now()) ?? 0,
            'has_registered_phone' => $access->registeredPhone($invoice) !== null,
        ]);
    }

    public function verify(Request $request, string $uuid, InvoiceAccessService $access)
    {
        $invoice = Invoice::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        if (! $access->phoneMatches($invoice, $validated['phone'])) {
            return response()->json([
                'message' => 'The phone number does not match this invoice.',
            ], 422);
        }

        return response()->json([
            'access_token' => $access->issueToken($invoice),
        ]);
    }
}
