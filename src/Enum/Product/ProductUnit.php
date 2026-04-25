<?php

namespace App\Enum\Product;

enum ProductUnit: string
{
    case UNIDADE = 'UN';
    case QUILO = 'KG';
    case GRAMA = 'G';
    case LITRO = 'LT';
    case MILILITRO = 'ML';
    case METRO = 'MT';
    case PACOTE = 'PCT';
    case CAIXA = 'CX';
    case PAR = 'PAR';

    /**
     * Retorna o nome por extenso para exibir em selects ou labels
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::UNIDADE => 'Unidade',
            self::QUILO => 'Quilograma',
            self::GRAMA => 'Grama',
            self::LITRO => 'Litro',
            self::MILILITRO => 'Mililitro',
            self::METRO => 'Metro',
            self::PACOTE => 'Pacote',
            self::CAIXA => 'Caixa',
            self::PAR => 'Par',
        };
    }
}
