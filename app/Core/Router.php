<?php
// app/Core/Router.php

class Router
{
    private $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    private static $routeCache = [];

    public function get($uri, $action, $roles = [])
    {
        if (isset($this->routes['GET'][$uri])) {
            throw new \RuntimeException("Rota GET duplicada: {$uri}");
        }
        $this->routes['GET'][$uri] = ['action' => $action, 'roles' => $roles];
    }

    public function post($uri, $action, $roles = [])
    {
        if (isset($this->routes['POST'][$uri])) {
            throw new \RuntimeException("Rota POST duplicada: {$uri}");
        }
        $this->routes['POST'][$uri] = ['action' => $action, 'roles' => $roles];
    }

    public function dispatch($requestUri)
    {
        $path = parse_url($requestUri, PHP_URL_PATH);

        // BasePath dinâmico
        $appUrl   = \Config::get('APP_URL', '/Aptus');
        $basePath = parse_url($appUrl, PHP_URL_PATH) ?: '/Aptus';
        $basePath = rtrim($basePath, '/');

        if ($basePath !== '' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        if ($path === '' || $path === false) {
            $path = '/';
        }

        $method = $_SERVER['REQUEST_METHOD'];

        $cacheKey = $method . ':' . $path;
        $params   = [];
        $route    = null;

        // 1) Rota estática (com ou sem cache)
        if (isset($this->routes[$method][$path])) {
            $route = $this->routes[$method][$path];
        } else {
            // 2) Rota dinâmica (com params)
            foreach ($this->routes[$method] as $routePath => $routeData) {
                if (!str_contains($routePath, '{')) continue;

                $pattern = preg_replace('#\{[a-zA-Z0-9_]+\}#', '([a-zA-Z0-9\-]+)', $routePath);
                $pattern = "#^" . $pattern . "$#";

                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches);
                    $route            = $routeData;
                    $route['pattern'] = $pattern;
                    $params           = $matches;
                    break;
                }
            }
        }

        if (!$route) {
            http_response_code(404);
            $tituloPagina = 'Página não encontrada - Aptus';
            $cssPagina    = 'erro.css';
            require_once __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $action = $route['action'];
        $roles  = $route['roles'];

        // 3) Controle de acesso
        if (!empty($roles)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $usuario = $_SESSION['usuario'] ?? null;

            $querJson = (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
                     || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
                     || (strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

            if (!$usuario) {
                if ($querJson) {
                    http_response_code(401);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'error' => 'Não autenticado']);
                    exit;
                }
                header('Location: /Aptus/login');
                exit;
            }

            $roleUsuario     = (int) ($usuario['role'] ?? 0);
            $rolesPermitidos = array_map('intval', $roles);

            if ($roleUsuario !== 4 && !in_array($roleUsuario, $rolesPermitidos, true)) {
                if ($querJson) {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
                    exit;
                }
                http_response_code(403);
                echo "<h1>403 - Acesso Negado</h1>";
                echo '<p><a href="/Aptus/">Voltar para o início</a></p>';
                exit;
            }
        }

        if (!str_contains($action, '@')) {
            http_response_code(500);
            echo "<h1>Rota inválida</h1>";
            return;
        }

        [$controller, $methodName] = explode('@', $action);

        $controllerPath = dirname(__DIR__) . "/Controllers/{$controller}.php";

        if (!file_exists($controllerPath)) {
            http_response_code(500);
            echo "<h1>Controller não encontrado: {$controller}</h1>";
            return;
        }

        require_once $controllerPath;

        $controllerInstance = new $controller();

        if (!method_exists($controllerInstance, $methodName)) {
            http_response_code(500);
            echo "<h1>Método não encontrado: {$methodName}</h1>";
            return;
        }

        if ($method === 'POST') {
            require_once __DIR__ . '/../Middleware/CsrfMiddleware.php';
            CsrfMiddleware::validate();
        }

        $controllerInstance->$methodName(...$params);
    }
}