<?php
// app/Core/Router.php

class Router
{
    private $routes = [
        'GET' => [],
        'POST' => []
    ];

    public function get($uri, $action, $roles = [])
    {
        $this->routes['GET'][$uri] = [
            'action' => $action,
            'roles' => $roles
        ];
    }

    public function post($uri, $action, $roles = [])
    {
        $this->routes['POST'][$uri] = [
            'action' => $action,
            'roles' => $roles
        ];
    }

    public function dispatch($requestUri)
    {
        $path = parse_url($requestUri, PHP_URL_PATH);

        $basePath = '/Aptus';
        $path = str_replace($basePath, '', $path);

        if ($path === '') {
            $path = '/';
        }

        $method = $_SERVER['REQUEST_METHOD'];

        $route = $this->routes[$method][$path] ?? null;

        $params = [];

        if (!$route) {
            foreach ($this->routes[$method] as $routePath => $routeData) {
                if (str_contains($routePath, '{')) {
                    $pattern = preg_replace(
                        '#\{[a-zA-Z0-9_]+\}#',
                        '([a-zA-Z0-9\-]+)',
                        $routePath
                    );
                    $pattern = "#^" . $pattern . "$#";

                    if (preg_match($pattern, $path, $matches)) {
                        array_shift($matches);
                        $route = $routeData;
                        $params = $matches;
                        break;
                    }
                }
            }
        }

        if (!$route) {
            http_response_code(404);
            echo "<h1>404 - Rota não encontrada</h1>";
            return;
        }

        $action = $route['action'];
        $roles = $route['roles'];

        // ============================================================
        // VERIFICAÇÃO DE PERMISSÕES
        // ============================================================
        if (!empty($roles)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $usuario = $_SESSION['usuario'] ?? null;

            if (!$usuario) {
                header('Location: /Aptus/login');
                exit;
            }

            $roleUsuario = (int) ($usuario['role'] ?? 0);
            $rolesPermitidos = array_map('intval', $roles);

            // Master (4) tem acesso a TUDO
            if ($roleUsuario !== 4 && !in_array($roleUsuario, $rolesPermitidos, true)) {
                http_response_code(403);
                echo "<h1>403 - Acesso Negado</h1>";
                echo "<p>Seu perfil: " . $roleUsuario . "</p>";
                echo "<p>Perfis permitidos: " . implode(', ', $rolesPermitidos) . "</p>";
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

        $controllerInstance->$methodName(...$params);
    }
}