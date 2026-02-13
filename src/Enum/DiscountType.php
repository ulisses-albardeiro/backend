<?php

namespace App\Enum;

enum DiscountType: string
{
    case NONE = 'none';
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';

    public function getLabel(): string
    {
        return match ($this) {
            self::NONE => 'Sem Desconto',
            self::FIXED => 'Valor Fixo (R$)',
            self::PERCENTAGE => 'Percentual (%)',
        };
    }

    public function getSymbol(): string
    {
        return match ($this) {
            self::NONE => '',
            self::FIXED => 'R$',
            self::PERCENTAGE => '%',
        };
    }
}
