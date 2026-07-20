<?php

namespace App\Validator;

use App\Enum\CustomerType;

final class DocumentValidator
{
    public static function isValidForType(?string $document, CustomerType $type): bool
    {
        $digits = preg_replace('/\D/', '', $document ?? '');

        if ($digits === '') {
            return true;
        }

        return match ($type) {
            CustomerType::INDIVIDUAL => self::isValidCpf($digits),
            CustomerType::LEGAL_ENTITY => self::isValidCnpj($digits),
        };
    }

    public static function isValidCpf(string $digits): bool
    {
        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $digits[$i] * (($t + 1) - $i);
            }
            $digit = (($sum * 10) % 11) % 10;
            if ((int) $digits[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    public static function isValidCnpj(string $digits): bool
    {
        if (strlen($digits) !== 14 || preg_match('/^(\d)\1{13}$/', $digits)) {
            return false;
        }

        $weightSets = [
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        ];

        foreach ($weightSets as $index => $weights) {
            $length = 12 + $index;
            $sum = 0;
            for ($i = 0; $i < $length; $i++) {
                $sum += (int) $digits[$i] * $weights[$i];
            }
            $remainder = $sum % 11;
            $digit = $remainder < 2 ? 0 : 11 - $remainder;
            if ((int) $digits[$length] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
