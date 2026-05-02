<?php

namespace App\Enum\Labor;

enum LaborUnit: string
{
    case UNIDADE = 'UN';
    case HORA = 'HR';
    case MINUTO = 'MIN';
    case DIA = 'DIA';
    case METRO = 'MT';
    case METRO_QUADRADO = 'M2';

    /**
     * Retorna o nome por extenso para exibir em selects ou labels
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::UNIDADE => 'Unidade (Preço Fixo)',
            self::HORA => 'Hora técnica',
            self::MINUTO => 'Minutos',
            self::DIA => 'Diária',
            self::METRO => 'Metro Linear',
            self::METRO_QUADRADO => 'Metro Quadrado'
        };
    }
}
