<?php
include_once __DIR__ . "/../Config/conexionDB.php";
class Pedido
{
    public static function all()
    {
        $sql = "SELECT * FROM PEDIDO";
        return ConexionPDO::query($sql); 
    }
}