<?php

namespace Tests\Feature;

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
}
