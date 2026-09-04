<?php

require_once "../src/Models/Producto_pedido.php";

class Producto_pedidoController
{
    public function getAll()
    {
        $producto_pedido = Producto_pedido::all();

        echo json_encode($producto_pedido);
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

    private function validarProductoPedido($data)
    {
        $errores = [];

        if (!isset($data['id_producto']) || trim($data['id_producto']) == "") {
            $errores['id_producto'] = "El campo id_producto es obligatorio";
        } elseif (!is_numeric($data['id_producto'])) {
            $errores['id_producto'] = "El campo id_producto debe ser numerico";
        }

        if (!isset($data['id_pedido']) || trim($data['id_pedido']) == "") {
            $errores['id_pedido'] = "El campo id_pedido es obligatorio";
        } elseif (!is_numeric($data['id_pedido'])) {
            $errores['id_pedido'] = "El campo id_pedido debe ser numerico";
        }

        if (!isset($data['Cantidad']) || trim($data['Cantidad']) == "") {
            $errores['Cantidad'] = "El campo Cantidad es obligatorio";
        } elseif (!is_numeric($data['Cantidad'])) {
            $errores['Cantidad'] = "El campo Cantidad debe ser numerico";
        }

        if (!isset($data['Precio_unitario']) ||
            trim($data['Precio_unitario']) == "") {

            $errores['Precio_unitario'] =
                "El campo Precio_unitario es obligatorio";

        } elseif (!is_numeric($data['Precio_unitario'])) {

            $errores['Precio_unitario'] =
                "El campo Precio_unitario debe ser numerico";
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
        $errores = $this->validarProductoPedido($data);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

        $producto_pedido = Producto_pedido::add($data);

        if ($producto_pedido) {
            echo json_encode([
                "estado" => true,
                "message" => "Producto del pedido agregado correctamente"
            ]);
            return;
        }

        echo json_encode($producto_pedido);
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
        $errores = $this->validarProductoPedido($data);

        if (!empty($errores)) {
            echo json_encode([
                "estado" => false,
                "errores" => $errores
            ]);
            return;
        }

        $producto_pedido = Producto_pedido::update($id, $data);

        if ($producto_pedido) {
            echo json_encode([
                "estado" => true,
                "message" => "Producto del pedido actualizado correctamente"
            ]);
            return;
        }

        echo json_encode($producto_pedido);
    }

    public function delete($id)
    {
        $producto_pedido = Producto_pedido::delete($id);

        if ($producto_pedido) {
            echo json_encode([
                "estado" => true,
                "message" => "Producto del pedido eliminado correctamente"
            ]);
            return;
        }

        echo json_encode($producto_pedido);
    }
}