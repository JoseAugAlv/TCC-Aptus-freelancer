<?php
// app/Config/database.php
require_once __DIR__ . '/config.php';

class Database
{
    private static $connection = null;

    // ============================================================
    // CACHE DE QUERIES
    // ============================================================

    /** @var array<string, array{data:mixed, expires:int}> Cache em memória (fallback) */
    private static $memoryCache = [];

    /** Tamanho máximo do cache em memória (evita crescimento infinito) */
    private static $maxMemoryEntries = 200;

    /**
     * Gera chave determinística para o cache a partir da SQL + parâmetros.
     */
    private static function cacheKey(string $sql, array $params = []): string
    {
        return 'aptus_db_' . md5($sql . '|' . serialize($params));
    }

    /**
     * Recupera do cache, se existir e não tiver expirado.
     */
    private static function cacheGet(string $key)
    {
        // 1) APCu (preferencial)
        if (function_exists('apcu_fetch')) {
            $ok  = false;
            $val = apcu_fetch($key, $ok);
            if ($ok) return $val;
        }

        // 2) Fallback em memória
        if (isset(self::$memoryCache[$key])) {
            $entry = self::$memoryCache[$key];
            if ($entry['expires'] > time()) {
                return $entry['data'];
            }
            unset(self::$memoryCache[$key]);
        }

        return null;
    }

    /**
     * Armazena no cache com TTL.
     */
    private static function cacheSet(string $key, $data, int $ttl): void
    {
        if ($ttl <= 0) return;

        if (function_exists('apcu_store')) {
            apcu_store($key, $data, $ttl);
            return;
        }

        if (count(self::$memoryCache) >= self::$maxMemoryEntries) {
            array_shift(self::$memoryCache);
        }

        self::$memoryCache[$key] = [
            'data'    => $data,
            'expires' => time() + $ttl,
        ];
    }

    /**
     * Invalida todo o cache (usar após INSERT/UPDATE/DELETE).
     */
    public static function clearCache(): void
    {
        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }
        self::$memoryCache = [];
    }

    /**
     * Invalida uma query específica do cache.
     */
    public static function invalidateCache(string $sql, array $params = []): void
    {
        $key = self::cacheKey($sql, $params);
        if (function_exists('apcu_delete')) {
            apcu_delete($key);
        }
        unset(self::$memoryCache[$key]);
    }

    // ============================================================
    // CONEXÃO
    // ============================================================

    public static function getConnection()
    {
        if (self::$connection === null) {
            $host   = Config::get('DB_HOST') ?: '127.0.0.1';
            $port   = Config::get('DB_PORT') ?: '3306';
            $dbname = Config::get('DB_NAME') ?: 'Aptus';
            $user   = Config::get('DB_USER') ?: 'root';
            $pass   = Config::get('DB_PASS') ?: '';

            if (empty($dbname)) {
                throw new RuntimeException('Configuração de banco incompleta.');
            }

            try {
                self::$connection = new PDO(
                    "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                        PDO::ATTR_TIMEOUT            => 5,
                    ]
                );

                if (Config::get('APP_ENV') !== 'production') {
                    error_log("DB conectado em {$host}:{$port} - {$dbname}");
                }

            } catch (PDOException $e) {
                error_log('Falha de conexão MySQL: ' . $e->getMessage());

                throw new RuntimeException(
                    'Serviço temporariamente indisponível. Tente novamente em instantes.',
                    0,
                    $e
                );
            }
        }

        return self::$connection;
    }

    public static function isConnected()
    {
        try {
            if (self::$connection === null) return false;
            self::$connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function disconnect()
    {
        self::$connection = null;
        self::$memoryCache = [];
    }

    public static function lastInsertId()
    {
        if (self::$connection === null) {
            throw new RuntimeException('Conexão não estabelecida');
        }
        return self::$connection->lastInsertId();
    }

    public static function beginTransaction()
    {
        if (self::$connection === null) {
            throw new RuntimeException('Conexão não estabelecida');
        }
        return self::$connection->beginTransaction();
    }

    public static function commit()
    {
        if (self::$connection === null) {
            throw new RuntimeException('Conexão não estabelecida');
        }
        return self::$connection->commit();
    }

    public static function rollBack()
    {
        if (self::$connection === null) {
            throw new RuntimeException('Conexão não estabelecida');
        }
        return self::$connection->rollBack();
    }

    public static function inTransaction()
    {
        if (self::$connection === null) return false;
        return self::$connection->inTransaction();
    }

    // ============================================================
    // QUERIES
    // ============================================================

    public static function query($sql, $params = [])
{
    if (self::$connection === null) {
        throw new RuntimeException('Conexão não estabelecida');
    }

    $stmt = self::$connection->prepare($sql);

    if (!empty($params)) {
        // Detecta se o array é posicional (chaves 0,1,2...) ou nomeado (':id', 'id')
        $isPositional = array_keys($params) === range(0, count($params) - 1);

        if ($isPositional) {
            // PDO conta posições a partir de 1 → execute() resolve sozinho
            $stmt->execute(array_values($params));
        } else {
            foreach ($params as $key => $value) {
                // Garante o ":" no início para bind nomeado
                $placeholder = (strpos($key, ':') === 0) ? $key : ':' . $key;

                $type = PDO::PARAM_STR;
                if (is_int($value))       $type = PDO::PARAM_INT;
                elseif (is_bool($value))  $type = PDO::PARAM_BOOL;
                elseif (is_null($value))  $type = PDO::PARAM_NULL;

                $stmt->bindValue($placeholder, $value, $type);
            }
            $stmt->execute();
        }
    } else {
        $stmt->execute();
    }

    // Escrita → invalida cache global
    if (preg_match('/^\s*(INSERT|UPDATE|DELETE|REPLACE|TRUNCATE|ALTER|DROP)/i', $sql)) {
        self::clearCache();
    }

    return $stmt;
}

    /**
     * fetchAll com cache OPCIONAL.
     *   - $ttl = 0  → sem cache
     *   - $ttl > 0  → cacheia pelo número de segundos
     */
    public static function fetchAll($sql, $params = [], int $ttl = 0)
    {
        if ($ttl <= 0) {
            return self::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
        }

        $key    = self::cacheKey($sql, $params);
        $cached = self::cacheGet($key);
        if ($cached !== null) {
            return $cached;
        }

        $data = self::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
        self::cacheSet($key, $data, $ttl);
        return $data;
    }

    public static function fetchOne($sql, $params = [], int $ttl = 0)
    {
        if ($ttl <= 0) {
            return self::query($sql, $params)->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $key    = self::cacheKey($sql, $params);
        $cached = self::cacheGet($key);
        if ($cached !== null) {
            return $cached ?: null;
        }

        $data = self::query($sql, $params)->fetch(PDO::FETCH_ASSOC) ?: null;
        self::cacheSet($key, $data, $ttl);
        return $data;
    }

    public static function fetchColumn($sql, $params = [], int $ttl = 0)
    {
        if ($ttl <= 0) {
            return self::query($sql, $params)->fetchColumn();
        }

        $key    = self::cacheKey($sql, $params);
        $cached = self::cacheGet($key);
        if ($cached !== null) {
            return $cached;
        }

        $data = self::query($sql, $params)->fetchColumn();
        self::cacheSet($key, $data, $ttl);
        return $data;
    }

    // ============================================================
    // DIAGNÓSTICO
    // ============================================================

    public static function getStats()
    {
        if (self::$connection === null) {
            return ['connected' => false];
        }
        try {
            return [
                'connected'         => true,
                'server_info'       => self::$connection->getAttribute(PDO::ATTR_SERVER_VERSION),
                'client_info'       => self::$connection->getAttribute(PDO::ATTR_CLIENT_VERSION),
                'connection_status' => self::$connection->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            ];
        } catch (PDOException $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }
}