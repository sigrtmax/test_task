<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ProductSeeder extends Seeder
{
    private array $products = [
        // Двигатель (8)
        ['name' => 'Фильтр масляный', 'sku' => 'ENG-001', 'price' => 12.50, 'stock_quantity' => 150, 'category' => 'engine'],
        ['name' => 'Фильтр воздушный', 'sku' => 'ENG-002', 'price' => 18.99, 'stock_quantity' => 120, 'category' => 'engine'],
        ['name' => 'Свеча зажигания', 'sku' => 'ENG-003', 'price' => 8.75, 'stock_quantity' => 200, 'category' => 'engine'],
        ['name' => 'Ремень ГРМ', 'sku' => 'ENG-004', 'price' => 45.00, 'stock_quantity' => 60, 'category' => 'engine'],
        ['name' => 'Помпа охлаждения', 'sku' => 'ENG-005', 'price' => 89.99, 'stock_quantity' => 40, 'category' => 'engine'],
        ['name' => 'Форсунка топливная', 'sku' => 'ENG-006', 'price' => 125.00, 'stock_quantity' => 30, 'category' => 'engine'],
        ['name' => 'Сальник коленвала', 'sku' => 'ENG-007', 'price' => 15.50, 'stock_quantity' => 80, 'category' => 'engine'],
        ['name' => 'Датчик положения распредвала', 'sku' => 'ENG-008', 'price' => 65.99, 'stock_quantity' => 45, 'category' => 'engine'],

        // Тормоза (6)
        ['name' => 'Колодки тормозные передние', 'sku' => 'BRK-001', 'price' => 49.99, 'stock_quantity' => 100, 'category' => 'brakes'],
        ['name' => 'Колодки тормозные задние', 'sku' => 'BRK-002', 'price' => 39.99, 'stock_quantity' => 90, 'category' => 'brakes'],
        ['name' => 'Диск тормозной передний', 'sku' => 'BRK-003', 'price' => 75.00, 'stock_quantity' => 50, 'category' => 'brakes'],
        ['name' => 'Диск тормозной задний', 'sku' => 'BRK-004', 'price' => 65.00, 'stock_quantity' => 50, 'category' => 'brakes'],
        ['name' => 'Суппорт тормозной', 'sku' => 'BRK-005', 'price' => 120.00, 'stock_quantity' => 25, 'category' => 'brakes'],
        ['name' => 'Датчик скорости колеса ABS', 'sku' => 'BRK-006', 'price' => 55.00, 'stock_quantity' => 60, 'category' => 'brakes'],

        // Подвеска (6)
        ['name' => 'Амортизатор передний', 'sku' => 'SUS-001', 'price' => 95.00, 'stock_quantity' => 40, 'category' => 'suspension'],
        ['name' => 'Амортизатор задний', 'sku' => 'SUS-002', 'price' => 85.00, 'stock_quantity' => 40, 'category' => 'suspension'],
        ['name' => 'Рычаг передней подвески', 'sku' => 'SUS-003', 'price' => 110.00, 'stock_quantity' => 30, 'category' => 'suspension'],
        ['name' => 'Шаровая опора', 'sku' => 'SUS-004', 'price' => 35.99, 'stock_quantity' => 70, 'category' => 'suspension'],
        ['name' => 'Наконечник рулевой тяги', 'sku' => 'SUS-005', 'price' => 28.50, 'stock_quantity' => 75, 'category' => 'suspension'],
        ['name' => 'Стойка стабилизатора', 'sku' => 'SUS-006', 'price' => 22.00, 'stock_quantity' => 90, 'category' => 'suspension'],

        // Электрика (5)
        ['name' => 'Генератор', 'sku' => 'ELC-001', 'price' => 185.00, 'stock_quantity' => 20, 'category' => 'electrical'],
        ['name' => 'Стартер', 'sku' => 'ELC-002', 'price' => 155.00, 'stock_quantity' => 20, 'category' => 'electrical'],
        ['name' => 'Катушка зажигания', 'sku' => 'ELC-003', 'price' => 42.00, 'stock_quantity' => 55, 'category' => 'electrical'],
        ['name' => 'Датчик кислорода (лямбда)', 'sku' => 'ELC-004', 'price' => 68.99, 'stock_quantity' => 45, 'category' => 'electrical'],
        ['name' => 'Датчик положения дроссельной заслонки', 'sku' => 'ELC-005', 'price' => 52.50, 'stock_quantity' => 50, 'category' => 'electrical'],

        // Кузов (5)
        ['name' => 'Фара левая в сборе', 'sku' => 'BDY-001', 'price' => 145.00, 'stock_quantity' => 15, 'category' => 'body'],
        ['name' => 'Фара правая в сборе', 'sku' => 'BDY-002', 'price' => 145.00, 'stock_quantity' => 15, 'category' => 'body'],
        ['name' => 'Фонарь задний в сборе', 'sku' => 'BDY-003', 'price' => 89.00, 'stock_quantity' => 20, 'category' => 'body'],
        ['name' => 'Зеркало боковое левое', 'sku' => 'BDY-004', 'price' => 75.00, 'stock_quantity' => 25, 'category' => 'body'],
        ['name' => 'Бампер передний', 'sku' => 'BDY-005', 'price' => 195.00, 'stock_quantity' => 10, 'category' => 'body'],
    ];

    public function run(): void
    {
        foreach ($this->products as $product) {
            Product::updateOrCreate(['sku' => $product['sku']], $product);
        }

        Cache::tags(['products'])->flush();

        $this->command->info('Товары загружены: ' . count($this->products) . ' записей.');
    }
}
