<?php

include_once __DIR__ . "/../Config/conexionDB.php";

class Producto_pedido
{
    public static function all()
    {
        $sql = "SELECT * FROM PRODUCTO_PEDIDO";

        return ConexionPDO::query($sql);
    }

    public static function add($data)
    {
        $campos = [];
        $parametros = [];
        $valores = [];

        foreach ($data as $columna => $valor) {
            $campos[] = $columna;
            $parametros[] = ":$columna";
            $valores[":$columna"] = $valor;
        }

        $stringCampos = implode(",", $campos);
        $stringParametros = implode(",", $parametros);

        $sql = "INSERT INTO PRODUCTO_PEDIDO ($stringCampos)
                VALUES ($stringParametros)";

        return ConexionPDO::execute($sql, $valores, true);
    }

    public static function update($id, $data)
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }

        $campos = [];
        $valores = [];

        foreach ($data as $columna => $valor) {
            $campos[] = "$columna=:$columna";
            $valores[":$columna"] = $valor;
        }

        $stringCampos = implode(",", $campos);

        $sql = "UPDATE PRODUCTO_PEDIDO
                SET $stringCampos
                WHERE id=:id";

        $valores[':id'] = $id;

        return ConexionPDO::execute($sql, $valores, false);
    }

    public static function delete($id)
    {
        $sql = "DELETE FROM PRODUCTO_PEDIDO WHERE id=:id";

        $valores = [
            ":id" => $id
        ];

        return ConexionPDO::execute($sql, $valores, false);
    }
}