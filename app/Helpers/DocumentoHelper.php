<?php
// app/Helpers/DocumentoHelper.php
//
// Validação de CPF (dígitos verificadores), data de nascimento, idade e
// nome de usuário. A checagem do CPF é matemática: confirma que o número é
// um CPF possível, não consulta a Receita Federal.

class DocumentoHelper
{
    public static function somenteDigitos(string $v): string
    {
        return preg_replace('/\D+/', '', $v);
    }

    public static function cpfValido(string $cpf): bool
    {
        $cpf = self::somenteDigitos($cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digito = ((10 * $soma) % 11) % 10;
            if ((int) $cpf[$t] !== $digito) {
                return false;
            }
        }
        return true;
    }

    public static function formatarCpf(string $cpf): string
    {
        $d = self::somenteDigitos($cpf);
        return strlen($d) === 11
            ? substr($d, 0, 3) . '.' . substr($d, 3, 3) . '.' . substr($d, 6, 3) . '-' . substr($d, 9, 2)
            : $cpf;
    }

    /** Data real (AAAA-MM-DD), não futura e com no máximo 120 anos. */
    public static function dataNascimentoValida(string $data): bool
    {
        $dt = DateTime::createFromFormat('!Y-m-d', $data);
        if (!$dt || $dt->format('Y-m-d') !== $data) return false;
        $hoje = new DateTime('today');
        return $dt <= $hoje && $dt->diff($hoje)->y <= 120;
    }

    public static function idade(string $data): int
    {
        try {
            return (new DateTime($data))->diff(new DateTime('today'))->y;
        } catch (Throwable $e) {
            return 0;
        }
    }

    /** 3 a 30 caracteres: letras minúsculas, números, ponto e sublinhado. */
    public static function usernameValido(string $u): bool
    {
        return (bool) preg_match('/^(?![.])(?!.*[.]{2})[a-z0-9._]{3,30}(?<![.])$/', $u);
    }

    public static function normalizarUsername(string $u): string
    {
        return strtolower(ltrim(trim($u), '@'));
    }
}
