<?php
// app/Helpers/MoneyHelper.php

class MoneyHelper
{
    /**
     * Converte "R$ 1.500,50" ou "1500,50" ou "1500.50" em float 1500.50
     */
    public static function parse($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $s = trim((string) $value);
        $s = preg_replace('/[R$\s]/u', '', $s);  // remove R$ e espaços

        if ($s === '') return 0.0;

        // Se tem vírgula, formato BR
        if (strpos($s, ',') !== false) {
            $s = str_replace('.', '', $s);   // 1.500,50 → 1500,50
            $s = str_replace(',', '.', $s);  // 1500,50 → 1500.50
        }

        return (float) $s;
    }

    public static function format(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
}