<?php

namespace App\Enum;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELED = 'canceled';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'Pendente',
            self::CONFIRMED => 'Confirmado',
            self::CANCELED => 'Cancelado',
        };
    }
}