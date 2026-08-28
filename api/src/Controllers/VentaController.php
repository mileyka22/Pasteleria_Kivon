<?php
require_once "../src/Models/Venta.php";
class VentaController{
    public function getAll()
    {
        $venta=Venta::all();
        echo json_encode($venta);
         
    }
}