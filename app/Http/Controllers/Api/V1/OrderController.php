<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CreateOrderDTO;
use App\DTOs\UpdateOrderStatusDTO;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListOrdersRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Orders", description="Order management endpoints")
 */
class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     tags={"Orders"},
     *     summary="Get list of orders",
     *     description="Returns paginated list of orders with optional filtering",
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"new","confirmed","processing","shipped","completed","cancelled"})),
     *     @OA\Parameter(name="customer_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="Successful response",
     *         @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order")))
     *     )
     * )
     */
    public function index(ListOrdersRequest $request): OrderCollection
    {
        $orders = $this->orderService->getList($request->validated());

        return new OrderCollection($orders);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     description="Creates a new order with atomic stock decrement. Rate limit: 10/min per IP.",
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreOrderRequest")
     *     ),
     *     @OA\Response(response=201, description="Order created",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Order"))
     *     ),
     *     @OA\Response(response=422, description="Validation error or insufficient stock"),
     *     @OA\Response(response=429, description="Too Many Attempts")
     * )
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create(CreateOrderDTO::fromRequest($request));

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}",
     *     tags={"Orders"},
     *     summary="Get order details",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Order details",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Order"))
     *     ),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show(Order $order): OrderResource
    {
        $order->load(['customer', 'items.product', 'export']);

        return new OrderResource($order);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/orders/{id}/status",
     *     tags={"Orders"},
     *     summary="Update order status",
     *     description="Changes order status with validation of allowed transitions",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(@OA\Property(property="status", type="string", enum={"confirmed","processing","shipped","completed","cancelled"}))
     *     ),
     *     @OA\Response(response=200, description="Status updated",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Order"))
     *     ),
     *     @OA\Response(response=422, description="Invalid status transition"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): OrderResource
    {
        $dto = new UpdateOrderStatusDTO(
            orderId: $order->id,
            status: OrderStatus::from($request->validated('status')),
        );

        $order = $this->orderService->changeStatus($dto);

        return new OrderResource($order);
    }
}
