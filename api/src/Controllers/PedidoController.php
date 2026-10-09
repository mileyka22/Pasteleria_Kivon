<?php
require_once "../src/Models/Pedido.php";

class PedidoController
{
    public function getAll()
    {
        echo json_encode(Pedido::all(), JSON_UNESCAPED_UNICODE);
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
        $allowed = ['Estado', 'Detalle', 'Fecha_entrega', 'id_cliente', 'id_personal'];
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
            'Fecha_entrega' => $normalizedDate,
            'id_cliente' => empty($data['id_cliente']) ? null : (int) $data['id_cliente'],
            'id_personal' => empty($data['id_personal']) ? null : (int) $data['id_personal']
        ], []];
    }

    public function add()
    {
        [$data, $errors] = $this->getData();
        if ($errors) return $this->respondErrors($errors);
        [$values, $errors] = $this->validate($data);
        if ($errors) return $this->respondErrors($errors);
        echo json_encode(['estado' => (bool) Pedido::add($values), 'message' => 'Pedido agregado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        [$data, $errors] = $this->getData();
        if ($errors) return $this->respondErrors($errors);
        [$values, $errors] = $this->validate($data);
        if ($errors) return $this->respondErrors($errors);
        if (!Pedido::find($id)) return $this->respondErrors(['id' => 'Pedido no encontrado'], 404);
        Pedido::update($id, $values);
        echo json_encode(['estado' => true, 'message' => 'Pedido actualizado correctamente'], JSON_UNESCAPED_UNICODE);
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
