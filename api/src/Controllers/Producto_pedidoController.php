<?php
require_once "../src/Models/Producto_pedido.php";
class Producto_pedidoController{
    public function getAll()
    {
        $producto_pedido=Producto_pedido::all();
        echo json_encode($producto_pedido);
         
    }
}