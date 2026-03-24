<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListProductsRequest;
use App\Http\Resources\ProductCollection;
use App\Services\ProductService;

/**
 * @OA\Tag(name="Products", description="Product catalog endpoints")
 */
class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService) {}

    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     tags={"Products"},
     *     summary="Get list of products",
     *     description="Returns paginated list of products with optional filtering",
     *     @OA\Parameter(name="category", in="query", description="Filter by category", required=false, @OA\Schema(type="string", example="engine")),
     *     @OA\Parameter(name="search", in="query", description="Search by name or SKU (ILIKE)", required=false, @OA\Schema(type="string", example="filter")),
     *     @OA\Parameter(name="per_page", in="query", description="Items per page (max 100)", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(ListProductsRequest $request): ProductCollection
    {
        $products = $this->productService->getList($request->validated());

        return new ProductCollection($products);
    }
}
