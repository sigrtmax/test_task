<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CreateOrderDTO;
use App\DTOs\UpdateOrderStatusDTO;
use App\Enums\OrderStatus;
use App\Events\OrderConfirmed;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new order atomically with stock validation and decrement.
     */
    public function create(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto): Order {
            // Lock all products involved for update to prevent race conditions
            $productIds = array_map(fn ($item) => $item->productId, $dto->items);
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate stock availability for all items
            foreach ($dto->items as $itemDTO) {
                $product = $products->get($itemDTO->productId);
                if ($product === null || $product->stock_quantity < $itemDTO->quantity) {
                    $available = $product?->stock_quantity ?? 0;
                    throw new InsufficientStockException(
                        productName: $product?->name ?? "ID:{$itemDTO->productId}",
                        sku: $product?->sku ?? 'unknown',
                        available: $available,
                        requested: $itemDTO->quantity,
                    );
                }
            }

            // Create the order
            $order = Order::create([
                'customer_id' => $dto->customerId,
                'status' => OrderStatus::New,
                'total_amount' => 0,
            ]);

            // Create order items and decrement stock
            $totalAmount = '0';
            foreach ($dto->items as $itemDTO) {
                $product = $products->get($itemDTO->productId);
                $unitPrice = $product->price;
                $totalPrice = bcmul((string) $unitPrice, (string) $itemDTO->quantity, 2);

                $order->items()->create([
                    'product_id' => $itemDTO->productId,
                    'quantity' => $itemDTO->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);

                $product->decrement('stock_quantity', $itemDTO->quantity);
                $totalAmount = bcadd($totalAmount, $totalPrice, 2);
            }

            // Update total amount
            $order->update(['total_amount' => $totalAmount]);
            $order->refresh();

            return $order->load(['customer', 'items.product']);
        });
    }

    /**
     * Change order status with validation of allowed transitions.
     */
    public function changeStatus(UpdateOrderStatusDTO $dto): Order
    {
        $order = Order::findOrFail($dto->orderId);

        if (! $order->status->canTransitionTo($dto->status)) {
            throw new InvalidStatusTransitionException($order->status, $dto->status);
        }

        $attributes = ['status' => $dto->status];

        if ($dto->status === OrderStatus::Confirmed) {
            $attributes['confirmed_at'] = now();
        } elseif ($dto->status === OrderStatus::Shipped) {
            $attributes['shipped_at'] = now();
        }

        $order->update($attributes);
        $order->refresh();

        // Fire event when order is confirmed — listener will dispatch ExportOrderJob
        if ($dto->status === OrderStatus::Confirmed) {
            event(new OrderConfirmed($order));
        }

        return $order->load(['customer', 'items.product', 'export']);
    }

    /**
     * Get paginated list of orders with optional filters.
     */
    public function getList(array $filters): LengthAwarePaginator
    {
        $query = Order::with(['customer', 'items.product', 'export']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get a single order by ID with all relations loaded.
     */
    public function getById(int $id): Order
    {
        return Order::with(['customer', 'items.product', 'export'])
            ->findOrFail($id);
    }
}
