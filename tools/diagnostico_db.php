<?php
// tools/diagnostico_db.php — descobrir por que o banco não conecta

header('Content-Type: text/html; charset=utf-8');
echo '<pre style="font-family:monospace;background:#0f172a;color:#e2e8f0;padding:20px;font-size:14px;">';

require_once __DIR__ . '/../app/Config/config.php';
Config::load();

echo "=== 1. Variáveis do .env ===\n";
echo "APP_ENV : " . var_export(Config::get('APP_ENV'), true) . "\n";
echo "DB_HOST : " . var_export(Config::get('DB_HOST'), true) . "\n";
echo "DB_PORT : " . var_export(Config::get('DB_PORT'), true) . "\n";
echo "DB_NAME : " . var_export(Config::get('DB_NAME'), true) . "\n";
echo "DB_USER : " . var_export(Config::get('DB_USER'), true) . "\n";
echo "DB_PASS : " . (Config::get('DB_PASS') === '' ? '"(vazio)"' : '"(preenchido)"') . "\n\n";

echo "=== 2. Arquivo .env existe? ===\n";
$envFile = __DIR__ . '/../.env';
echo $envFile . "\n";
echo file_exists($envFile) ? "SIM\n\n" : "NÃO — o arquivo .env não existe!\n\n";

echo "=== 3. Extensão PDO MySQL carregada? ===\n";
echo extension_loaded('pdo_mysql') ? "SIM\n\n" : "NÃO — habilite pdo_mysql no php.ini!\n\n";

echo "=== 4. Testando conexão direta ===\n";
$host = Config::get('DB_HOST') ?: '127.0.0.1';
$port = Config::get('DB_PORT') ?: '3306';
$db   = Config::get('DB_NAME') ?: 'Aptus';
$user = Config::get('DB_USER') ?: 'root';
$pass = Config::get('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Conexão com MySQL (sem db): OK\n";

    $stmt = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($db));
    $existe = $stmt->fetch();

    if ($existe) {
        echo "Banco '{$db}': EXISTE\n";

        $pdo2 = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        echo "Conexão com o banco '{$db}': OK\n\n";

        echo "=== 5. Tabelas existentes ===\n";
        $tabelas = $pdo2->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($tabelas)) {
            echo "NENHUMA TABELA — importe o aptus_bd.sql!\n";
        } else {
            echo count($tabelas) . " tabela(s):\n";
            foreach ($tabelas as $t) {
                echo "  - $t\n";
            }

            echo "\n=== 6. Usuários cadastrados ===\n";
            $stmt = $pdo2->query("SELECT id_usuario, nome, email, id_perfil FROM usuario");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($users)) {
                echo "Nenhum usuário cadastrado.\n";
            } else {
                foreach ($users as $u) {
                    echo "  #{$u['id_usuario']} — {$u['email']} ({$u['nome']}, perfil {$u['id_perfil']})\n";
                }
            }
        }
    } else {
        echo "Banco '{$db}': NÃO EXISTE — importe o aptus_bd.sql!\n";
    }
} catch (PDOException $e) {
    echo "ERRO REAL DO PDO:\n";
    echo "  Código: " . $e->getCode() . "\n";
    echo "  Mensagem: " . $e->getMessage() . "\n";
}

echo '</pre>';