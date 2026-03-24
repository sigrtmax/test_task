<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    private static array $categories = ['engine', 'brakes', 'suspension', 'electrical', 'body'];

    private static array $productsByCategory = [
        'engine' => [
            'Oil Filter', 'Air Filter', 'Spark Plug', 'Timing Belt', 'Water Pump',
            'Fuel Injector', 'Crankshaft Seal', 'Camshaft Sensor',
        ],
        'brakes' => [
            'Brake Pad Set', 'Brake Disc', 'Brake Caliper', 'Brake Fluid',
            'Brake Drum', 'ABS Sensor',
        ],
        'suspension' => [
            'Shock Absorber', 'Control Arm', 'Ball Joint', 'Tie Rod End',
            'Sway Bar Link', 'Strut Mount',
        ],
        'electrical' => [
            'Alternator', 'Starter Motor', 'Battery', 'Fuse Box',
            'Ignition Coil', 'Oxygen Sensor',
        ],
        'body' => [
            'Headlight Assembly', 'Tail Light', 'Side Mirror', 'Door Handle',
            'Hood Latch', 'Bumper Cover',
        ],
    ];

    public function definition(): array
    {
        $category = $this->faker->randomElement(self::$categories);
        $name = $this->faker->randomElement(self::$productsByCategory[$category]);
        $categoryPrefix = strtoupper(substr($category, 0, 3));

        return [
            'name' => $name,
            'sku' => $categoryPrefix . '-' . str_pad((string) $this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'price' => $this->faker->randomFloat(2, 5, 500),
            'stock_quantity' => $this->faker->numberBetween(0, 200),
            'category' => $category,
        ];
    }

    public function inCategory(string $category): static
    {
        return $this->state(fn () => ['category' => $category]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock_quantity' => 0]);
    }

    public function withStock(int $quantity): static
    {
        return $this->state(fn () => ['stock_quantity' => $quantity]);
    }
}
