<?php
header('Content-Type: application/json; charset=utf-8');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allowedOrigins = [
    'https://pasteleria-kivon-1.onrender.com',
    'https://pasteleria-kivon.onrender.com', // Añadida por seguridad si usas el subdominio previo
    'http://www.pasteleria.com',
    'http://api.pasteleria' // Añadido el dominio interno de tu API en WampServer
];

// Expresión regular corregida para capturar también dominios locales sin terminación .local/.test
$isLocalOrigin = preg_match(
    '/^https?:\/\/(localhost|127\.0\.0\.1|\[::1\]|[a-z0-9-]+(\.(local|test|pasteleria))?)(:\d+)?$/i',
    $origin
) === 1;

if (in_array($origin, $allowedOrigins, true) || $isLocalOrigin) {
    // Forzamos a limpiar cualquier cabecera previa que Apache (.htaccess) haya duplicado
    header('Access-Control-Allow-Origin: ' . $origin, true);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS', true);
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With', true);
    header("Vary: Origin", true);
    header('Access-Control-Allow-Credentials: true', true);
}

// Responder inmediatamente a las peticiones preflight OPTIONS del navegador
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// header('Content-Type: application/json; charset=utf-8');

// $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// $allowedOrigins = [
//     'https://pasteleria-kivon-1.onrender.com',
//     'http://www.pasteleria.com'
// ];

// $isLocalOrigin = preg_match(
//     '/^https?:\/\/(localhost|127\.0\.0\.1|\[::1\]|[a-z0-9-]+\.(local|test))(:\d+)?$/i',
//     $origin
// ) === 1;

// if (in_array($origin, $allowedOrigins, true) || $isLocalOrigin) {
//     header('Access-Control-Allow-Origin: ' . $origin);
//     header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
//     header('Access-Control-Allow-Headers: Content-Type, Authorization');
//     header("Vary: Origin");
//     header('Access-Control-Allow-Credentials: true');
// }


// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//     http_response_code(204);
//     exit;
// }

require_once "../src/Router.php";
require_once "../src/AuthSession.php";
require_once "../src/Controllers/UsuarioController.php";
require_once "../src/Controllers/ProductoController.php";
require_once "../src/Controllers/ClientesController.php";
require_once "../src/Controllers/PedidoController.php";
require_once "../src/Controllers/VentaController.php";
require_once "../src/Controllers/Producto_pedidoController.php";
require_once "../src/Controllers/PersonalController.php";
require_once "../src/Controllers/LoginController.php";

use App\Router;

$route = new Router();
$route->add('POST', '/auth/login', 'LoginController@login');
$route->add('GET', '/auth/me', 'LoginController@me');
$route->add('POST', '/auth/logout', 'LoginController@logout');

// Dirección para usuarios
$route->add('GET', '/', 'UsuarioController@getAll');
$route->add('GET', '/usuario', 'UsuarioController@getAll');
$route->add('GET', '/usuario/{id}', 'UsuarioController@getById');
$route->add('POST', '/usuario', 'UsuarioController@add');
$route->add('POST', '/login', 'UsuarioController@login');
$route->add('PUT', '/usuario/{id}', 'UsuarioController@update');
$route->add('DELETE', '/usuario/{id}', 'UsuarioController@delete');

// Direcciones de productos
$route->add('GET', '/productos', 'ProductoController@getAll');
$route->add('GET', '/productos/{id}', 'ProductoController@getById');
$route->add('POST', '/productos', 'ProductoController@add');
$route->add('PUT', '/productos/{id}', 'ProductoController@update');
$route->add('POST', '/productos/{id}', 'ProductoController@update');
$route->add('DELETE', '/productos/{id}', 'ProductoController@delete');

// Direcciones de clientes
$route->add('GET', '/clientes', 'ClientesController@getAll');
$route->add('POST', '/clientes', 'ClientesController@add');
$route->add('PUT', '/clientes/{id}', 'ClientesController@update');
$route->add('DELETE', '/clientes/{id}', 'ClientesController@delete');

// Direcciones de pedidos
$route->add('GET', '/pedido', 'PedidoController@getAll');
$route->add('GET', '/pedido/{id}', 'PedidoController@getById');
$route->add('POST', '/pedido', 'PedidoController@add');
$route->add('PUT', '/pedido/{id}', 'PedidoController@update');
$route->add('PUT', '/pedido/{id}/estado', 'PedidoController@updateStatus');
$route->add('DELETE', '/pedido/{id}', 'PedidoController@delete');

// Direcciones de ventas
$route->add('GET', '/dashboard', 'VentaController@getDashboard');
$route->add('GET', '/venta', 'VentaController@getAll');
$route->add('GET', '/venta/reporte', 'VentaController@reporteVentas');
$route->add('POST', '/venta', 'VentaController@add');
$route->add('PUT', '/venta/{id}', 'VentaController@update');
$route->add('DELETE', '/venta/{id}', 'VentaController@delete');

// Direcciones de productos por pedido
$route->add('GET', '/producto_pedido', 'Producto_pedidoController@getAll');
$route->add('POST', '/producto_pedido', 'Producto_pedidoController@add');
$route->add('PUT', '/producto_pedido/{id}', 'Producto_pedidoController@update');
$route->add('DELETE', '/producto_pedido/{id}', 'Producto_pedidoController@delete');

// Direcciones de personal
$route->add('GET', '/personal', 'PersonalController@getAll');
$route->add('POST', '/personal', 'PersonalController@add');
$route->add('PUT', '/personal/{id}', 'PersonalController@update');
$route->add('DELETE', '/personal/{id}', 'PersonalController@delete');

// consulta de ventas realizadas entre fechas
 $route->add('POST', '/reporteVenta', 'VentaController@reporteVentas');
try {
    $route->run();
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    echo json_encode([
        'estado' => false,
        'error' => 'No se pudo completar la operación. Revisa la conexión y los datos enviados.'
    ], JSON_UNESCAPED_UNICODE);
}
