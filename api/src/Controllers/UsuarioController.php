<?php

require_once "../src/Models/Usuario.php";

class UsuarioController
{
    public function getAll()
    {
        $usuario = Usuario::all();

        echo json_encode($usuario);
    }

    public function getById($id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(["error" => "Usuario no encontrado"]);
            return;
        }

        echo json_encode($usuario);
    }

    private function getJsonData()
    {
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            return [
                "data" => null,
                "errores" => [
                    "json" => json_last_error_msg()
                ]
            ];
        }

        if (!is_array($data)) {
            return [
                "data" => null,
                "errores" => [
                    "json" => "Debe enviar datos en formato JSON"
                ]
            ];
        }

        return [
            "data" => $data,
            "errores" => []
        ];
    }

    private function validarUsuario($data, $esNuevo = false)
    {
        $errores = [];

        $camposPermitidos = $esNuevo
            ? ['Username', 'Permiso', 'Password']
            : ['Username', 'Permiso'];
        foreach (array_keys($data) as $campo) {
            if (!in_array($campo, $camposPermitidos, true)) {
                $errores[$campo] = "El campo no se puede modificar";
            }
        }

        if (!isset($data['Username']) || !is_string($data['Username']) || trim($data['Username']) === "") {
            $errores['Username'] = "El campo Username es obligatorio";
        } elseif (strlen(trim($data['Username'])) > 50) {
            $errores['Username'] = "Username no puede superar 50 caracteres";
        }

        if (!isset($data['Permiso']) || !is_string($data['Permiso']) || trim($data['Permiso']) === "") {
            $errores['Permiso'] = "El campo Permiso es obligatorio";
        } elseif (strlen(trim($data['Permiso'])) > 100) {
            $errores['Permiso'] = "Permiso no puede superar 100 caracteres";
        }

        if ($esNuevo) {
            if (!isset($data['Password']) || !is_string($data['Password']) || trim($data['Password']) === "") {
                $errores['Password'] = "El campo contraseña es obligatorio";
            } elseif (strlen($data['Password']) < 6) {
                $errores['Password'] = "La contraseña debe tener al menos 6 caracteres";
            }
        }

        return $errores;
    }

    public function add()
    {
        $json = $this->getJsonData();

        if (!empty($json['errores'])) {
            echo json_encode([
                "estado" => false,
                "errores" => $json['errores']
            ]);
            return;
        }

        $data = $json['data'];
        $errores = $this->validarUsuario($data, true);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

        $data['Username'] = trim($data['Username']);
        $data['Permiso'] = trim($data['Permiso']);
        $data['Password_hash'] = password_hash($data['Password'], PASSWORD_DEFAULT);
        unset($data['Password']);
        $usuario = Usuario::add($data);

        if ($usuario) {
            echo json_encode([
                "estado" => true,
                "message" => "Usuario agregado correctamente"
            ]);
            return;
        }

        echo json_encode($usuario);
    }

    public function update($id)
    {
        $json = $this->getJsonData();

        if (!empty($json['errores'])) {
            echo json_encode([
                "estado" => false,
                "errores" => $json['errores']
            ]);
            return;
        }

        $data = $json['data'];
        $errores = $this->validarUsuario($data);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

        if (!Usuario::find($id)) {
            http_response_code(404);
            echo json_encode([
                "estado" => false,
                "error" => "Usuario no encontrado"
            ]);
            return;
        }

        $data['Username'] = trim($data['Username']);
        $data['Permiso'] = trim($data['Permiso']);

        $usuario = Usuario::update($id, $data);

        if ($usuario) {
            echo json_encode([
                "estado" => true,
                "message" => "Usuario actualizado correctamente"
            ]);
            return;
        }

        echo json_encode($usuario);
    }

    public function delete($id)
    {
        $usuario = Usuario::delete($id);

        if ($usuario) {
            echo json_encode([
                "estado" => true,
                "message" => "Usuario eliminado correctamente"
            ]);
            return;
        }

        http_response_code(404);
        echo json_encode([
            "estado" => false,
            "error" => "Usuario no encontrado"
        ]);
    }
}
