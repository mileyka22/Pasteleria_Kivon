<?php

require_once "../src/Models/Usuario.php";

class UsuarioController
{
    public function getAll()
    {
        $usuario = Usuario::all();

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

    private function validarUsuario($data)
    {
        $errores = [];

        if (!isset($data['Username']) || trim($data['Username']) == "") {
            $errores['Username'] = "El campo Username es obligatorio";
        }

        if (!isset($data['Permiso']) || trim($data['Permiso']) == "") {
            $errores['Permiso'] = "El campo Permiso es obligatorio";
        }

        if (!isset($data['Password_hash']) || trim($data['Password_hash']) == "") {
            $errores['Password_hash'] = "El campo Password_hash es obligatorio";
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
        $errores = $this->validarUsuario($data);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

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

        echo json_encode($usuario);
    }
}