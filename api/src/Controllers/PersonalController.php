<?php
require_once __DIR__ . "/../Models/Personal.php";

class PersonalController
{
    public function getAll()
    {
        echo json_encode(Personal::all(), JSON_UNESCAPED_UNICODE);
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
        $allowed = ['Nombre', 'Cargo', 'Telefono', 'Salario', 'id_usuario'];
        $errors = [];
        foreach (array_diff(array_keys($data), $allowed) as $field) $errors[$field] = 'Campo no permitido';

        foreach (['Nombre' => 50, 'Cargo' => 100] as $field => $max) {
            if (!isset($data[$field]) || !is_string($data[$field]) || trim($data[$field]) === '') {
                $errors[$field] = "El campo $field es obligatorio";
            } elseif (strlen(trim($data[$field])) > $max) {
                $errors[$field] = "El campo $field no puede superar $max caracteres";
            }
        }
        if (!isset($data['Salario']) || !is_numeric($data['Salario']) || (float) $data['Salario'] < 0) {
            $errors['Salario'] = 'El salario debe ser un numero mayor o igual a cero';
        }
        if (isset($data['Telefono']) && (!is_string($data['Telefono']) || strlen(trim($data['Telefono'])) > 50)) {
            $errors['Telefono'] = 'El telefono debe ser texto de hasta 50 caracteres';
        }

        $userId = $data['id_usuario'] ?? '';
        if ($userId !== '' && (!ctype_digit((string) $userId) || !Personal::userExists($userId))) {
            $errors['id_usuario'] = 'Selecciona un usuario existente';
        }
        if ($errors) return [null, $errors];

        return [[
            'Nombre' => trim($data['Nombre']),
            'Cargo' => trim($data['Cargo']),
            'Telefono' => isset($data['Telefono']) && trim($data['Telefono']) !== '' ? trim($data['Telefono']) : null,
            'Salario' => (float) $data['Salario'],
            'id_usuario' => $userId === '' ? null : (int) $userId
        ], []];
    }

    public function add()
    {
        [$data, $errors] = $this->getData();
        if ($errors) return $this->respondErrors($errors);
        [$values, $errors] = $this->validate($data);
        if ($errors) return $this->respondErrors($errors);
        echo json_encode(['estado' => (bool) Personal::add($values), 'message' => 'Personal agregado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        [$data, $errors] = $this->getData();
        if ($errors) return $this->respondErrors($errors);
        [$values, $errors] = $this->validate($data);
        if ($errors) return $this->respondErrors($errors);
        if (!Personal::find($id)) return $this->respondErrors(['id' => 'Registro de personal no encontrado'], 404);
        Personal::update($id, $values);
        echo json_encode(['estado' => true, 'message' => 'Personal actualizado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function delete($id)
    {
        if (!Personal::find($id)) return $this->respondErrors(['id' => 'Registro de personal no encontrado'], 404);
        if (Personal::hasRelatedRecords($id)) {
            return $this->respondErrors(['relacion' => 'No se puede eliminar: el personal esta asociado a productos, pedidos o ventas'], 409);
        }
        Personal::delete($id);
        echo json_encode(['estado' => true, 'message' => 'Personal eliminado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    private function respondErrors($errors, $status = 400)
    {
        http_response_code($status);
        echo json_encode(['estado' => false, 'errores' => $errors], JSON_UNESCAPED_UNICODE);
    }
}
