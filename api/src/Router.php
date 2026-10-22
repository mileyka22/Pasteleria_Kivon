<?php

namespace App;

class Router
{
    protected $routes = [];
    public function add($method, $route, $handler)
    {
        $this->routes[] = ['method' => $method, 'route' => $route, 'handler' => $handler];
    }

    public function run()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        foreach ($this->routes as $route) {
            $pattern = str_replace(['{id}', '/'], ['([0-9]+)', '\\/'], $route['route']);
            if ($method === $route['method'] && preg_match('/^' . $pattern . '$/', $uri, $matches)) {
                $public = ['POST /auth/login', 'GET /auth/me', 'POST /auth/logout'];
                $user = in_array($method . ' ' . $uri, $public, true) ? null : \AuthSession::currentUser();
                if (!in_array($method . ' ' . $uri, $public, true) && !$user) {
                    http_response_code(401);
                    echo json_encode(['estado' => false, 'error' => 'Authentication required'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                if ($user && !$this->allowed($method, $uri, $user['Permiso'])) {
                    http_response_code(403);
                    echo json_encode(['estado' => false, 'error' => 'Forbidden'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                array_shift($matches);
                [$controllerName, $methodName] = explode('@', $route['handler']);
                return call_user_func_array([new $controllerName(), $methodName], $matches);
            }
        }
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada'], JSON_UNESCAPED_UNICODE);
    }

    private function allowed($method, $uri, $role)
    {
        $role = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($role)));
        if ($role === 'administrador') return true;
        $rules = [
            'ventas' => ['GET /dashboard', 'GET /pedido', 'GET /pedido/{id}', 'POST /pedido', 'PUT /pedido/{id}', 'GET /clientes', 'POST /clientes', 'PUT /clientes/{id}', 'DELETE /clientes/{id}', 'GET /productos', 'GET /productos/{id}', 'POST /productos', 'POST /productos/{id}', 'PUT /productos/{id}', 'DELETE /productos/{id}', 'GET /personal', 'GET /venta', 'POST /venta', 'PUT /venta/{id}', 'DELETE /venta/{id}'],
            'produccion' => ['GET /dashboard', 'GET /pedido', 'GET /pedido/{id}', 'PUT /pedido/{id}/estado', 'GET /productos', 'GET /productos/{id}', 'POST /productos', 'POST /productos/{id}', 'PUT /productos/{id}', 'DELETE /productos/{id}']
        ];
        foreach ($rules[$role] ?? [] as $rule) {
            $regex = '#^' . str_replace(['{id}', '/'], ['[0-9]+', '\\/'], $rule) . '$#';
            if (preg_match($regex, $method . ' ' . $uri)) return true;
        }
        return false;
    }
}
