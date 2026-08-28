<?php
include_once __DIR__ . "/../Config/conexionDB.php";
class Clientes
{
    public static function all()
    {
        $sql = "SELECT * FROM CLIENTE";
        return ConexionPDO::query($sql); 
    }
}