<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Models\Tenant;
use App\Models\Review;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\InvoiceService;
use App\Http\Controllers\Controller;

class InfoController extends Controller
{
    public function index(Request $req)
    {   
        $apiKey = $req->header('x-api-key');

        $tenant = Tenant::with(['branding', 'taxConfig'])
            ->where('api_key', $apiKey)
            ->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API Key'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                    'logo' => $tenant->logo,
                    'api_key' => $tenant->api_key,
                    'address' => $tenant->address,
                    'industry' => $tenant->industry,
                ],

                'branding' => $tenant->branding,

                'tax' => $tenant->taxConfig,
            ]
        ]);
    }

    public function list(Request $req)
    {   

        $tenants = Tenant::with(['branding', 'taxConfig'])
        ->whereNotNull('api_key')
        ->where('api_key', '!=', '')
        ->get();

        if (!$tenants) {
            return response()->json([
                'success' => false,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenants' => $tenants
            ]
        ]);
    }

    public function welcome(Request $req)
    {   

        $tenants = Tenant::with(['branding', 'taxConfig'])
        ->whereNotNull('api_key')
        ->where('api_key', '!=', '')
        ->get();

        /*
        |--------------------------------------------------------------------------
        | Attach Review Stats
        |--------------------------------------------------------------------------
        */

        $tenants->transform(function ($tenant) {

            $reviewsQuery = Review::where('tenant_id', $tenant->id)
                ->where('is_approved', true);

            $tenant->reviews_avg_rating = round(
                (float) $reviewsQuery->avg('rating'),
                1
            );

            $tenant->reviews_count = $reviewsQuery->count();

            $tenant->featured_reviews = $reviewsQuery
                ->latest()
                ->take(3)
                ->get([
                    'rating',
                    'review_text',
                    'created_at'
                ]);

            return $tenant;
        });

        if (!$tenants) {
            return response()->json([
                'success' => false,
            ], 404);
        }

        return view('welcome', ['tenants' => $tenants]);
    }

    public function storePage($slug)
    {
        $tenant = Tenant::with('branding')
            ->get()
            ->first(function ($t) use ($slug) {

                return Str::slug($t->name) === $slug;
            });

        if (!$tenant) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Reviews
        |--------------------------------------------------------------------------
        */

        $reviewsQuery = Review::where('tenant_id', $tenant->id)
            ->where('is_approved', true);

        $reviews = $reviewsQuery
            ->latest()
            ->paginate(10);

        $avgRating = round(
            (float) $reviewsQuery->avg('rating'),
            1
        );

        $totalReviews = $reviewsQuery->count();

        /*
        |--------------------------------------------------------------------------
        | Rating Breakdown
        |--------------------------------------------------------------------------
        */

        $ratingBreakdown = [];

        for ($i = 5; $i >= 1; $i--) {

            $ratingBreakdown[$i] = Review::where(
                'tenant_id',
                $tenant->id
            )
            ->where('rating', $i)
            ->where('is_approved', true)
            ->count();
        }

        return view('store', [

            'tenant' => $tenant,

            'reviews' => $reviews,

            'avgRating' => $avgRating,

            'totalReviews' => $totalReviews,

            'ratingBreakdown' => $ratingBreakdown,
        ]);
    }
}