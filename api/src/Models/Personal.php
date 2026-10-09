<?php
include_once __DIR__ . "/../Config/conexionDB.php";

class Personal
{
    public static function all()
    {
        return ConexionPDO::query("SELECT * FROM PERSONAL ORDER BY id");
    }

    public static function find($id)
    {
        $rows = ConexionPDO::query("SELECT * FROM PERSONAL WHERE id = :id", [':id' => $id]);
        return $rows[0] ?? null;
    }

    public static function userExists($id)
    {
        return (bool) ConexionPDO::query("SELECT id FROM USUARIO WHERE id = :id", [':id' => $id]);
    }

    public static function hasRelatedRecords($id)
    {
        $rows = ConexionPDO::query(
            "SELECT
                (SELECT COUNT(*) FROM PRODUCTO WHERE id_personal = :producto) +
                (SELECT COUNT(*) FROM PEDIDO WHERE id_personal = :pedido) +
                (SELECT COUNT(*) FROM VENTA WHERE id_personal = :venta) AS total",
            [':producto' => $id, ':pedido' => $id, ':venta' => $id]
        );
        return (int) ($rows[0]['total'] ?? 0) > 0;
    }

    public static function add($data)
    {
        $sql = "INSERT INTO PERSONAL (Nombre, Cargo, Telefono, Salario, id_usuario)
                VALUES (:Nombre, :Cargo, :Telefono, :Salario, :id_usuario)";
        return ConexionPDO::execute($sql, self::values($data), true);
    }

    public static function update($id, $data)
    {
        $sql = "UPDATE PERSONAL
                SET Nombre = :Nombre, Cargo = :Cargo, Telefono = :Telefono,
                    Salario = :Salario, id_usuario = :id_usuario
                WHERE id = :id";
        $values = self::values($data);
        $values[':id'] = $id;
        return ConexionPDO::execute($sql, $values);
    }

    private static function values($data)
    {
        return [
            ':Nombre' => $data['Nombre'],
            ':Cargo' => $data['Cargo'],
            ':Telefono' => $data['Telefono'],
            ':Salario' => $data['Salario'],
            ':id_usuario' => $data['id_usuario']
        ];
    }

    public static function delete($id)
    {
        $stmt = ConexionPDO::connect()->prepare("DELETE FROM PERSONAL WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
