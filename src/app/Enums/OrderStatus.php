<?php

namespace App\Enums;

enum OrderStatus: int
{
    case Pending = 1;
    case Approved = 2;
    case Cancelled = 3;
    case Refunded = 4;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    // Máquina de estados do Order
    public function canTransitionTo(self $to): bool
    {
        return match ($this) {
            self::Pending => in_array($to, [self::Approved, self::Cancelled]),
            self::Approved => in_array($to, [self::Refunded]),
            default => false,
        };
    }
}