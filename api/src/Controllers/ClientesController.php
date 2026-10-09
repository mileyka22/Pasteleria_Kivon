<?php
require_once "../src/Models/Clientes.php";

class ClientesController
{
    public function getAll()
    {
        echo json_encode(Clientes::all(), JSON_UNESCAPED_UNICODE);
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
        $allowed = ['Nombre', 'Apellidos', 'CI', 'Telefono', 'Direccion'];
        $errors = [];
        foreach (array_diff(array_keys($data), $allowed) as $field) $errors[$field] = 'Campo no permitido';

        foreach (['Nombre' => 50, 'CI' => 30] as $field => $max) {
            if (!isset($data[$field]) || !is_string($data[$field]) || trim($data[$field]) === '') {
                $errors[$field] = "El campo $field es obligatorio";
            } elseif (strlen(trim($data[$field])) > $max) {
                $errors[$field] = "El campo $field no puede superar $max caracteres";
            }
        }
        foreach (['Apellidos' => 100, 'Telefono' => 20, 'Direccion' => 250] as $field => $max) {
            if (isset($data[$field]) && !is_string($data[$field])) {
                $errors[$field] = "El campo $field debe ser texto";
            } elseif (isset($data[$field]) && strlen(trim($data[$field])) > $max) {
                $errors[$field] = "El campo $field no puede superar $max caracteres";
            }
        }
        if ($errors) return [null, $errors];

        $values = [];
        foreach ($allowed as $field) {
            $value = isset($data[$field]) ? trim($data[$field]) : '';
            $values[$field] = $value === '' ? null : $value;
        }
        return [$values, []];
    }

    public function add()
    {
        [$data, $errors] = $this->getData();
        if ($errors) return $this->respondErrors($errors);
        [$values, $errors] = $this->validate($data);
        if ($errors) return $this->respondErrors($errors);
        echo json_encode(['estado' => (bool) Clientes::add($values), 'message' => 'Cliente agregado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        [$data, $errors] = $this->getData();
        if ($errors) return $this->respondErrors($errors);
        [$values, $errors] = $this->validate($data);
        if ($errors) return $this->respondErrors($errors);
        if (!Clientes::find($id)) return $this->respondErrors(['id' => 'Cliente no encontrado'], 404);
        Clientes::update($id, $values);
        echo json_encode(['estado' => true, 'message' => 'Cliente actualizado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function delete($id)
    {
        if (!Clientes::find($id)) return $this->respondErrors(['id' => 'Cliente no encontrado'], 404);
        if (Clientes::hasOrders($id)) {
            return $this->respondErrors(['relacion' => 'No se puede eliminar: el cliente tiene pedidos asociados'], 409);
        }
        Clientes::delete($id);
        echo json_encode(['estado' => true, 'message' => 'Cliente eliminado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    private function respondErrors($errors, $status = 400)
    {
        http_response_code($status);
        echo json_encode(['estado' => false, 'errores' => $errors], JSON_UNESCAPED_UNICODE);
    }
}
