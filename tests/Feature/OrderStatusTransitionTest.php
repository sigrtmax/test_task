<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $customer = Customer::factory()->create();
        $this->order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::New,
        ]);
    }

    public function test_valid_status_transition_new_to_confirmed(): void
    {
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertNotNull($response->json('data.confirmed_at'));
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_invalid_status_transition_new_to_shipped(): void
    {
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'shipped',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);

        // Status should not change
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'new',
        ]);
    }

    public function test_cancellation_from_new(): void
    {
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'cancelled',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_cancellation_from_shipped_fails(): void
    {
        $shippedOrder = Order::factory()->shipped()->create([
            'customer_id' => $this->order->customer_id,
        ]);

        $response = $this->patchJson("/api/v1/orders/{$shippedOrder->id}/status", [
            'status' => 'cancelled',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('orders', [
            'id' => $shippedOrder->id,
            'status' => 'shipped',
        ]);
    }

    public function test_full_order_lifecycle(): void
    {
        // new -> confirmed
        $this->patchJson("/api/v1/orders/{$this->order->id}/status", ['status' => 'confirmed'])
            ->assertStatus(200);

        // confirmed -> processing
        $this->patchJson("/api/v1/orders/{$this->order->id}/status", ['status' => 'processing'])
            ->assertStatus(200);

        // processing -> shipped
        $this->patchJson("/api/v1/orders/{$this->order->id}/status", ['status' => 'shipped'])
            ->assertStatus(200);

        // shipped -> completed
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", ['status' => 'completed'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_order_not_found_returns_404(): void
    {
        $response = $this->patchJson('/api/v1/orders/99999/status', [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function test_invalid_status_value_returns_422(): void
    {
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
