<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Validates whether a transition to the given status is allowed.
     *
     * Allowed transitions:
     *   new -> confirmed -> processing -> shipped -> completed
     *   new -> cancelled
     *   confirmed -> cancelled
     */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::New => in_array($next, [self::Confirmed, self::Cancelled], true),
            self::Confirmed => in_array($next, [self::Processing, self::Cancelled], true),
            self::Processing => $next === self::Shipped,
            self::Shipped => $next === self::Completed,
            self::Completed, self::Cancelled => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}
