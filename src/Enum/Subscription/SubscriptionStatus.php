<?php

namespace App\Enum\Subscription;

enum SubscriptionStatus: string
{
    case TRIALING = 'trialing';
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case EXPIRED = 'expired';
    case CANCELED = 'canceled';
    case INCOMPLETE = 'incomplete';

    public function getLabel(): string
    {
        return match($this) {
            self::TRIALING => 'Em teste',
            self::ACTIVE => 'Ativa',
            self::PAST_DUE => 'Pagamento atrasado',
            self::EXPIRED => 'Expirada',
            self::CANCELED => 'Cancelada',
            self::INCOMPLETE => 'Incompleta',
        };
    }

    public function blocksAccess(): bool
    {
        return match($this) {
            self::PAST_DUE, self::EXPIRED, self::CANCELED, self::INCOMPLETE => true,
            self::TRIALING, self::ACTIVE => false,
        };
    }
}
