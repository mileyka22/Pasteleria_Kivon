<?php
require_once "../src/Models/Usuario.php";
class UsuarioController{
    public function getAll()
    {
        $usuario=Usuario::all();
        echo json_encode($usuario);
         
    }
}