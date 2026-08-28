<?php
include_once __DIR__ . "/../Config/conexionDB.php";
class Usuario
{
    private static $usuario = [
        ["id" => 1, "name" => 'Daniel Rosdriguez', 'email' => "daniel@gmail.com"],
        ["id" => 2, "name" => 'Maria Lopez', 'email' => "maria@gmail.com"],
        ["id" => 3, "name" => 'Calos Daniel', 'email' => "carlos@gmail.com"],
        ["id" => 4, "name" => 'test', 'email' => "daniel@gmail.com"],

    ];
    public static function all()
    {
        $sql = "SELECT * FROM USUARIO";
        return ConexionPDO::query($sql); //self::$users;
    }
}
