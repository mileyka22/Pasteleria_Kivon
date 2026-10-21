<?php
require_once "../src/Models/Venta.php";

class VentaController
{
    public function getAll()
    {
        echo json_encode(Venta::all(), JSON_UNESCAPED_UNICODE);
    }

    public function getDashboard()
    {
        echo json_encode(Venta::dashboard(), JSON_UNESCAPED_UNICODE);
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
        $allowed = ['Total', 'Metodo_de_pago', 'id_personal', 'id_pedido'];
        $errors = [];
        foreach (array_diff(array_keys($data), $allowed) as $field) $errors[$field] = 'Campo no permitido';

        if (!isset($data['Total']) || !is_numeric($data['Total']) || (float) $data['Total'] < 0) {
            $errors['Total'] = 'El total debe ser un numero mayor o igual a cero';
        }
        if (!isset($data['Metodo_de_pago']) || !is_string($data['Metodo_de_pago']) || trim($data['Metodo_de_pago']) === '') {
            $errors['Metodo_de_pago'] = 'El metodo de pago es obligatorio';
        } elseif (strlen(trim($data['Metodo_de_pago'])) > 255) {
            $errors['Metodo_de_pago'] = 'El metodo de pago no puede superar 255 caracteres';
        }

        foreach (['id_personal' => 'personalExists', 'id_pedido' => 'pedidoExists'] as $field => $check) {
            $value = $data[$field] ?? '';
            if ($value !== '' && (!ctype_digit((string) $value) || !Venta::$check($value))) {
                $errors[$field] = "Selecciona un registro existente para $field";
            }
        }
        if ($errors) return [null, $errors];

        return [[
            'Total' => (float) $data['Total'],
            'Metodo_de_pago' => trim($data['Metodo_de_pago']),
            'id_personal' => empty($data['id_personal']) ? null : (int) $data['id_personal'],
            'id_pedido' => empty($data['id_pedido']) ? null : (int) $data['id_pedido']
        ], []];
    }

    public function add()
    {
        [$data, $errors] = $this->getData();
        if ($errors) return $this->respondErrors($errors);
        [$values, $errors] = $this->validate($data);
        if ($errors) return $this->respondErrors($errors);
        $totalError = $this->validateOrderTotal($values);
        if ($totalError) return $this->respondErrors(['Total' => $totalError]);
        echo json_encode(['estado' => (bool) Venta::add($values), 'message' => 'Venta agregada correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        [$data, $errors] = $this->getData();
        if ($errors) return $this->respondErrors($errors);
        [$values, $errors] = $this->validate($data);
        if ($errors) return $this->respondErrors($errors);
        $existing = Venta::find($id);
        if (!$existing) return $this->respondErrors(['id' => 'Venta no encontrada'], 404);
        $totalError = $this->validateOrderTotal($values, $existing);
        if ($totalError) return $this->respondErrors(['Total' => $totalError]);
        Venta::update($id, $values);
        echo json_encode(['estado' => true, 'message' => 'Venta actualizada correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function delete($id)
    {
        if (!Venta::find($id)) return $this->respondErrors(['id' => 'Venta no encontrada'], 404);
        Venta::delete($id);
        echo json_encode(['estado' => true, 'message' => 'Venta eliminada correctamente'], JSON_UNESCAPED_UNICODE);
    }

    private function respondErrors($errors, $status = 400)
    {
        http_response_code($status);
        echo json_encode(['estado' => false, 'errores' => $errors], JSON_UNESCAPED_UNICODE);
    }

    private function validateOrderTotal($values, $existing = null)
    {
        if ($values['id_pedido'] === null) return null;
        $itemsTotal = Venta::orderItemsTotal($values['id_pedido']);
        if ($itemsTotal === null) return null;

        $keepsExistingMismatch = $existing
            && (string) $existing['id_pedido'] === (string) $values['id_pedido']
            && round((float) $existing['Total'], 2) === round((float) $values['Total'], 2);
        if ($keepsExistingMismatch) return null;

        if (abs($itemsTotal - round((float) $values['Total'], 2)) >= 0.01) {
            return 'El total debe coincidir con la suma de los productos asociados al pedido';
        }
        return null;
    }
}
