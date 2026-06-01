<?php

namespace Tests\Feature;

use App\Exceptions\PaymentException;
use App\Models\Tenant\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiExceptionRenderingTest extends TestCase
{
    public function test_api_order_not_found_errors_are_rendered_with_a_friendly_message(): void
    {
        Route::get('/api/__test/missing-order', function () {
            throw (new ModelNotFoundException())->setModel(Order::class, [75]);
        });

        $response = $this->getJson('/api/__test/missing-order');

        $response
            ->assertNotFound()
            ->assertHeader('X-Request-ID')
            ->assertJsonPath('message', 'Order not found. Please create a new order.')
            ->assertJsonPath('code', 'ORDER_NOT_FOUND')
            ->assertJsonStructure(['message', 'support_code', 'code']);
    }

    public function test_payment_business_errors_are_rendered_as_unprocessable_json(): void
    {
        Route::post('/api/__test/payment-error', function () {
            throw new PaymentException(
                'Amount exceeds remaining payment',
                'PAYMENT_AMOUNT_EXCEEDS_REMAINING',
                422,
                [
                    'requested_amount' => 4000.0,
                    'remaining_amount' => 3500.0,
                ]
            );
        });

        $response = $this->postJson('/api/__test/payment-error');

        $response
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Amount exceeds remaining payment')
            ->assertJsonPath('code', 'PAYMENT_AMOUNT_EXCEEDS_REMAINING')
            ->assertJsonPath('details.requested_amount', 4000)
            ->assertJsonPath('details.remaining_amount', 3500);
    }
}
