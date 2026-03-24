<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'status' => OrderStatus::New,
            'total_amount' => 0,
            'confirmed_at' => null,
            'shipped_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::Confirmed,
            'confirmed_at' => now(),
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::Shipped,
            'confirmed_at' => now()->subHours(2),
            'shipped_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::Completed,
            'confirmed_at' => now()->subDays(3),
            'shipped_at' => now()->subDays(1),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => OrderStatus::Cancelled,
        ]);
    }
}
