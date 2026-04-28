<?php

namespace App\Enum\Product;

enum InventoryMovementType: string
{
    case INPUT = 'input';
    case OUTPUT = 'output';
    case ADJUSTMENT = 'adjustment';
    case INITIAL_REGISTRATION = 'initial_registration'; 

    public function getLabel(): string
    {
        return match ($this) {
            self::INPUT => 'Entrada (Compra, devolução)',
            self::OUTPUT => 'Saída (Venda, perda)',
            self::ADJUSTMENT => 'Ajuste manual (Inventário)',
            self::INITIAL_REGISTRATION => 'Saldo inicial de cadastro',
        };
    }
}

