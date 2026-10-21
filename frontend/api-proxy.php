<?php

declare(strict_types=1);

$path = $_GET['path'] ?? '';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];

if (!is_string($path) || !preg_match('~\A[a-zA-Z0-9_./-]*\z~D', $path) || str_contains($path, '..') || !in_array($method, $allowedMethods, true)) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['estado' => false, 'error' => 'Solicitud inválida'], JSON_UNESCAPED_UNICODE);
    exit;
}

$curl = curl_init('http://api.pasteleria/' . ltrim($path, '/'));
$responseHeaders = [];
$requestHeaders = [];
$isMultipart = str_starts_with(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'multipart/form-data');
foreach (['CONTENT_TYPE' => 'Content-Type', 'HTTP_ACCEPT' => 'Accept', 'HTTP_AUTHORIZATION' => 'Authorization'] as $serverKey => $headerName) {
    if ($isMultipart && $serverKey === 'CONTENT_TYPE') continue;
    if (!empty($_SERVER[$serverKey])) $requestHeaders[] = $headerName . ': ' . $_SERVER[$serverKey];
}
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_HTTPHEADER => $requestHeaders,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HEADERFUNCTION => static function ($handle, string $header) use (&$responseHeaders): int {
        $length = strlen($header);
        $separator = strpos($header, ':');
        if ($separator !== false) {
            $name = strtolower(trim(substr($header, 0, $separator)));
            if (in_array($name, ['content-type', 'set-cookie', 'cache-control'], true)) {
                $responseHeaders[] = [trim(substr($header, 0, $separator)), trim(substr($header, $separator + 1))];
            }
        }
        return $length;
    },
]);

if ($isMultipart && $method === 'POST') {
    // PHP parses multipart bodies into $_POST and $_FILES, so php://input can be empty here.
    $formFields = $_POST;
    foreach ($_FILES as $fieldName => $file) {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) continue;
        $formFields[$fieldName] = new CURLFile(
            $file['tmp_name'],
            $file['type'] ?: 'application/octet-stream',
            $file['name'] ?: basename($file['tmp_name'])
        );
    }
    curl_setopt($curl, CURLOPT_POSTFIELDS, $formFields);
} elseif (in_array($method, ['POST', 'PUT'], true)) {
    curl_setopt($curl, CURLOPT_POSTFIELDS, file_get_contents('php://input'));
}

if (!empty($_SERVER['HTTP_COOKIE'])) {
    curl_setopt($curl, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE']);
}

$body = curl_exec($curl);
$status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
$error = curl_error($curl);
curl_close($curl);

if ($body === false) {
    error_log('Error al conectar con la API desde el proxy: ' . $error);
    http_response_code(502);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['estado' => false, 'error' => 'No se pudo conectar con el servidor'], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code($status ?: 502);
foreach ($responseHeaders as [$name, $value]) header($name . ': ' . $value, $name !== 'Set-Cookie');
echo $body;
