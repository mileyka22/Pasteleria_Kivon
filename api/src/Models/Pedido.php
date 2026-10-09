<?php
include_once __DIR__ . "/../Config/conexionDB.php";

class Pedido
{
    public static function all()
    {
        return ConexionPDO::query("SELECT * FROM PEDIDO ORDER BY id");
    }

    public static function find($id)
    {
        $rows = ConexionPDO::query("SELECT * FROM PEDIDO WHERE id = :id", [':id' => $id]);
        return $rows[0] ?? null;
    }

    public static function clienteExists($id)
    {
        return (bool) ConexionPDO::query("SELECT id FROM CLIENTE WHERE id = :id", [':id' => $id]);
    }

    public static function personalExists($id)
    {
        return (bool) ConexionPDO::query("SELECT id FROM PERSONAL WHERE id = :id", [':id' => $id]);
    }

    public static function hasRelatedRecords($id)
    {
        $rows = ConexionPDO::query(
            "SELECT
                (SELECT COUNT(*) FROM PRODUCTO_PEDIDO WHERE id_pedido = :detalle) +
                (SELECT COUNT(*) FROM VENTA WHERE id_pedido = :venta) AS total",
            [':detalle' => $id, ':venta' => $id]
        );
        return (int) ($rows[0]['total'] ?? 0) > 0;
    }

    public static function add($data)
    {
        $sql = "INSERT INTO PEDIDO (Estado, Detalle, Fecha_entrega, id_cliente, id_personal)
                VALUES (:Estado, :Detalle, COALESCE(:Fecha_entrega, CURRENT_TIMESTAMP), :id_cliente, :id_personal)";
        return ConexionPDO::execute($sql, self::values($data), true);
    }

    public static function update($id, $data)
    {
        $sql = "UPDATE PEDIDO
                SET Estado = :Estado, Detalle = :Detalle,
                    Fecha_entrega = COALESCE(:Fecha_entrega, Fecha_entrega),
                    id_cliente = :id_cliente, id_personal = :id_personal
                WHERE id = :id";
        $values = self::values($data);
        $values[':id'] = $id;
        return ConexionPDO::execute($sql, $values);
    }

    private static function values($data)
    {
        return [
            ':Estado' => $data['Estado'],
            ':Detalle' => $data['Detalle'],
            ':Fecha_entrega' => $data['Fecha_entrega'],
            ':id_cliente' => $data['id_cliente'],
            ':id_personal' => $data['id_personal']
        ];
    }

    public static function delete($id)
    {
        $stmt = ConexionPDO::connect()->prepare("DELETE FROM PEDIDO WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
