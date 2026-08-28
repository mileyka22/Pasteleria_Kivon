<?php
include_once __DIR__ . "/../Config/conexionDB.php";
class Venta
{
    public static function all()
    {
        $sql = "SELECT * FROM VENTA";
        return ConexionPDO::query($sql); 
    }
}