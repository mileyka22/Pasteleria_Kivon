<?php

require_once "../src/Models/Venta.php";

class VentaController
{
    public function getAll()
    {
        $venta = Venta::all();

        echo json_encode($venta);
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

    private function validarVenta($data)
    {
        $errores = [];

        if (!isset($data['Total']) || trim($data['Total']) == "") {
            $errores['Total'] = "El campo Total es obligatorio";
        } elseif (!is_numeric($data['Total'])) {
            $errores['Total'] = "El campo Total debe ser numerico";
        }

        if (!isset($data['Metodo_de_pago']) ||
            trim($data['Metodo_de_pago']) == "") {

            $errores['Metodo_de_pago'] =
                "El campo Metodo_de_pago es obligatorio";
        }

        if (isset($data['id_personal']) &&
            $data['id_personal'] !== "" &&
            !is_numeric($data['id_personal'])) {

            $errores['id_personal'] =
                "El campo id_personal debe ser numerico";
        }

        if (isset($data['id_pedido']) &&
            $data['id_pedido'] !== "" &&
            !is_numeric($data['id_pedido'])) {

            $errores['id_pedido'] =
                "El campo id_pedido debe ser numerico";
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
        $errores = $this->validarVenta($data);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

        $venta = Venta::add($data);

        if ($venta) {
            echo json_encode([
                "estado" => true,
                "message" => "Venta agregada correctamente"
            ]);
            return;
        }

        echo json_encode($venta);
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
        $errores = $this->validarVenta($data);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

        $venta = Venta::update($id, $data);

        if ($venta) {
            echo json_encode([
                "estado" => true,
                "message" => "Venta actualizada correctamente"
            ]);
            return;
        }

        echo json_encode($venta);
    }

    public function delete($id)
    {
        $venta = Venta::delete($id);

        if ($venta) {
            echo json_encode([
                "estado" => true,
                "message" => "Venta eliminada correctamente"
            ]);
            return;
        }

        echo json_encode($venta);
    }
}