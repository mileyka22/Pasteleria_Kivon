<?php
require_once "../src/Models/Pedido.php";

class PedidoController
{
    public function getAll()
    {
        $orders = Pedido::all();
        $user = AuthSession::currentUser();
        if (strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $user['Permiso'])) === 'produccion') {
            foreach ($orders as &$order) foreach (['Metodo_de_pago', 'id_cliente', 'id_personal', 'cliente', 'cliente_telefono', 'cliente_direccion', 'Total'] as $field) unset($order[$field]);
            unset($order);
        }
        echo json_encode($orders, JSON_UNESCAPED_UNICODE);
    }

    public function getById($id)
    {
        $pedido = Pedido::findWithDetails($id);
        if (!$pedido) {
            return $this->respondErrors(['id' => 'No se encontró el pedido solicitado'], 404);
        }
        if (empty($pedido['productos'])) {
            return $this->respondErrors(['productos' => 'El pedido no tiene productos válidos para elaborar un ticket'], 422);
        }
        $user = AuthSession::currentUser();
        if (strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $user['Permiso'])) === 'produccion') {
            foreach (['Metodo_de_pago', 'id_cliente', 'id_personal', 'cliente'] as $field) unset($pedido[$field]);
        }
        echo json_encode($pedido, JSON_UNESCAPED_UNICODE);
    }

    private function getData()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return json_last_error() === JSON_ERROR_NONE && is_array($data)
            ? [$data, []]
            : [null, ['json' => 'Debe enviar datos validos en formato JSON']];
    }

    private function validate($data)
    {
        $allowed = ['Estado', 'Detalle', 'Metodo_de_pago', 'Fecha_entrega', 'id_cliente', 'id_personal', 'productos'];
        $errors = [];
        foreach (array_diff(array_keys($data), $allowed) as $field) $errors[$field] = 'Campo no permitido';

        if (!isset($data['Estado']) || !is_string($data['Estado']) || trim($data['Estado']) === '') {
            $errors['Estado'] = 'El estado es obligatorio';
        } elseif (strlen(trim($data['Estado'])) > 80) {
            $errors['Estado'] = 'El estado no puede superar 80 caracteres';
        }
        if (isset($data['Detalle']) && (!is_string($data['Detalle']) || strlen(trim($data['Detalle'])) > 255)) {
            $errors['Detalle'] = 'El detalle debe tener como maximo 255 caracteres';
        }
        $paymentMethod = $data['Metodo_de_pago'] ?? '';
        if (!in_array($paymentMethod, ['Efectivo', 'Tarjeta', 'QR'], true)) {
            $errors['Metodo_de_pago'] = 'Selecciona Efectivo, Tarjeta o QR';
        }

        // Ventas puede editar los datos comerciales del pedido sin cambiar
        // su estado de elaboración, que pertenece al flujo de Producción.
        $user = AuthSession::currentUser();
        if (strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $user['Permiso'] ?? '')) === 'ventas'
            && isset($data['Estado']) && in_array($data['Estado'], ['En proceso', 'En elaboración', 'Listo para entrega'], true)) {
            $data['Estado'] = 'Pendiente';
        }

        if (!isset($data['productos']) || !is_array($data['productos']) || count($data['productos']) === 0) {
            $errors['productos'] = 'Selecciona al menos un producto';
        } else {
            $seen = [];
            foreach ($data['productos'] as $index => $item) {
                if (!is_array($item) || !isset($item['id_producto']) || !ctype_digit((string) $item['id_producto'])) {
                    $errors["productos.$index.id_producto"] = 'Selecciona un producto existente';
                    continue;
                }
                $productId = (int) $item['id_producto'];
                if (!ConexionPDO::query("SELECT id FROM PRODUCTO WHERE id = :id", [':id' => $productId])) {
                    $errors["productos.$index.id_producto"] = 'El producto no existe';
                }
                if (isset($seen[$productId])) {
                    $errors["productos.$index.id_producto"] = 'No repitas productos; modifica la cantidad de la fila existente';
                }
                $seen[$productId] = true;
                $quantity = $item['Cantidad'] ?? null;
                if (filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity <= 0) {
                    $errors["productos.$index.Cantidad"] = 'La cantidad debe ser un entero mayor que cero';
                }
            }
        }

        $references = [
            'id_cliente' => 'clienteExists',
            'id_personal' => 'personalExists'
        ];
        foreach ($references as $field => $check) {
            $value = $data[$field] ?? '';
            if ($value !== '' && (!ctype_digit((string) $value) || !Pedido::$check($value))) {
                $errors[$field] = "Selecciona un registro existente para $field";
            }
        }

        $date = $data['Fecha_entrega'] ?? '';
        $normalizedDate = null;
        if ($date !== '') {
            $timestamp = is_string($date) ? strtotime($date) : false;
            if ($timestamp === false) $errors['Fecha_entrega'] = 'La fecha de entrega no es valida';
            else $normalizedDate = date('Y-m-d H:i:s', $timestamp);
        }
        if ($errors) return [null, $errors];

        return [[
            'Estado' => trim($data['Estado']),
            'Detalle' => isset($data['Detalle']) && trim($data['Detalle']) !== '' ? trim($data['Detalle']) : null,
            'Metodo_de_pago' => $paymentMethod,
            'Fecha_entrega' => $normalizedDate,
            'id_cliente' => empty($data['id_cliente']) ? null : (int) $data['id_cliente'],
            'id_personal' => empty($data['id_personal']) ? null : (int) $data['id_personal'],
            'productos' => array_map(static function ($item) {
                return ['id_producto' => (int) $item['id_producto'], 'Cantidad' => (int) $item['Cantidad']];
            }, $data['productos'])
        ], []];
    }

    public function add()
    {
        [$data, $errors] = $this->getData();
        if ($errors) return $this->respondErrors($errors);
        [$values, $errors] = $this->validate($data);
        if ($errors) return $this->respondErrors($errors);
        $values['Estado'] = 'Pendiente';
        try {
            $id = Pedido::add($values);
        } catch (PedidoStockException $error) {
            return $this->respondErrors($error->errors, 409);
        }
        echo json_encode(['estado' => true, 'id' => $id, 'message' => 'Pedido agregado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        [$data, $errors] = $this->getData();
        if ($errors) return $this->respondErrors($errors);
        [$values, $errors] = $this->validate($data);
        if ($errors) return $this->respondErrors($errors);
        $current = Pedido::find($id);
        if (!$current) return $this->respondErrors(['id' => 'Pedido no encontrado'], 404);
        $user = AuthSession::currentUser();
        if (mb_strtolower($user['Permiso']) === 'ventas' && $values['Estado'] !== $current['Estado']) {
            return $this->respondErrors(['Estado' => 'Ventas no puede cambiar el estado de elaboración'], 403);
        }
        try {
            Pedido::update($id, $values);
        } catch (PedidoStockException $error) {
            return $this->respondErrors($error->errors, 409);
        }
        echo json_encode(['estado' => true, 'message' => 'Pedido actualizado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function updateStatus($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $allowed = ['En elaboración', 'Listo para entrega', 'Entregado'];
        if (!is_array($data) || !in_array($data['Estado'] ?? null, $allowed, true)) return $this->respondErrors(['Estado' => 'Estado inválido'], 400);
        $result = Pedido::updateStatus($id, $data['Estado']);
        if ($result === 'missing') return $this->respondErrors(['id' => 'Pedido no encontrado'], 404);
        if ($result === 'transition') return $this->respondErrors(['Estado' => 'La transición debe seguir Pendiente → En elaboración → Listo para entrega → Entregado'], 409);
        echo json_encode(['estado' => true, 'message' => 'Estado actualizado correctamente'], JSON_UNESCAPED_UNICODE);
    }
    public function delete($id)
    {
        if (!Pedido::find($id)) return $this->respondErrors(['id' => 'Pedido no encontrado'], 404);
        if (Pedido::hasRelatedRecords($id)) {
            return $this->respondErrors(['relacion' => 'No se puede eliminar: el pedido tiene productos o ventas asociados'], 409);
        }
        Pedido::delete($id);
        echo json_encode(['estado' => true, 'message' => 'Pedido eliminado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    private function respondErrors($errors, $status = 400)
    {
        http_response_code($status);
        echo json_encode(['estado' => false, 'errores' => $errors], JSON_UNESCAPED_UNICODE);
    }
}
