<?php
include_once __DIR__ . "/../Config/conexionDB.php";

class Venta
{
    public static function all()
    {
        return ConexionPDO::query("SELECT * FROM VENTA ORDER BY id");
    }

    public static function find($id)
    {
        $rows = ConexionPDO::query("SELECT * FROM VENTA WHERE id = :id", [':id' => $id]);
        return $rows[0] ?? null;
    }

    public static function personalExists($id)
    {
        return (bool) ConexionPDO::query("SELECT id FROM PERSONAL WHERE id = :id", [':id' => $id]);
    }

    public static function pedidoExists($id)
    {
        return (bool) ConexionPDO::query("SELECT id FROM PEDIDO WHERE id = :id", [':id' => $id]);
    }

    public static function orderItemsTotal($pedidoId)
    {
        $rows = ConexionPDO::query(
            "SELECT COUNT(*) AS lineas, COALESCE(SUM(Cantidad * Precio_unitario), 0) AS total
             FROM PRODUCTO_PEDIDO WHERE id_pedido = :id",
            [':id' => $pedidoId]
        );
        if ((int) ($rows[0]['lineas'] ?? 0) === 0) return null;
        return round((float) $rows[0]['total'], 2);
    }

    public static function add($data)
    {
        $sql = "INSERT INTO VENTA (Total, Metodo_de_pago, id_personal, id_pedido)
                VALUES (:Total, :Metodo_de_pago, :id_personal, :id_pedido)";
        return ConexionPDO::execute($sql, self::values($data), true);
    }

    public static function update($id, $data)
    {
        $sql = "UPDATE VENTA
                SET Total = :Total, Metodo_de_pago = :Metodo_de_pago,
                    id_personal = :id_personal, id_pedido = :id_pedido
                WHERE id = :id";
        $values = self::values($data);
        $values[':id'] = $id;
        return ConexionPDO::execute($sql, $values);
    }

    private static function values($data)
    {
        return [
            ':Total' => $data['Total'],
            ':Metodo_de_pago' => $data['Metodo_de_pago'],
            ':id_personal' => $data['id_personal'],
            ':id_pedido' => $data['id_pedido']
        ];
    }

    public static function delete($id)
    {
        $stmt = ConexionPDO::connect()->prepare("DELETE FROM VENTA WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
