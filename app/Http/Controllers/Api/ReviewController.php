<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Models\ReviewSession;
use App\Http\Controllers\Controller;



class ReviewController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([

            'uuid' => 'required|string',

            'rating' => 'required|integer|min:1|max:5',

            'comment' => 'nullable|string|max:1000',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Find Invoice
        |--------------------------------------------------------------------------
        */

        $invoice = Invoice::where(
            'uuid',
            $validated['uuid']
        )->first();

        if (!$invoice) {

            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Extract Review Token
        |--------------------------------------------------------------------------
        */

        $reviewToken = data_get(
            $invoice->order_data,
            'review_token'
        );

        if (!$reviewToken) {

            return response()->json([
                'success' => false,
                'message' => 'Review token missing'
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Find Review Session
        |--------------------------------------------------------------------------
        */

        $session = ReviewSession::where(
            'review_token',
            $reviewToken
        )->first();

        if (!$session) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid review token'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Reviews
        |--------------------------------------------------------------------------
        */

        if ($session->is_reviewed) {

            return response()->json([
                'success' => false,
                'message' => 'Review already submitted'
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Expiry Check
        |--------------------------------------------------------------------------
        */

        if (
            $session->expires_at &&
            now()->greaterThan($session->expires_at)
        ) {

            return response()->json([
                'success' => false,
                'message' => 'Review link expired'
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Store Review
        |--------------------------------------------------------------------------
        */

        $review = Review::create([

            'review_session_id' => $session->id,

            'tenant_id' => $session->tenant_id,

            'tenant_slug' => $session->tenant_slug,

            'rating' => $validated['rating'],

            'review_text' => $validated['comment'] ?? null,

            'ip_address' => $request->ip(),

            'user_agent' => $request->userAgent(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Mark Session Reviewed
        |--------------------------------------------------------------------------
        */

        $session->update([

            'is_reviewed' => true,

            'reviewed_at' => now(),
        ]);

        return response()->json([

            'success' => true,

            'message' => 'Review submitted successfully',

            'data' => $review
        ]);
    }
}
