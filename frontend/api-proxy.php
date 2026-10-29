<?php
// 1. Manejo único y obligatorio de CORS para el proxy
header('Access-Control-Allow-Origin: http://www.pasteleria.com');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Responder de inmediato al Preflight OPTIONS del navegador
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$endpoint = $_GET['endpoint'] ?? $_GET['e'] ?? '';
$endpoint = ltrim($endpoint, '/');

$apiBase = 'http://api.pasteleria/';
$url = rtrim($apiBase, '/') . '/' . $endpoint;

$method = $_SERVER['REQUEST_METHOD'];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);

// Forward all cookies
$cookieHeader = [];
foreach ($_COOKIE as $name => $value) {
    $cookieHeader[] = $name . '=' . $value;
}
if (!empty($cookieHeader)) {
    curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookieHeader));
}

// CORRECCIÓN CLAVE: Retransmitir todas las cabeceras esenciales del cliente (Content-Type y Authorization)
$headers = [];
$headers[] = 'Origin: http://www.pasteleria.com';

if (isset($_SERVER['CONTENT_TYPE'])) {
    $headers[] = 'Content-Type: ' . $_SERVER['CONTENT_TYPE'];
}

// Capturar el token de autenticación si el frontend lo envía por los Headers
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $headers[] = 'Authorization: ' . $_SERVER['HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $requestHeaders = apache_request_headers();
    if (isset($requestHeaders['Authorization'])) {
        $headers[] = 'Authorization: ' . $requestHeaders['Authorization'];
    }
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
    $body = file_get_contents('php://input');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
}

$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$rawHeaders = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

// Rewrite Set-Cookie headers for same origin
if (preg_match_all('/Set-Cookie:\s*([^\r\n]+)/i', $rawHeaders, $matches)) {
    foreach ($matches[1] as $cookie) {
        $cookie = preg_replace('/;\s*domain=[^;]+/i', '', $cookie);
        $cookie = preg_replace('/;\s*Secure\b/i', '', $cookie);
        $cookie = preg_replace('/;\s*SameSite\s*=\s*None/i', '; SameSite=Lax', $cookie);
        if (stripos($cookie, 'samesite') === false) {
            $cookie .= '; SameSite=Lax';
        }
        header('Set-Cookie: ' . trim($cookie), false);
    }
}

http_response_code($httpCode);
header('Content-Type: application/json; charset=utf-8');
echo $body;
