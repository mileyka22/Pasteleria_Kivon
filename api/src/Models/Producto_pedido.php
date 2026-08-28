<?php
include_once __DIR__ . "/../Config/conexionDB.php";
class Producto_pedido
{
    public static function all()
    {
        $sql = "SELECT * FROM PRODUCTO_PEDIDO";
        return ConexionPDO::query($sql); 
    }
}