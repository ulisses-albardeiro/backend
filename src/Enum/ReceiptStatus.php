<?php

namespace App\Enum;

enum ReceiptStatus: string
{
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';

    /**
     * Retorna o texto formatado para labels ou badges no sistema
     */
    public function getLabel(): string
    {
        return match($this) {
            self::ACTIVE => 'Ativo',
            self::CANCELLED => 'Cancelado',
        };
    }

    /**
     * Define a cor ou estilo (CSS) que deve ser usado no front-end
     */
    public function getColor(): string
    {
        return match($this) {
            self::ACTIVE => 'success', // verde
            self::CANCELLED => 'danger',  // vermelho
        };
    }

    /**
     * Atalho para verificar se o recibo ainda tem valor financeiro
     */
    public function isValid(): bool
    {
        return $this === self::ACTIVE;
    }
}
