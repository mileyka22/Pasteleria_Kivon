<?php
require_once "../src/Models/Clientes.php";
class ClientesController{
    public function getAll()
    {
        $cliente=Clientes::all();
        echo json_encode($cliente);
         
    }
}