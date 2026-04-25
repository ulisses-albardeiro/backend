<?php

namespace App\Enum\Product;

enum InventoryMovementType: string
{
    case INPUT = 'input';
    case OUTPUT = 'output';
    case ADJUSTMENT = 'adjustment';

    /**
     * Retorna um nome amigável para exibição no sistema/PDF
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::INPUT => 'Entrada (Compra, devolução de cliente)',
            self::OUTPUT => 'Saída (Venda, perda, consumo interno)',
            self::ADJUSTMENT => 'Ajuste manual (Inventário)',
        };
    }
}
