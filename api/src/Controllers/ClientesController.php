<?php

require_once "../src/Models/Clientes.php";

class ClientesController
{
    public function getAll()
    {
        $clientes = Clientes::all();
        echo json_encode($clientes);
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

    private function validarCliente($data)
    {
        $errores = [];

        if (!isset($data['Nombre']) || trim($data['Nombre']) == "") {
            $errores['Nombre'] = "El campo Nombre es obligatorio";
        }

        if (!isset($data['CI']) || trim($data['CI']) == "") {
            $errores['CI'] = "El campo CI es obligatorio";
        }

        return $errores;
    }

    // Agregar
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
        $errores = $this->validarCliente($data);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

        $cliente = Clientes::add($data);

        if ($cliente) {
            echo json_encode([
                "estado" => true,
                "message" => "Cliente agregado correctamente"
            ]);
            return;
        }

        echo json_encode($cliente);
    }

    // Actualizar
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
        $errores = $this->validarCliente($data);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

        $cliente = Clientes::update($id, $data);

        if ($cliente) {
            echo json_encode([
                "estado" => true,
                "message" => "Cliente actualizado correctamente"
            ]);
            return;
        }

        echo json_encode($cliente);
    }

    // Eliminar
    public function delete($id)
    {
        $cliente = Clientes::delete($id);

        if ($cliente) {
            echo json_encode([
                "estado" => true,
                "message" => "Cliente eliminado correctamente"
            ]);
            return;
        }

        echo json_encode($cliente);
    }
}