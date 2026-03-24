<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create products in different categories
        Product::factory()->count(5)->create(['category' => 'engine']);
        Product::factory()->count(3)->create(['category' => 'brakes']);
        Product::factory()->count(2)->create(['category' => 'suspension']);
    }

    public function test_products_list_returns_paginated_response(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'sku', 'price', 'stock_quantity', 'category', 'created_at'],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_products_filter_by_category(): void
    {
        $response = $this->getJson('/api/v1/products?category=engine');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        foreach ($data as $product) {
            $this->assertEquals('engine', $product['category']);
        }
    }

    public function test_products_search_by_name(): void
    {
        // Create a product with a distinctive name for searching
        Product::factory()->create([
            'name' => 'UniqueTestOilFilter',
            'sku' => 'TEST-001',
            'category' => 'engine',
        ]);

        $response = $this->getJson('/api/v1/products?search=uniquetestoil');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        $this->assertStringContainsStringIgnoringCase('uniquetestoil', strtolower($data[0]['name']));
    }

    public function test_products_search_by_sku(): void
    {
        Product::factory()->create([
            'name' => 'Some Part',
            'sku' => 'UNIQUE-SKU-999',
            'category' => 'engine',
        ]);

        $response = $this->getJson('/api/v1/products?search=UNIQUE-SKU');

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        $this->assertStringContainsStringIgnoringCase('UNIQUE-SKU', $data[0]['sku']);
    }

    public function test_products_pagination_per_page(): void
    {
        $response = $this->getJson('/api/v1/products?per_page=2');

        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 2);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_products_invalid_per_page_returns_422(): void
    {
        $response = $this->getJson('/api/v1/products?per_page=999');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }
}
