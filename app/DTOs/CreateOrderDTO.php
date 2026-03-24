<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Http\Requests\StoreOrderRequest;

final readonly class CreateOrderDTO
{
    /**
     * @param OrderItemDTO[] $items
     */
    public function __construct(
        public int $customerId,
        public array $items,
    ) {}

    public static function fromRequest(StoreOrderRequest $request): self
    {
        $items = array_map(
            fn (array $item) => new OrderItemDTO(
                productId: (int) $item['product_id'],
                quantity: (int) $item['quantity'],
            ),
            $request->validated('items'),
        );

        return new self(
            customerId: (int) $request->validated('customer_id'),
            items: $items,
        );
    }
}
