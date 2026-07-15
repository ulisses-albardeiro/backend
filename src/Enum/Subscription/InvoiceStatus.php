<?php

namespace App\Enum\Subscription;

enum InvoiceStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case RECEIVED = 'received';
    case OVERDUE = 'overdue';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
    case CHARGEBACK = 'chargeback';

    public function getLabel(): string
    {
        return match($this) {
            self::PENDING => 'Pendente',
            self::CONFIRMED => 'Confirmada',
            self::RECEIVED => 'Recebida',
            self::OVERDUE => 'Vencida',
            self::REFUNDED => 'Estornada',
            self::FAILED => 'Falhou',
            self::CANCELED => 'Cancelada',
            self::CHARGEBACK => 'Contestada',
        };
    }
}
