<?php
// public/_update_pass.php
// ACESSO: http://localhost/Aptus/_update_pass.php
// APAGUE ESTE ARQUIVO DEPOIS DE USAR.

// ============= CONFIGURAÇÃO =============
$novaSenha = 'Aptus@2026';
// ========================================

$envFile = __DIR__ . '/../.env';
$appEnv = 'development';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    $appEnv = $env['APP_ENV'] ?? 'development';
}
if ($appEnv === 'production') {
    http_response_code(403);
    exit('Bloqueado em produção.');
}

require_once __DIR__ . '/../app/Config/database.php';

$hash = password_hash($novaSenha, PASSWORD_DEFAULT);
$pdo  = Database::getConnection();

$stmt = $pdo->prepare("UPDATE usuario SET senha = ?");
$stmt->execute([$hash]);
$afetados = $stmt->rowCount();

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
$sqlFinal = "UPDATE usuario SET senha = '{$hashEscapado}';\n\n"
          . "UPDATE usuario\n"
          . "SET email_verificado = 1,\n"
          . "    ativo = 1,\n"
          . "    banido = 0,\n"
          . "    token_verificacao = NULL\n"
          . "WHERE email LIKE '%@aptus.com';\n";

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
        pre { background:#0f172a; color:#e2e8f0; padding:18px; border-radius:10px; overflow-x:auto; font-size:.9rem; }
        .senha { background:#fef3c7; padding:12px 16px; border-radius:8px; font-weight:700; color:#92400e; font-size:1.1rem; display:inline-block; }
        .alerta { background:#fee2e2; color:#991b1b; border-left:5px solid #ef4444; padding:12px 16px; border-radius:8px; margin-top:20px; font-weight:600; }
    </style>
</head>
<body>
<div class="card">
    <h1>✅ Senhas atualizadas</h1>

    <div class="ok">Todas as contas foram atualizadas.</div>

    <div class="info">
        <b>Usuários afetados (senha):</b><span><?= (int) $afetados ?></span>
        <b>Usuários de teste desbloqueados:</b><span><?= (int) $testes ?></span>
        <b>Nova senha:</b><span class="senha"><?= htmlspecialchars($novaSenha) ?></span>
        <b>Hash:</b><span><code><?= htmlspecialchars($hash) ?></code></span>
    </div>

    <h2 style="color:#006577;">SQL pronto para o repositório</h2>
    <pre><?= htmlspecialchars($sqlFinal) ?></pre>

    <p><a href="/Aptus/login" style="display:inline-block;margin-top:12px;padding:10px 22px;background:#006577;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;">Ir para o login</a></p>

    <div class="alerta">
        ⚠️ APAGUE este arquivo (<code>public/_update_pass.php</code>) e também o <code>public/_diag_db.php</code> depois de usar.
    </div>
</div>
</body>
</html>