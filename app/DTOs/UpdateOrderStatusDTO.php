<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\OrderStatus;

final readonly class UpdateOrderStatusDTO
{
    public function __construct(
        public int $orderId,
        public OrderStatus $status,
    ) {}
}
