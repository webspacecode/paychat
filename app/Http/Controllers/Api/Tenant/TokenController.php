<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\OrderToken;
use App\Constants\TokenStatus;

class TokenController extends Controller
{
    public function show($tokenCode)
    {
        $token = OrderToken::with('order')
            ->where('token_code', $tokenCode)
            ->firstOrFail();

        return response()->json([
            'token' => $token,
            'order' => $token->order
        ]);
    }

    public function updateStatus(Request $request, $tokenCode)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', TokenStatus::all())
        ]);

        $token = OrderToken::where('token_code', $tokenCode)
            ->firstOrFail();

        $token->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Updated',
            'token' => $token
        ]);
    }
}