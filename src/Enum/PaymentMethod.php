<?php

namespace App\Enum;

enum PaymentMethod: string
{
    case PIX = 'pix';
    case CREDIT_CARD = 'credit_card';
    case CASH = 'cash';
    case BOLETO = 'boleto';
    case BANK_TRANSFER = 'bank_transfer';

    /**
     * Retorna um nome amigável para exibição no sistema/PDF
     */
    public function getLabel(): string
    {
        return match($this) {
            self::PIX => 'Pix',
            self::CREDIT_CARD => 'Cartão de Crédito',
            self::CASH => 'Dinheiro',
            self::BOLETO => 'Boleto',
            self::BANK_TRANSFER => 'Transferência Bancária',
        };
    }

    /**
     * Útil para lógica de "dar baixa" automática
     * (Ex: Pix cai na hora, Boleto demora)
     */
    public function isInstant(): bool
    {
        return match($this) {
            self::PIX, self::CASH => true,
            default => false,
        };
    }
}
