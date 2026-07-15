<?php

namespace App\Enum\Subscription;

enum PlanBillingCycle: string
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case YEARLY = 'yearly';

    public function getLabel(): string
    {
        return match($this) {
            self::MONTHLY => 'Mensal',
            self::QUARTERLY => 'Trimestral',
            self::YEARLY => 'Anual',
        };
    }
}
