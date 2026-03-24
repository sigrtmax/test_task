<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        string $productName,
        string $sku,
        int $available,
        int $requested,
    ) {
        parent::__construct(
            "Insufficient stock for product '{$productName}' (SKU: {$sku}). Available: {$available}, requested: {$requested}."
        );
    }
}
