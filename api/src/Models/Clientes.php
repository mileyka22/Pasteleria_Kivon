<?php
include_once __DIR__ . "/../Config/conexionDB.php";

class Clientes
{
    // Mostrar todos los clientes
    public static function all()
    {
        $sql = "SELECT * FROM CLIENTE";
        return ConexionPDO::query($sql);
    }

    // Agregar cliente
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

        $sql = "INSERT INTO CLIENTE ($stringCampos)
                VALUES ($stringParametros)";

        return ConexionPDO::execute($sql, $valores, true);
    }

    // Actualizar cliente
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

        $sql = "UPDATE CLIENTE
                SET $stringCampos
                WHERE id=:id";

        $valores[':id'] = $id;

        return ConexionPDO::execute($sql, $valores, false);
    }

    // Eliminar cliente
    public static function delete($id)
    {
        $sql = "DELETE FROM CLIENTE WHERE id=:id";

        $valores = [
            ":id" => $id
        ];

        return ConexionPDO::execute($sql, $valores, false);
    }
}