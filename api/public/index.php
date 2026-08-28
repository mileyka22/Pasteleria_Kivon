<?php
header("Access-Control-");
if($_SERVER['REQUEST_METHOD']=='OPTIONS')
    {
        exit;
    }
require_once "../src/Router.php";
require_once "../src/Controllers/UsuarioController.php";
require_once "../src/Controllers/ProductoController.php";
require_once "../src/Controllers/ClientesController.php";
require_once "../src/Controllers/PedidoController.php";
require_once "../src/Controllers/VentaController.php";
require_once "../src/Controllers/Producto_pedidoController.php";

use App\Router;

$route=new Router();
//direccion para usuarios
$route->add('GET','/','UsuarioController@getAll');
$route->add('GET','/usuario','UsuarioController@getAll'); 
//direccion de productos
$route->add('GET','/productos','ProductoController@getAll'); 
$route->add('POST','/productos','ProductoController@add'); 
$route->add('PUT','/productos/{id}','ProductoController@update'); 
$route->add('DELETE','/productos/{id}','ProductoController@delete'); 
$route->add('GET','/clientes','ClientesController@getAll'); 
$route->add('GET','/pedido','PedidoController@getAll'); 
$route->add('GET','/venta','VentaController@getAll'); 
$route->add('GET','/producto_pedido','Producto_pedidoController@getAll');
$route->run();
