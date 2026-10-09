<?php

include_once __DIR__ . "/../Config/conexionDB.php";

class Usuario
{
    public static function all()
    {
        $sql = "SELECT id, Username, Permiso FROM USUARIO";

        return ConexionPDO::query($sql);
    }

    public static function find($id)
    {
        $sql = "SELECT id, Username, Permiso FROM USUARIO WHERE id = :id";
        $usuarios = ConexionPDO::query($sql, [':id' => $id]);
        return $usuarios[0] ?? null;
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

        $sql = "INSERT INTO USUARIO ($stringCampos)
                VALUES ($stringParametros)";

        return ConexionPDO::execute($sql, $valores, true);
    }

    public static function update($id, $data)
    {
        $sql = "UPDATE USUARIO
                SET Username = :Username, Permiso = :Permiso
                WHERE id = :id";

        $valores = [
            ':Username' => $data['Username'],
            ':Permiso' => $data['Permiso'],
            ':id' => $id
        ];

        return ConexionPDO::execute($sql, $valores, false);
    }

    public static function delete($id)
    {
        $stmt = ConexionPDO::connect()->prepare("DELETE FROM USUARIO WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
