<?php
require_once "../src/Models/Productos.php";

class ProductoController
{
    public function getAll()
    {
        echo json_encode(PRODUCTO::all(), JSON_UNESCAPED_UNICODE);
    }

    private function getJsonData()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return [null, ['json' => 'Debe enviar datos validos en formato JSON']];
        }
        return [$data, []];
    }

    private function normalizeAndValidate($data)
    {
        $errors = [];
        $allowed = ['Nombre', 'Precio', 'Cantidad', 'Ingrediente', 'id_personal'];
        foreach (array_diff(array_keys($data), $allowed) as $field) {
            $errors[$field] = 'Campo no permitido';
        }

        if (!isset($data['Nombre']) || !is_string($data['Nombre']) || trim($data['Nombre']) === '') {
            $errors['Nombre'] = 'El nombre es obligatorio';
        } elseif (strlen(trim($data['Nombre'])) > 80) {
            $errors['Nombre'] = 'El nombre no puede superar 80 caracteres';
        }

        if (!isset($data['Precio']) || !is_numeric($data['Precio']) || (float) $data['Precio'] < 0) {
            $errors['Precio'] = 'El precio debe ser un numero mayor o igual a cero';
        }
        if (!isset($data['Cantidad']) || filter_var($data['Cantidad'], FILTER_VALIDATE_INT) === false || (int) $data['Cantidad'] < 0) {
            $errors['Cantidad'] = 'La cantidad debe ser un entero mayor o igual a cero';
        }
        if (isset($data['Ingrediente']) && !is_string($data['Ingrediente'])) {
            $errors['Ingrediente'] = 'El ingrediente debe ser texto';
        } elseif (isset($data['Ingrediente']) && strlen($data['Ingrediente']) > 255) {
            $errors['Ingrediente'] = 'El ingrediente no puede superar 255 caracteres';
        }

        $personalId = $data['id_personal'] ?? '';
        if ($personalId !== '' && (!ctype_digit((string) $personalId) || !PRODUCTO::personalExists($personalId))) {
            $errors['id_personal'] = 'Selecciona un registro de personal existente';
        }

        if ($errors) return [null, $errors];
        return [[
            'Nombre' => trim($data['Nombre']),
            'Precio' => (float) $data['Precio'],
            'Cantidad' => (int) $data['Cantidad'],
            'Ingrediente' => isset($data['Ingrediente']) && trim($data['Ingrediente']) !== '' ? trim($data['Ingrediente']) : null,
            'id_personal' => $personalId === '' ? null : (int) $personalId
        ], []];
    }

    public function add()
    {
        [$data, $jsonErrors] = $this->getJsonData();
        if ($jsonErrors) return $this->respondErrors($jsonErrors);
        [$values, $errors] = $this->normalizeAndValidate($data);
        if ($errors) return $this->respondErrors($errors);

        echo json_encode(['estado' => (bool) PRODUCTO::add($values), 'message' => 'Producto agregado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        [$data, $jsonErrors] = $this->getJsonData();
        if ($jsonErrors) return $this->respondErrors($jsonErrors);
        [$values, $errors] = $this->normalizeAndValidate($data);
        if ($errors) return $this->respondErrors($errors);
        if (!PRODUCTO::find($id)) return $this->respondErrors(['id' => 'Producto no encontrado'], 404);

        PRODUCTO::update($id, $values);
        echo json_encode(['estado' => true, 'message' => 'Producto actualizado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function delete($id)
    {
        if (!PRODUCTO::find($id)) return $this->respondErrors(['id' => 'Producto no encontrado'], 404);
        if (PRODUCTO::hasOrderItems($id)) {
            return $this->respondErrors(['relacion' => 'No se puede eliminar: el producto aparece en pedidos existentes'], 409);
        }
        PRODUCTO::delete($id);
        echo json_encode(['estado' => true, 'message' => 'Producto eliminado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    private function respondErrors($errors, $status = 400)
    {
        http_response_code($status);
        echo json_encode(['estado' => false, 'errores' => $errors], JSON_UNESCAPED_UNICODE);
    }
}
