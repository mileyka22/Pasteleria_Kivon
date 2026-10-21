<?php
include_once __DIR__ . "/../Config/conexionDB.php";

class Pedido
{
    private static function validateStock(PDO $db, array $items)
    {
        $select = $db->prepare("SELECT Nombre, Cantidad FROM PRODUCTO WHERE id = :id FOR UPDATE");
        foreach ($items as $item) {
            $select->execute([':id' => $item['id_producto']]);
            $product = $select->fetch();
            if (!$product) {
                throw new PedidoStockException(['productos' => 'Uno de los productos ya no existe']);
            }
            if ((int) $item['Cantidad'] > (int) $product['Cantidad']) {
                throw new PedidoStockException([
                    'productos' => $product['Nombre'] . ': solo hay ' . (int) $product['Cantidad'] . ' unidades disponibles'
                ]);
            }
        }
    }

    public static function all()
    {
        $pedidos = ConexionPDO::query("SELECT p.*, CONCAT_WS(' ', c.Nombre, c.Apellidos) AS cliente,
                                              c.Telefono AS cliente_telefono, c.Direccion AS cliente_direccion
                                      FROM PEDIDO p LEFT JOIN CLIENTE c ON c.id = p.id_cliente ORDER BY p.id");
        foreach ($pedidos as &$pedido) {
            $pedido['productos'] = self::products($pedido['id']);
            $pedido['Total'] = array_reduce($pedido['productos'], static function ($total, $item) {
                return $total + (float) $item['Subtotal'];
            }, 0);
        }
        return $pedidos;
    }

    private static function products($id)
    {
        return ConexionPDO::query(
            "SELECT pp.id, pp.id_producto, pp.Cantidad, pp.Precio_unitario,
                    pr.Nombre, (pp.Cantidad * pp.Precio_unitario) AS Subtotal
             FROM PRODUCTO_PEDIDO pp
             LEFT JOIN PRODUCTO pr ON pr.id = pp.id_producto
             WHERE pp.id_pedido = :id ORDER BY pp.id",
            [':id' => $id]
        );
    }

    public static function findWithDetails($id)
    {
        $rows = ConexionPDO::query(
            "SELECT p.*, CONCAT_WS(' ', c.Nombre, c.Apellidos) AS cliente
             FROM PEDIDO p LEFT JOIN CLIENTE c ON c.id = p.id_cliente
             WHERE p.id = :id LIMIT 1",
            [':id' => $id]
        );
        if (!$rows) return null;
        $pedido = $rows[0];
        $pedido['productos'] = self::products($id);
        return $pedido;
    }

    private static function saveProducts(PDO $db, $pedidoId, array $items)
    {
        $existing = ConexionPDO::query(
            "SELECT id_producto, Precio_unitario FROM PRODUCTO_PEDIDO WHERE id_pedido = :id",
            [':id' => $pedidoId]
        );
        $historicalPrices = [];
        foreach ($existing as $row) {
            $historicalPrices[(int) $row['id_producto']] = $row['Precio_unitario'];
        }
        $db->prepare("DELETE FROM PRODUCTO_PEDIDO WHERE id_pedido = :id")->execute([':id' => $pedidoId]);
        $insert = $db->prepare("INSERT INTO PRODUCTO_PEDIDO (id_producto, id_pedido, Cantidad, Precio_unitario)
                                VALUES (:producto, :pedido, :cantidad, :precio)");
        foreach ($items as $item) {
            $price = $historicalPrices[$item['id_producto']] ??
                ConexionPDO::query("SELECT Precio FROM PRODUCTO WHERE id = :id", [':id' => $item['id_producto']])[0]['Precio'];
            $insert->execute([
                ':producto' => $item['id_producto'], ':pedido' => $pedidoId,
                ':cantidad' => $item['Cantidad'], ':precio' => $price
            ]);
        }
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
        $sql = "INSERT INTO PEDIDO (Estado, Fecha_registro, Detalle, Metodo_de_pago, Fecha_entrega, id_cliente, id_personal)
                VALUES (:Estado, CURRENT_TIMESTAMP, :Detalle, :Metodo_de_pago, :Fecha_entrega, :id_cliente, :id_personal)";
        $db = ConexionPDO::connect();
        $db->beginTransaction();
        try {
            self::validateStock($db, $data['productos']);
            $stmt = $db->prepare($sql);
            $stmt->execute(self::values($data));
            $id = (int) $db->lastInsertId();
            self::saveProducts($db, $id, $data['productos']);
            $db->commit();
            return $id;
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }

    public static function update($id, $data)
    {
        $sql = "UPDATE PEDIDO
                SET Estado = :Estado, Detalle = :Detalle, Metodo_de_pago = :Metodo_de_pago,
                    Fecha_entrega = COALESCE(:Fecha_entrega, Fecha_entrega),
                    id_cliente = :id_cliente, id_personal = :id_personal
                WHERE id = :id";
        $db = ConexionPDO::connect();
        $db->beginTransaction();
        try {
            self::validateStock($db, $data['productos']);
            $values = self::values($data);
            $values[':id'] = $id;
            $stmt = $db->prepare($sql);
            $stmt->execute($values);
            self::saveProducts($db, $id, $data['productos']);
            $db->commit();
            return true;
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }

    private static function values($data)
    {
        return [
            ':Estado' => $data['Estado'],
            ':Detalle' => $data['Detalle'],
            ':Metodo_de_pago' => $data['Metodo_de_pago'],
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

class PedidoStockException extends RuntimeException
{
    public array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Stock insuficiente');
        $this->errors = $errors;
    }
}
