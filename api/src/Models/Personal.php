<?php

include_once __DIR__ . "/../Config/conexionDB.php";

class Personal
{
    // Mostrar personal
    public static function all()
    {
        $sql = "SELECT * FROM PERSONAL";
        return ConexionPDO::query($sql);
    }

    // Agregar personal
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

        $sql = "INSERT INTO PERSONAL ($stringCampos) VALUES ($stringParametros)";

        $result = ConexionPDO::execute($sql, $valores, true);

        return $result;
    }

    // Actualizar personal
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

        $sql = "UPDATE PERSONAL SET $stringCampos WHERE id=:id";

        $valores[':id'] = $id;

        $result = ConexionPDO::execute($sql, $valores, false);

        return $result;
    }

    // Eliminar personal
    public static function delete($id)
    {
        $sql = "DELETE FROM PERSONAL WHERE id=:id";

        $valores = [
            ":id" => $id
        ];

        $result = ConexionPDO::execute($sql, $valores, false);

        return $result;
    }
}