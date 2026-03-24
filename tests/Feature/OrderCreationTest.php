<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;
    private Product $product1;
    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create();
        $this->product1 = Product::factory()->create([
            'price' => 45.00,
            'stock_quantity' => 50,
        ]);
        $this->product2 = Product::factory()->create([
            'price' => 60.75,
            'stock_quantity' => 30,
        ]);
    }

    public function test_successful_order_creation(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 2],
                ['product_id' => $this->product2->id, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer' => ['id', 'name', 'email'],
                    'status',
                    'total_amount',
                    'items' => [
                        '*' => ['id', 'product', 'quantity', 'unit_price', 'total_price'],
                    ],
                    'confirmed_at',
                    'shipped_at',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.status', OrderStatus::New->value);

        $this->assertEquals(150.75, $response->json('data.total_amount'));

        // Stock should be decremented
        $this->assertEquals(48, $this->product1->fresh()->stock_quantity);
        $this->assertEquals(29, $this->product2->fresh()->stock_quantity);

        // Order should exist in DB
        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
            'status' => OrderStatus::New->value,
        ]);

        // Order items should exist
        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product1->id,
            'quantity' => 2,
        ]);
    }

    public function test_order_creation_fails_with_insufficient_stock(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 1]);

        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);

        // Stock should not be decremented
        $this->assertEquals(1, $product->fresh()->stock_quantity);
    }

    public function test_order_creation_fails_with_invalid_customer(): void
    {
        $payload = [
            'customer_id' => 99999,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }

    public function test_order_creation_fails_with_empty_items(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_order_creation_fails_with_invalid_product(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['product_id' => 99999, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_order_total_amount_is_calculated_correctly(): void
    {
        $payload = [
            'customer_id' => $this->customer->id,
            'items' => [
                ['product_id' => $this->product1->id, 'quantity' => 3],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $payload);

        $response->assertStatus(201);
        $this->assertEquals(135.00, $response->json('data.total_amount'));
    }
}
