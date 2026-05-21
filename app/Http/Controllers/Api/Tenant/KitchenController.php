<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\OrderToken;

class KitchenController extends Controller
{
    public function index()
    {
        $tokens = OrderToken::with('order')
            ->whereHas('order', fn ($query) => $query
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) {
                    $q->whereNull('dining_flow')
                        ->orWhere('dining_flow', '!=', 'table_service');
                }))
            ->whereDate('created_at', today())
            ->get()
            ->groupBy('status');

        return response()->json([
            'waiting' => $tokens['waiting'] ?? [],
            'pending' => $tokens['pending'] ?? [],
            'preparing' => $tokens['preparing'] ?? [],
            'ready' => $tokens['ready'] ?? []
        ]);
    }
}
