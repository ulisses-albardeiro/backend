<?php

namespace App\Enum\Product;

enum ProductCategoryStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    /**
     * Retorna o texto formatado para labels ou badges no sistema
     */
    public function getLabel(): string
    {
        return match($this) {
            self::ACTIVE => 'Ativa',
            self::INACTIVE => 'Inativa',
        };
    }

    /**
     * Define a cor ou estilo (CSS) que deve ser usado no front-end
     */
    public function getColor(): string
    {
        return match($this) {
            self::ACTIVE => 'success', // verde
            self::INACTIVE => 'danger',  // vermelho
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
