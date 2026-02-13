<?php

namespace App\Enum;

enum EstimateStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case SENT = 'sent';
    case EXPIRED = 'expired';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CANCELED = 'canceled';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::PENDING => 'Pendente',
            self::SENT => 'Enviado',
            self::EXPIRED => 'Expirado',
            self::ACCEPTED => 'Aprovado',
            self::REJECTED => 'Recusado',
            self::CANCELED => 'Cancelado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'amber',
            self::SENT => 'blue',
            self::EXPIRED => 'orange',
            self::ACCEPTED => 'green',
            self::REJECTED => 'red',
            self::CANCELED => 'slate',
        };
    }
}
