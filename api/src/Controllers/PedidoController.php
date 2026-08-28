<?php
require_once "../src/Models/Pedido.php";
class PedidoController{
    public function getAll()
    {
        $pedido=Pedido::all();
        echo json_encode($pedido);
         
    }
}