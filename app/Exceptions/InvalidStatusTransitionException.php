<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\OrderStatus;
use RuntimeException;

class InvalidStatusTransitionException extends RuntimeException
{
    public function __construct(OrderStatus $from, OrderStatus $to)
    {
        parent::__construct(
            "Cannot transition order status from '{$from->value}' to '{$to->value}'."
        );
    }
}
