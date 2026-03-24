<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ProductSeeder extends Seeder
{
    private array $products = [
        // Engine parts (8)
        ['name' => 'Oil Filter', 'sku' => 'ENG-001', 'price' => 12.50, 'stock_quantity' => 150, 'category' => 'engine'],
        ['name' => 'Air Filter', 'sku' => 'ENG-002', 'price' => 18.99, 'stock_quantity' => 120, 'category' => 'engine'],
        ['name' => 'Spark Plug', 'sku' => 'ENG-003', 'price' => 8.75, 'stock_quantity' => 200, 'category' => 'engine'],
        ['name' => 'Timing Belt', 'sku' => 'ENG-004', 'price' => 45.00, 'stock_quantity' => 60, 'category' => 'engine'],
        ['name' => 'Water Pump', 'sku' => 'ENG-005', 'price' => 89.99, 'stock_quantity' => 40, 'category' => 'engine'],
        ['name' => 'Fuel Injector', 'sku' => 'ENG-006', 'price' => 125.00, 'stock_quantity' => 30, 'category' => 'engine'],
        ['name' => 'Crankshaft Seal', 'sku' => 'ENG-007', 'price' => 15.50, 'stock_quantity' => 80, 'category' => 'engine'],
        ['name' => 'Camshaft Position Sensor', 'sku' => 'ENG-008', 'price' => 65.99, 'stock_quantity' => 45, 'category' => 'engine'],

        // Brakes (6)
        ['name' => 'Brake Pad Set Front', 'sku' => 'BRK-001', 'price' => 49.99, 'stock_quantity' => 100, 'category' => 'brakes'],
        ['name' => 'Brake Pad Set Rear', 'sku' => 'BRK-002', 'price' => 39.99, 'stock_quantity' => 90, 'category' => 'brakes'],
        ['name' => 'Brake Disc Front', 'sku' => 'BRK-003', 'price' => 75.00, 'stock_quantity' => 50, 'category' => 'brakes'],
        ['name' => 'Brake Disc Rear', 'sku' => 'BRK-004', 'price' => 65.00, 'stock_quantity' => 50, 'category' => 'brakes'],
        ['name' => 'Brake Caliper', 'sku' => 'BRK-005', 'price' => 120.00, 'stock_quantity' => 25, 'category' => 'brakes'],
        ['name' => 'ABS Wheel Speed Sensor', 'sku' => 'BRK-006', 'price' => 55.00, 'stock_quantity' => 60, 'category' => 'brakes'],

        // Suspension (6)
        ['name' => 'Front Shock Absorber', 'sku' => 'SUS-001', 'price' => 95.00, 'stock_quantity' => 40, 'category' => 'suspension'],
        ['name' => 'Rear Shock Absorber', 'sku' => 'SUS-002', 'price' => 85.00, 'stock_quantity' => 40, 'category' => 'suspension'],
        ['name' => 'Front Control Arm', 'sku' => 'SUS-003', 'price' => 110.00, 'stock_quantity' => 30, 'category' => 'suspension'],
        ['name' => 'Ball Joint', 'sku' => 'SUS-004', 'price' => 35.99, 'stock_quantity' => 70, 'category' => 'suspension'],
        ['name' => 'Tie Rod End', 'sku' => 'SUS-005', 'price' => 28.50, 'stock_quantity' => 75, 'category' => 'suspension'],
        ['name' => 'Sway Bar Link', 'sku' => 'SUS-006', 'price' => 22.00, 'stock_quantity' => 90, 'category' => 'suspension'],

        // Electrical (5)
        ['name' => 'Alternator', 'sku' => 'ELC-001', 'price' => 185.00, 'stock_quantity' => 20, 'category' => 'electrical'],
        ['name' => 'Starter Motor', 'sku' => 'ELC-002', 'price' => 155.00, 'stock_quantity' => 20, 'category' => 'electrical'],
        ['name' => 'Ignition Coil', 'sku' => 'ELC-003', 'price' => 42.00, 'stock_quantity' => 55, 'category' => 'electrical'],
        ['name' => 'Oxygen Sensor', 'sku' => 'ELC-004', 'price' => 68.99, 'stock_quantity' => 45, 'category' => 'electrical'],
        ['name' => 'Throttle Position Sensor', 'sku' => 'ELC-005', 'price' => 52.50, 'stock_quantity' => 50, 'category' => 'electrical'],

        // Body (5)
        ['name' => 'Headlight Assembly Left', 'sku' => 'BDY-001', 'price' => 145.00, 'stock_quantity' => 15, 'category' => 'body'],
        ['name' => 'Headlight Assembly Right', 'sku' => 'BDY-002', 'price' => 145.00, 'stock_quantity' => 15, 'category' => 'body'],
        ['name' => 'Tail Light Assembly', 'sku' => 'BDY-003', 'price' => 89.00, 'stock_quantity' => 20, 'category' => 'body'],
        ['name' => 'Side Mirror Left', 'sku' => 'BDY-004', 'price' => 75.00, 'stock_quantity' => 25, 'category' => 'body'],
        ['name' => 'Front Bumper Cover', 'sku' => 'BDY-005', 'price' => 195.00, 'stock_quantity' => 10, 'category' => 'body'],
    ];

    public function run(): void
    {
        foreach ($this->products as $product) {
            Product::updateOrCreate(['sku' => $product['sku']], $product);
        }

        Cache::tags(['products'])->flush();

        $this->command->info('Products seeded: ' . count($this->products) . ' records.');
    }
}
