<?php

require_once "../src/Models/Pedido.php";

class PedidoController
{
    public function getAll()
    {
        $pedido = Pedido::all();

        echo json_encode($pedido);
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

    private function validarPedido($data)
    {
        $errores = [];

        if (!isset($data['Estado']) || trim($data['Estado']) == "") {
            $errores['Estado'] = "El campo Estado es obligatorio";
        }

        if (isset($data['id_cliente']) && $data['id_cliente'] !== ""
            && !is_numeric($data['id_cliente'])) {
            $errores['id_cliente'] = "El campo id_cliente debe ser numerico";
        }

        if (isset($data['id_personal']) && $data['id_personal'] !== ""
            && !is_numeric($data['id_personal'])) {
            $errores['id_personal'] = "El campo id_personal debe ser numerico";
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
        $errores = $this->validarPedido($data);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

        $pedido = Pedido::add($data);

        if ($pedido) {
            echo json_encode([
                "estado" => true,
                "message" => "Pedido agregado correctamente"
            ]);
            return;
        }

        echo json_encode($pedido);
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
        $errores = $this->validarPedido($data);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

        $pedido = Pedido::update($id, $data);

        if ($pedido) {
            echo json_encode([
                "estado" => true,
                "message" => "Pedido actualizado correctamente"
            ]);
            return;
        }

        echo json_encode($pedido);
    }

    public function delete($id)
    {
        $pedido = Pedido::delete($id);

        if ($pedido) {
            echo json_encode([
                "estado" => true,
                "message" => "Pedido eliminado correctamente"
            ]);
            return;
        }

        echo json_encode($pedido);
    }
}