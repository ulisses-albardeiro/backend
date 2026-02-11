<?php

namespace App\Enum;

enum CustomerType: string
{
    case INDIVIDUAL = 'individual';
    case LEGAL_ENTITY = 'legal_entity';

    public function getLabel(): string
    {
        return match($this) {
            self::INDIVIDUAL => 'Pessoa Física',
            self::LEGAL_ENTITY => 'Pessoa Jurídica',
        };
    }
}
