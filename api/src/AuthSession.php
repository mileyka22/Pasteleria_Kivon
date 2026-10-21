<?php

require_once __DIR__ . '/Models/Usuario.php';

class AuthSession
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        ini_set('session.use_strict_mode', '1');
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_name('KIVONSESSID');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }

    public static function currentUser()
    {
        self::start();
        $id = $_SESSION['user_id'] ?? null;
        if (!$id) return null;

        $user = Usuario::findSafe($id);
        if (!$user) {
            self::destroy();
            return null;
        }

        return [
            'id' => (int) $user['id'],
            'Username' => $user['Username'],
            'Permiso' => $user['Permiso']
        ];
    }

    public static function login($userId)
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $userId;
    }

    public static function destroy()
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax'
            ]);
        }
        session_destroy();
    }
}
