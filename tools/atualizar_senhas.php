<?php
// tools/atualizar_senhas.php
//
// Uso CLI:      php tools/atualizar_senhas.php
// Uso navegador: http://localhost/Aptus/tools/atualizar_senhas.php
//
// O que faz:
//   1) Gera o hash bcrypt da senha padrão.
//   2) Atualiza a senha de TODOS os usuários.
//   3) Garante que os usuários de teste (@aptus.com) estão verificados e ativos.
//   4) Mostra o SQL pronto para você salvar no repositório.

// ============= CONFIGURAÇÃO =============
$novaSenha = 'Aptus@2026';   // <-- troque aqui se quiser outra senha
// ========================================

// Bloqueia em produção
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if (($env['APP_ENV'] ?? 'development') === 'production') {
        http_response_code(403);
        exit('Este script está bloqueado em produção.');
    }
}

require_once __DIR__ . '/../app/Config/database.php';

$isCli = (php_sapi_name() === 'cli');

// Gera hash
$hash = password_hash($novaSenha, PASSWORD_DEFAULT);

$pdo = Database::getConnection();

// 1) Atualiza senha de TODOS os usuários
$stmt = $pdo->prepare("UPDATE usuario SET senha = ?");
$stmt->execute([$hash]);
$afetados = $stmt->rowCount();

// 2) Desbloqueia usuários de teste (verificado, ativo, não banido)
$stmt2 = $pdo->prepare(
    "UPDATE usuario
     SET email_verificado = 1,
         ativo = 1,
         banido = 0,
         token_verificacao = NULL
     WHERE email LIKE '%@aptus.com'"
);
$stmt2->execute();
$testes = $stmt2->rowCount();

$hashEscapado = addslashes($hash);
$sqlFinal = <<<SQL
-- Cole este bloco no final do seu aptus_bd.sql (ou em seed_update_senhas.sql)
-- Senha de TODOS os usuários: {$novaSenha}

UPDATE usuario SET senha = '{$hashEscapado}';

UPDATE usuario
SET email_verificado = 1,
    ativo = 1,
    banido = 0,
    token_verificacao = NULL
WHERE email LIKE '%@aptus.com';
SQL;

// ===== Saída =====
if ($isCli) {
    echo "===========================================\n";
    echo "Senhas atualizadas com sucesso.\n";
    echo "Usuários afetados (senha): {$afetados}\n";
    echo "Usuários de teste desbloqueados: {$testes}\n";
    echo "Nova senha: {$novaSenha}\n";
    echo "Hash: {$hash}\n";
    echo "===========================================\n\n";
    echo "SQL para salvar no repositório:\n\n";
    echo $sqlFinal . "\n";
    exit;
}

// Saída via navegador
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Atualizar senhas — Aptus</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background:#f8fafc; color:#1a2f3e; padding:32px; line-height:1.6; }
        .card { max-width: 820px; margin: 0 auto; background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,.05); }
        h1 { color:#006577; margin-top:0; }
        .ok { background:#d1fae5; color:#065f46; border-left:5px solid #10b981; padding:12px 16px; border-radius:8px; margin-bottom:20px; }
        .info { display:grid; grid-template-columns: 220px 1fr; gap:8px 16px; margin-bottom:20px; }
        .info b { color:#006577; }
        code { background:#f1f5f9; padding:2px 8px; border-radius:5px; font-family: monospace; word-break: break-all; }
        pre { background:#0f172a; color:#e2e8f0; padding:18px; border-radius:10px; overflow-x:auto; font-size:.9rem; line-height:1.5; }
        .senha { background:#fef3c7; padding:12px 16px; border-radius:8px; font-weight:700; color:#92400e; font-size:1.1rem; display:inline-block; }
        .foot { text-align:center; margin-top:24px; color:#64748b; font-size:.85rem; }
    </style>
</head>
<body>
<div class="card">
    <h1>✅ Senhas atualizadas</h1>

    <div class="ok">
        Todas as contas foram atualizadas. Use as credenciais abaixo para logar.
    </div>

    <div class="info">
        <b>Usuários afetados (senha):</b><span><?= (int) $afetados ?></span>
        <b>Usuários de teste desbloqueados:</b><span><?= (int) $testes ?></span>
        <b>Nova senha:</b><span class="senha"><?= htmlspecialchars($novaSenha) ?></span>
        <b>Hash gerado:</b><span><code><?= htmlspecialchars($hash) ?></code></span>
    </div>

    <h2 style="color:#006577;">SQL pronto para o repositório</h2>
    <p>Copie o bloco abaixo e salve em <code>seed_update_senhas.sql</code> (ou cole no final de <code>aptus_bd.sql</code>):</p>
    <pre><?= htmlspecialchars($sqlFinal) ?></pre>

    <p><a href="/Aptus/login" style="display:inline-block;margin-top:12px;padding:10px 22px;background:#006577;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Ir para o login</a></p>
</div>
<div class="foot">Aptus — script de desenvolvimento</div>
</body>
</html>