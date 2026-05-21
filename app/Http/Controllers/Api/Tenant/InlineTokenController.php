<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Order;
use App\Services\TokenService;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Generator;

class InlineTokenController extends Controller
{
    public function store(string $tenantSlug, Order $order, TokenService $tokenService)
    {
        $order->load(['table', 'tableSession', 'token']);

        if ($order->order_type !== 'dine_in') {
            throw ValidationException::withMessages([
                'order' => 'Inline token is only available for dine-in orders.',
            ]);
        }

        if ($order->dining_flow !== 'table_service') {
            throw ValidationException::withMessages([
                'order' => 'Inline token is only available for table-service orders.',
            ]);
        }

        if (in_array($order->status, ['completed', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'order' => 'Completed or cancelled order cannot generate an inline token.',
            ]);
        }

        if (!$order->table_id || !$order->tableSession || $order->tableSession->status !== 'active') {
            throw ValidationException::withMessages([
                'table_session' => 'Order must have an active table session before generating an inline token.',
            ]);
        }

        $token = $order->token ?: $tokenService->generateInlineKitchenToken($order);
        $order = $order->fresh(['table', 'tableSession', 'token']);

        return response()->json([
            'message' => 'Inline token generated',
            'token' => $this->tokenPayload($tenantSlug, $token),
            'order' => [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'dining_flow' => $order->dining_flow,
                'table' => $order->table ? [
                    'id' => $order->table->id,
                    'name' => $order->table->name,
                    'code' => $order->table->code,
                ] : null,
                'guest_count' => $order->guest_count,
            ],
        ]);
    }

    private function tokenPayload(string $tenantSlug, $token): array
    {
        $publicUrl = url('/pos#/kitchen-order-status?'.http_build_query([
            'tenant' => $tenantSlug,
            'token' => $token->token_code,
        ]));

        return [
            'id' => $token->id,
            'token_code' => $token->token_code,
            'status' => $token->status,
            'public_url' => $publicUrl,
            'qr_url' => $this->qrDataUri($publicUrl),
        ];
    }

    private function qrDataUri(string $url): ?string
    {
        try {
            $svg = (new Generator())->format('svg')->size(240)->generate($url);

            return 'data:image/svg+xml;base64,'.base64_encode($svg);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
