<?php

require_once __DIR__ . "/../Models/Personal.php";

class PersonalController
{
    // Consultar todos
    public static function getAll()
    {
        $result = Personal::all();

        header('Content-Type: application/json');

        echo json_encode($result);
    }

    // Obtener datos JSON
    private static function getJsonData()
    {
        $json = file_get_contents("php://input");

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            header('Content-Type: application/json');

            echo json_encode([
                "error" => "JSON invalido"
            ]);

            exit;
        }

        return $data;
    }

    // Validar personal
    private static function validarPersonal($data)
    {
        $errores = [];

        // Nombre
        if (!isset($data['Nombre']) || trim($data['Nombre']) == "") {
            $errores['Nombre'] = "El campo Nombre es obligatorio";
        }

        // Cargo
        if (!isset($data['Cargo']) || trim($data['Cargo']) == "") {
            $errores['Cargo'] = "El campo Cargo es obligatorio";
        }

        // Salario
        if (!isset($data['Salario']) || trim($data['Salario']) == "") {
            $errores['Salario'] = "El campo Salario es obligatorio";
        } elseif (!is_numeric($data['Salario'])) {
            $errores['Salario'] = "El campo Salario debe ser numerico";
        }

        // Telefono
        if (isset($data['Telefono']) && $data['Telefono'] !== "") {
            if (!is_numeric($data['Telefono'])) {
                $errores['Telefono'] = "El campo Telefono debe ser numerico";
            }
        }

        // id_usuario
        if (isset($data['id_usuario']) && $data['id_usuario'] !== "") {
            if (!is_numeric($data['id_usuario'])) {
                $errores['id_usuario'] = "El campo id_usuario debe ser numerico";
            }
        }

        return $errores;
    }

    // Actualizar
    public static function update($id)
    {
        $data = self::getJsonData();

        $errores = self::validarPersonal($data);

        if (!empty($errores)) {
            header('Content-Type: application/json');
            http_response_code(400);

            echo json_encode([
                "errores" => $errores
            ]);

            return;
        }

        $result = Personal::update($id, $data);

        header('Content-Type: application/json');

        echo json_encode([
            "mensaje" => "Personal actualizado correctamente",
            "resultado" => $result
        ]);
    }

    // Agregar
    public static function add()
    {
        $data = self::getJsonData();

        $errores = self::validarPersonal($data);

        if (!empty($errores)) {
            header('Content-Type: application/json');
            http_response_code(400);

            echo json_encode([
                "errores" => $errores
            ]);

            return;
        }

        $result = Personal::add($data);

        header('Content-Type: application/json');

        echo json_encode([
            "mensaje" => "Personal adicionado correctamente",
            "resultado" => $result
        ]);
    }

    // Eliminar
    public static function delete($id)
    {
        $result = Personal::delete($id);

        header('Content-Type: application/json');

        echo json_encode([
            "mensaje" => "Personal eliminado correctamente",
            "resultado" => $result
        ]);
    }
}