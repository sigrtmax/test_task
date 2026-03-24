<?php

declare(strict_types=1);

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Ekapak Order Management API",
 *     version="1.0.0",
 *     description="REST API for managing orders in an auto parts e-commerce store",
 *     @OA\Contact(email="api@ekapak.com")
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="Local development server"
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Oil Filter"),
 *     @OA\Property(property="sku", type="string", example="ENG-001"),
 *     @OA\Property(property="price", type="number", format="float", example=12.50),
 *     @OA\Property(property="stock_quantity", type="integer", example=150),
 *     @OA\Property(property="category", type="string", example="engine"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Customer",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Ivan Petrov"),
 *     @OA\Property(property="email", type="string", format="email", example="ivan@example.com"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+79001234567")
 * )
 *
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product", ref="#/components/schemas/Product"),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_price", type="number", format="float", example=45.00),
 *     @OA\Property(property="total_price", type="number", format="float", example=90.00)
 * )
 *
 * @OA\Schema(
 *     schema="OrderExport",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="status", type="string", enum={"pending","success","failed"}, example="success"),
 *     @OA\Property(property="attempts", type="integer", example=1),
 *     @OA\Property(property="last_error", type="string", nullable=true),
 *     @OA\Property(property="exported_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="customer", ref="#/components/schemas/Customer"),
 *     @OA\Property(property="status", type="string", enum={"new","confirmed","processing","shipped","completed","cancelled"}, example="new"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=150.75),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderItem")),
 *     @OA\Property(property="export", ref="#/components/schemas/OrderExport", nullable=true),
 *     @OA\Property(property="confirmed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="shipped_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="StoreOrderRequest",
 *     type="object",
 *     required={"customer_id","items"},
 *     @OA\Property(property="customer_id", type="integer", example=1),
 *     @OA\Property(property="items", type="array", minItems=1,
 *         @OA\Items(
 *             type="object",
 *             required={"product_id","quantity"},
 *             @OA\Property(property="product_id", type="integer", example=5),
 *             @OA\Property(property="quantity", type="integer", minimum=1, example=2)
 *         )
 *     )
 * )
 */
abstract class Controller
{
    //
}
