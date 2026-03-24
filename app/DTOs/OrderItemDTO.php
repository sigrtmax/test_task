<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class OrderItemDTO
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {}
}
