<?php

require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../AuthSession.php';

class LoginController
{
    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data) || !is_string($data['Username'] ?? null) || !is_string($data['Password'] ?? null)) {
            return $this->respond(['estado' => false, 'error' => 'Ingresa un usuario y una contraseña'], 400);
        }

        $username = trim($data['Username']);
        $user = $username === '' ? null : Usuario::findByUsername($username);
        if (!$user || !password_verify($data['Password'], $user['Password_hash'])) {
            return $this->respond(['estado' => false, 'error' => 'Usuario o contraseña incorrectos'], 401);
        }

        AuthSession::login($user['id']);

        return $this->respond([
            'estado' => true,
            'message' => 'Sesión iniciada correctamente',
            'user' => [
                'id' => (int) $user['id'],
                'Username' => $user['Username'],
                'Permiso' => $user['Permiso']
            ]
        ]);
    }

    public function me()
    {
        return $this->respond(['estado' => true, 'user' => AuthSession::currentUser()]);
    }

    public function logout()
    {
        AuthSession::destroy();
        return $this->respond(['estado' => true, 'message' => 'Sesión cerrada']);
    }

    private function respond($body, $status = 200)
    {
        http_response_code($status);
        echo json_encode($body, JSON_UNESCAPED_UNICODE);
    }
}
