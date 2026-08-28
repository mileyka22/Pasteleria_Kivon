<?php
include_once __DIR__ . "/../Config/conexionDB.php";
class Productos
{
    //Mostrar producto
    public static function all()
    {
        $sql = "SELECT * FROM PRODUCTO";
        return ConexionPDO::query($sql); 
    }
    //Actualizar producto
        public static function update($id,$data)
    {
        if(isset($data['id']))
            {
                unset($data['id']);
            }
            $campos=[];
            $valores=[];
            //construir datos
            foreach($data as $columna=>$valor)
            {
                $campos[]="$columna=:$columna";
                $valores[":$columna"]=$valor;
            }
        $stringCampos=implode(",",$campos);
        //preparamos la consulta
        $sql="UPDATE producto SET $stringCampos WHERE id=:id";
        $valores[':id']=$id;
        $result=ConexionPDO::execute($sql,$valores,false);
        //$sql = "SELECT * FROM productos";
        return $result; //ConexionPDO::query($sql); 
    }
    public static function add($data){
        $campos=[];
        $parametros=[];
        $valores=[];
            //construir datos
        foreach($data as $columna=>$valor)
        {
            $campos[]=$columna;
            $parametros[]=":$columna";
            $valores[":$columna"]=$valor;
        }
        $stringCampos=implode(",",$campos);
        $stringParametros=implode(",",$parametros);
        //die($stringCampos);
        //preparamos la consulta
        $sql="INSERT INTO producto ($stringCampos) VALUES ($stringParametros)";
        $result=ConexionPDO::execute($sql,$valores,true);
        return $result;
    }
    //Eliminar producto
    public static function delete($id)
    {
        $sql="DELETE FROM producto WHERE id=:id";
        $valores=[
            ":id"=>$id
        ];
        $result=ConexionPDO::execute($sql,$valores,false);
        return $result;
    }
}
