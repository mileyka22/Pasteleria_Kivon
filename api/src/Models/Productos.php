<?php
include_once __DIR__ . "/../Config/conexionDB.php";

class PRODUCTO
{
    public static function all()
    {
        return ConexionPDO::query("SELECT * FROM PRODUCTO ORDER BY id");
    }

    public static function find($id)
    {
        $rows = ConexionPDO::query("SELECT * FROM PRODUCTO WHERE id = :id", [':id' => $id]);
        return $rows[0] ?? null;
    }

    public static function personalExists($id)
    {
        return (bool) ConexionPDO::query("SELECT id FROM PERSONAL WHERE id = :id", [':id' => $id]);
    }

    public static function hasOrderItems($id)
    {
        return (bool) ConexionPDO::query(
            "SELECT id FROM PRODUCTO_PEDIDO WHERE id_producto = :id LIMIT 1",
            [':id' => $id]
        );
    }

    public static function add($data)
    {
        $sql = "INSERT INTO PRODUCTO (Nombre, Precio, Cantidad, Ingrediente, id_personal)
                VALUES (:Nombre, :Precio, :Cantidad, :Ingrediente, :id_personal)";
        $values = [
            ':Nombre' => $data['Nombre'],
            ':Precio' => $data['Precio'],
            ':Cantidad' => $data['Cantidad'],
            ':Ingrediente' => $data['Ingrediente'],
            ':id_personal' => $data['id_personal']
        ];
        return ConexionPDO::execute($sql, $values, true);
    }

    public static function update($id, $data)
    {
        $sql = "UPDATE PRODUCTO
                SET Nombre = :Nombre, Precio = :Precio, Cantidad = :Cantidad,
                    Ingrediente = :Ingrediente, id_personal = :id_personal
                WHERE id = :id";
        $values = [
            ':Nombre' => $data['Nombre'],
            ':Precio' => $data['Precio'],
            ':Cantidad' => $data['Cantidad'],
            ':Ingrediente' => $data['Ingrediente'],
            ':id_personal' => $data['id_personal'],
            ':id' => $id
        ];
        return ConexionPDO::execute($sql, $values);
    }

    public static function delete($id)
    {
        $stmt = ConexionPDO::connect()->prepare("DELETE FROM PRODUCTO WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
