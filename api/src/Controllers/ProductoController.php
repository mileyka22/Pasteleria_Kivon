<?php
require_once "../src/Models/Productos.php";
class ProductoController{
    public function getAll()
    {
        $producto=Productos::all();
        echo json_encode($producto);
         
    }

    private function getJsonData()
    {
        $jsonData=file_get_contents('php://input');
        $data= json_decode($jsonData,true);

        if(json_last_error()!=JSON_ERROR_NONE)
            {
                return [
                    "data"=>null,
                    "errores"=>[
                        "json"=>json_last_error_msg()
                    ]
                ];
            }

        if(!is_array($data))
            {
                return [
                    "data"=>null,
                    "errores"=>[
                        "json"=>"Debe enviar datos en formato JSON"
                    ]
                ];
            }

        return [
            "data"=>$data,
            "errores"=>[]
        ];
    }

    private function validarProducto($data)
    {
        $errores=[];

        if(!isset($data['nombre']) || trim($data['nombre'])=="")
            {
                $errores['nombre']="El campo nombre es obligatorio";
            }

        if(!isset($data['precio']) || trim($data['precio'])=="")
            {
                $errores['precio']="El campo precio es obligatorio";
            }
        elseif(!is_numeric($data['precio']))
            {
                $errores['precio']="El campo precio debe ser numerico";
            }

        if(!isset($data['cantidad']) || trim($data['cantidad'])=="")
            {
                $errores['cantidad']="El campo cantidad es obligatorio";
            }
        elseif(!is_numeric($data['cantidad']))
            {
                $errores['cantidad']="El campo cantidad debe ser numerico";
            }

        if(!isset($data['ingrediente']) || trim($data['ingrediente'])=="")
            {
                $errores['ingrediente']="El campo ingrediente es obligatorio";
            }

        if(!isset($data['fecha_registro']) || trim($data['fecha_registro'])=="")
            {
                $errores['fecha_registro']="El campo fecha_registro es obligatorio";
            }
        elseif(!strtotime($data['fecha_registro']))
            {
                $errores['fecha_registro']="El campo fecha_registro debe ser una fecha valida";
            }

        if(!isset($data['id_personal']) || trim($data['id_personal'])=="")
            {
                $errores['id_personal']="El campo id_personal es obligatorio";
            }
        elseif(!is_numeric($data['id_personal']))
            {
                $errores['id_personal']="El campo id_personal debe ser numerico";
            }

        return $errores;
    }

    //actualizar producto
    public function update($id)
    {
        $json=$this->getJsonData();

        if(!empty($json['errores']))
            {
                echo json_encode([
                    "estado"=>false,
                    "errores"=>$json['errores']
                ]);
                return;
            }

        $data=$json['data'];
        $errores=$this->validarProducto($data);

        if(!empty($errores))
            {
            echo json_encode(
                [
                    "estado"=>false,
                    "errores"=>$errores
                ]);
            return;
            }

        $producto=Productos::update($id,$data);
        if($producto){
            echo json_encode([
                "estado"=>true,
                "message"=>"Producto actualizado correctamente",
            ]);
            return;
        }
        echo json_encode($producto);
        
    }

    //Adicionar producto
    public function add()
    {
        $json=$this->getJsonData();

        if(!empty($json['errores']))
            {
                echo json_encode([
                    "estado"=>false,
                    "errores"=>$json['errores']
                ]);
                return;
            }

        $data=$json['data'];
        $errores=$this->validarProducto($data);

        if(!empty($errores))
            {
                echo json_encode(
                [
                    "estado"=>false,
                    "errores"=>$errores
                ]);
                return;
            }

        $producto=Productos::add($data);

        if($producto){
            echo json_encode([
                "estado"=>true,
                "message"=>"Producto adicionado correctamente",
            ]);
            return;
        }
        
        echo json_encode($producto);
         
    }

    //Eliminar producto
    public function delete($id)
    {
        $producto=Productos::delete($id);

        if($producto){
            echo json_encode([
                "estado"=>true,
                "message"=>"Producto eliminado correctamente",
            ]);
            return;
        }

        echo json_encode($producto);
    }
}
