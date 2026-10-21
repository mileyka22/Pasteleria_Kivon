<?php
include_once __DIR__ . "/../Config/conexionDB.php";

class Venta
{
    public static function dashboard()
    {
        $summary = ConexionPDO::query(
            "SELECT
                COALESCE(SUM(CASE WHEN Fecha >= CURRENT_DATE AND Fecha < CURRENT_DATE + INTERVAL 1 DAY THEN Total ELSE 0 END), 0) AS ventasHoy,
                COALESCE(SUM(CASE WHEN YEAR(Fecha) = YEAR(CURRENT_DATE) AND MONTH(Fecha) = MONTH(CURRENT_DATE) THEN Total ELSE 0 END), 0) AS ventasMes,
                COALESCE(SUM(CASE WHEN YEAR(Fecha) = YEAR(CURRENT_DATE) THEN Total ELSE 0 END), 0) AS ventasAnio,
                COALESCE(SUM(CASE WHEN Fecha >= CURRENT_DATE AND Fecha < CURRENT_DATE + INTERVAL 1 DAY THEN 1 ELSE 0 END), 0) AS cantidadVentasHoy,
                YEAR(CURRENT_DATE) AS anioActual,
                MONTH(CURRENT_DATE) AS mesActual
             FROM VENTA"
        )[0];

        $products = ConexionPDO::query(
            "SELECT pp.id_producto, COALESCE(pr.Nombre, 'Producto eliminado') AS Nombre,
                    SUM(pp.Cantidad) AS Cantidad
             FROM PRODUCTO_PEDIDO pp
             LEFT JOIN PRODUCTO pr ON pr.id = pp.id_producto
             -- Count each order's units once even if multiple sale rows reference that order.
             WHERE EXISTS (SELECT 1 FROM VENTA v WHERE v.id_pedido = pp.id_pedido)
             GROUP BY pp.id_producto, pr.Nombre
             ORDER BY Cantidad DESC, Nombre ASC"
        );

        $monthlySales = ConexionPDO::query(
            "SELECT MONTH(Fecha) AS Mes, COALESCE(SUM(Total), 0) AS Total
             FROM VENTA
             WHERE YEAR(Fecha) = YEAR(CURRENT_DATE)
             GROUP BY MONTH(Fecha)
             ORDER BY Mes"
        );

        return [
            'resumen' => [
                'ventasHoy' => (float) $summary['ventasHoy'],
                'ventasMes' => (float) $summary['ventasMes'],
                'ventasAnio' => (float) $summary['ventasAnio'],
                'cantidadVentasHoy' => (int) $summary['cantidadVentasHoy'],
                'anioActual' => (int) $summary['anioActual'],
                'mesActual' => (int) $summary['mesActual']
            ],
            'productosMasVendidos' => array_map(static function ($product) {
                return [
                    'id_producto' => $product['id_producto'] === null ? null : (int) $product['id_producto'],
                    'Nombre' => $product['Nombre'],
                    'Cantidad' => (int) $product['Cantidad']
                ];
            }, $products),
            'ventasPorMes' => array_map(static function ($month) {
                return ['Mes' => (int) $month['Mes'], 'Total' => (float) $month['Total']];
            }, $monthlySales)
        ];
    }

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
