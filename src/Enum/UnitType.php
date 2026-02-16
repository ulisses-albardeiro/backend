<?php

namespace App\Enum;

enum UnitType: string
{
    case UNIT = 'un';
    case METERS = 'm';
    case KILOGRAMS = 'kg';
    case LITERS = 'l';
    case HOURS = 'h';
    case PIECES = 'pc';
    case BAGS = 'sc';
    case PAIRS = 'pr';
    case SQUARE_METERS = 'm2';
    case CUBIC_METERS = 'm3';

    public function getLabel(): string
    {
        return match($this) {
            self::UNIT => 'Unidade',
            self::METERS => 'Metros',
            self::KILOGRAMS => 'Quilos',
            self::LITERS => 'Litros',
            self::HOURS => 'Horas',
            self::PIECES => 'Peças',
            self::BAGS => 'Sacos',
            self::PAIRS => 'Pares',
            self::SQUARE_METERS => 'Metros Quadrados',
            self::CUBIC_METERS => 'Metros Cúbicos',
        };
    }
}
