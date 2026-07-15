<?php

namespace App\Enum\Subscription;

enum SubscriptionBillingType: string
{
    case UNDEFINED = 'undefined';
    case CREDIT_CARD = 'credit_card';
    case PIX = 'pix';
    case BOLETO = 'boleto';

    public function getLabel(): string
    {
        return match($this) {
            self::UNDEFINED => 'Não definido',
            self::CREDIT_CARD => 'Cartão de Crédito',
            self::PIX => 'Pix',
            self::BOLETO => 'Boleto',
        };
    }
}
