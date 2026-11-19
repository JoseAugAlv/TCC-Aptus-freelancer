<?php
// app/Helpers/RestricaoHelper.php
//
// Regras de quem pode anunciar / contratar:
//  - conta suspensa (usuario.suspenso_ate no futuro) não anuncia nem contrata;
//  - é preciso ter CPF e data de nascimento válidos e 18 anos ou mais;
//  - usuários com bloqueio entre si (qualquer direção) não negociam.

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/DocumentoHelper.php';

class RestricaoHelper
{
    /** Prazos de suspensão aceitos (valor do formulário => intervalo). */
    public const PRAZOS_SUSPENSAO = [
        '24h' => ['+24 hours', '24 horas'],
        '7d'  => ['+7 days',   '7 dias'],
        '14d' => ['+14 days',  '14 dias'],
        '30d' => ['+30 days',  '30 dias'],
        '90d' => ['+90 days',  '90 dias'],
        '1a'  => ['+1 year',   '1 ano'],
    ];

    private static array $cacheUsuario = [];

    private static function usuario(int $id): ?array
    {
        if (!array_key_exists($id, self::$cacheUsuario)) {
            $stmt = Database::getConnection()->prepare(
                "SELECT id_usuario, cpf_cnpj, data_nascimento, suspenso_ate, motivo_suspensao
                 FROM usuario WHERE id_usuario = ?"
            );
            $stmt->execute([$id]);
            self::$cacheUsuario[$id] = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        return self::$cacheUsuario[$id];
    }

    /** Data/hora do fim da suspensão, ou null se a conta não está suspensa. */
    public static function suspensoAte(int $usuarioId): ?string
    {
        $u = self::usuario($usuarioId);
        if (!$u || empty($u['suspenso_ate'])) return null;
        return strtotime($u['suspenso_ate']) > time() ? $u['suspenso_ate'] : null;
    }

    public static function existeBloqueio(int $a, int $b): bool
    {
        if ($a <= 0 || $b <= 0 || $a === $b) return false;
        $stmt = Database::getConnection()->prepare(
            "SELECT 1 FROM usuario_bloqueio
             WHERE (id_bloqueador = ? AND id_bloqueado = ?) OR (id_bloqueador = ? AND id_bloqueado = ?)
             LIMIT 1"
        );
        $stmt->execute([$a, $b, $b, $a]);
        return (bool) $stmt->fetchColumn();
    }

    /** Eu bloqueei essa pessoa? (para mostrar "Desbloquear") */
    public static function bloqueouUsuario(int $quem, int $alvo): bool
    {
        $stmt = Database::getConnection()->prepare(
            "SELECT 1 FROM usuario_bloqueio WHERE id_bloqueador = ? AND id_bloqueado = ? LIMIT 1"
        );
        $stmt->execute([$quem, $alvo]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Motivo pelo qual o usuário NÃO pode anunciar/contratar, ou null se pode.
     * $acao: 'anunciar' ou 'contratar' (só muda o texto).
     */
    public static function motivoImpedimento(int $usuarioId, string $acao = 'contratar'): ?string
    {
        $verbo = $acao === 'anunciar' ? 'anunciar serviços' : 'contratar serviços';

        if ($ate = self::suspensoAte($usuarioId)) {
            return 'Sua conta está suspensa até ' . date('d/m/Y \à\s H:i', strtotime($ate))
                 . ". Até lá não é possível {$verbo}.";
        }

        $u = self::usuario($usuarioId);
        if (!$u || !DocumentoHelper::cpfValido((string) $u['cpf_cnpj'])
            || empty($u['data_nascimento']) || $u['data_nascimento'] === '0000-00-00') {
            return "Para {$verbo}, complete seu cadastro com CPF e data de nascimento em Editar Perfil.";
        }

        if (DocumentoHelper::idade($u['data_nascimento']) < 18) {
            return "É preciso ter 18 anos ou mais para {$verbo}.";
        }

        return null;
    }

    /** Redireciona com flash se o usuário não puder realizar a ação. */
    public static function exigir(int $usuarioId, string $acao, string $voltarPara): void
    {
        $motivo = self::motivoImpedimento($usuarioId, $acao);
        if ($motivo === null) return;

        $_SESSION['flash'] = ['tipo' => 'erro', 'mensagem' => $motivo];
        header('Location: ' . $voltarPara);
        exit;
    }
}
