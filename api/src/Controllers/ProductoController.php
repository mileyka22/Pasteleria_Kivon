<?php
require_once "../src/Models/Productos.php";

class ProductoController
{
    public function getAll()
    {
        echo json_encode(PRODUCTO::all(), JSON_UNESCAPED_UNICODE);
    }

    public function getById($id)
    {
        $product = PRODUCTO::find($id);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado'], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode($product, JSON_UNESCAPED_UNICODE);
    }

    private function getJsonData()
    {
        if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') === 0) {
            return [$_POST, []];
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return [null, ['json' => 'Debe enviar datos validos en formato JSON']];
        }
        return [$data, []];
    }

    private function storeImage()
    {
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) return [null, null];
        $file = $_FILES['imagen'];
        if ($file['error'] !== UPLOAD_ERR_OK) return [null, 'No se pudo recibir la imagen. Intenta seleccionarla nuevamente.'];
        if ($file['size'] > 5 * 1024 * 1024) return [null, 'La imagen no debe superar 5 MB.'];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mime]) || @getimagesize($file['tmp_name']) === false) {
            return [null, 'Formato no válido. Usa una imagen JPG, JPEG, PNG o WEBP.'];
        }

        $directory = __DIR__ . '/../../public/uploads/products';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return [null, 'No se pudo preparar la carpeta para guardar imágenes.'];
        }
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            return [null, 'No se pudo guardar la imagen en el servidor.'];
        }
        return ['uploads/products/' . $filename, null];
    }

    private function removeImageIfUnused($path, $exceptId = null)
    {
        if (!$path || !preg_match('/\Auploads\/products\/[a-f0-9]{32}\.(jpg|png|webp)\z/D', $path)) return;
        $sql = 'SELECT id FROM PRODUCTO WHERE Imagen = :imagen';
        $params = [':imagen' => $path];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $exceptId;
        }
        if (ConexionPDO::query($sql . ' LIMIT 1', $params)) return;
        $fullPath = __DIR__ . '/../../public/' . $path;
        if (is_file($fullPath)) unlink($fullPath);
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
        $user = AuthSession::currentUser();
        if (in_array(mb_strtolower(strtr($user['Permiso'], ['ó' => 'o'])), ['ventas', 'produccion'], true) && !empty($data['id_personal'])) {
            return $this->respondErrors(['id_personal' => 'Este perfil no puede asignar personal al producto'], 403);
        }
        [$values, $errors] = $this->normalizeAndValidate($data);
        if ($errors) return $this->respondErrors($errors);
        [$image, $imageError] = $this->storeImage();
        if ($imageError) return $this->respondErrors(['imagen' => $imageError]);
        $values['Imagen'] = $image;
        try {
            $id = PRODUCTO::add($values);
        } catch (Throwable $error) {
            if ($image) @unlink(__DIR__ . '/../../public/' . $image);
            throw $error;
        }
        echo json_encode(['estado' => (bool) $id, 'id' => $id, 'message' => 'Producto agregado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function update($id)
    {
        [$data, $jsonErrors] = $this->getJsonData();
        if ($jsonErrors) return $this->respondErrors($jsonErrors);
        [$values, $errors] = $this->normalizeAndValidate($data);
        if ($errors) return $this->respondErrors($errors);
        $current = PRODUCTO::find($id);
        if (!$current) return $this->respondErrors(['id' => 'Producto no encontrado'], 404);
        $user = AuthSession::currentUser();
        if (mb_strtolower($user['Permiso']) === 'ventas') {
            if (array_key_exists('id_personal', $data)) return $this->respondErrors(['id_personal' => 'Ventas no puede modificar la asignación de personal'], 403);
            $values['id_personal'] = $current['id_personal'] ?? null;
        }
        if (mb_strtolower(strtr($user['Permiso'], ['ó' => 'o'])) === 'produccion') {
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) return $this->respondErrors(['imagen' => 'Producción solo puede actualizar existencias']);
            if (!array_key_exists('id_personal', $data)) $values['id_personal'] = $current['id_personal'] ?? null;
            foreach (['Nombre', 'Precio', 'Ingrediente', 'id_personal'] as $field) {
                $old = $current[$field] ?? null;
                $new = $values[$field] ?? null;
                if ($field === 'Precio' && round((float) $old, 2) === round((float) $new, 2)) continue;
                if ((string) $old !== (string) $new) return $this->respondErrors([$field => 'Producción solo puede actualizar existencias'], 403);
            }
        }
        [$image, $imageError] = $this->storeImage();
        if ($imageError) return $this->respondErrors(['imagen' => $imageError]);
        $values['Imagen'] = $image ?? $current['Imagen'] ?? null;
        try {
            PRODUCTO::update($id, $values);
        } catch (Throwable $error) {
            if ($image) @unlink(__DIR__ . '/../../public/' . $image);
            throw $error;
        }
        if ($image && !empty($current['Imagen'])) $this->removeImageIfUnused($current['Imagen'], $id);
        echo json_encode(['estado' => true, 'message' => 'Producto actualizado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    public function delete($id)
    {
        $product = PRODUCTO::find($id);
        if (!$product) return $this->respondErrors(['id' => 'Producto no encontrado'], 404);
        if (PRODUCTO::hasOrderItems($id)) {
            return $this->respondErrors(['relacion' => 'No se puede eliminar: el producto aparece en pedidos existentes'], 409);
        }
        PRODUCTO::delete($id);
        if (!empty($product['Imagen'])) $this->removeImageIfUnused($product['Imagen']);
        echo json_encode(['estado' => true, 'message' => 'Producto eliminado correctamente'], JSON_UNESCAPED_UNICODE);
    }

    private function respondErrors($errors, $status = 400)
    {
        http_response_code($status);
        echo json_encode(['estado' => false, 'errores' => $errors], JSON_UNESCAPED_UNICODE);
    }
}
