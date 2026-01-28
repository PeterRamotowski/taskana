<?php

namespace App\Entity\Enum;

enum RecurrencePattern: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';

    public function getLabel(): string
    {
        return match($this) {
            self::DAILY => 'Daily',
            self::WEEKLY => 'Weekly',
            self::MONTHLY => 'Monthly',
            self::YEARLY => 'Yearly',
        };
    }
}
