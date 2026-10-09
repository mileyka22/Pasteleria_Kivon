<?php
include_once __DIR__ . "/../Config/conexionDB.php";

class Clientes
{
    public static function all()
    {
        return ConexionPDO::query("SELECT * FROM CLIENTE ORDER BY id");
    }

    public static function find($id)
    {
        $rows = ConexionPDO::query("SELECT * FROM CLIENTE WHERE id = :id", [':id' => $id]);
        return $rows[0] ?? null;
    }

    public static function hasOrders($id)
    {
        return (bool) ConexionPDO::query(
            "SELECT id FROM PEDIDO WHERE id_cliente = :id LIMIT 1",
            [':id' => $id]
        );
    }

    public static function add($data)
    {
        $sql = "INSERT INTO CLIENTE (Nombre, Apellidos, CI, Telefono, Direccion)
                VALUES (:Nombre, :Apellidos, :CI, :Telefono, :Direccion)";
        return ConexionPDO::execute($sql, self::values($data), true);
    }

    public static function update($id, $data)
    {
        $sql = "UPDATE CLIENTE
                SET Nombre = :Nombre, Apellidos = :Apellidos, CI = :CI,
                    Telefono = :Telefono, Direccion = :Direccion
                WHERE id = :id";
        $values = self::values($data);
        $values[':id'] = $id;
        return ConexionPDO::execute($sql, $values);
    }

    private static function values($data)
    {
        return [
            ':Nombre' => $data['Nombre'],
            ':Apellidos' => $data['Apellidos'],
            ':CI' => $data['CI'],
            ':Telefono' => $data['Telefono'],
            ':Direccion' => $data['Direccion']
        ];
    }

    public static function delete($id)
    {
        $stmt = ConexionPDO::connect()->prepare("DELETE FROM CLIENTE WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
