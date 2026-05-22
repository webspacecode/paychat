<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InlineTokenController extends Controller
{
    public function store(string $tenantSlug, Order $order)
    {
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

        $this->logTableServiceSkip($order);

        throw ValidationException::withMessages([
            'order' => 'Table-service dine-in uses kitchen batches from Send to Kitchen instead of QSR tokens.',
        ]);
    }

    private function logTableServiceSkip(Order $order): void
    {
        Log::debug('token.generation.skipped_table_service', [
            'request_id' => $this->requestId(),
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'order_type' => $order->order_type,
            'dining_flow' => $order->dining_flow,
            'item_count' => $order->items()->where('quantity', '>', 0)->count(),
        ]);
    }

    private function requestId(): ?string
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request->attributes->get('request_id')
            ?: $request->headers->get('X-Request-ID');
    }
}
