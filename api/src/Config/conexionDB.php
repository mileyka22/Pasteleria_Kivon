<?php
require_once "config.php";
class ConexionPDO
{
    private static ?PDO  $cnn = null;
    public static function connect(): PDO
    {
        if (self::$cnn instanceof PDO) {
            return self::$cnn;
        }
        $pdo = 'mysql:host=' . HOST . ';port=' . PORT . ';dbname=' . DATABASE . ';' . CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        try {
            self::$cnn = new PDO($pdo, USERNAME, PASSWORD, $options);
        } catch (PDOException $error) {
            throw new RuntimeException("No se pudo conectar con la base de datos", 0, $error);
        }
        return self::$cnn;
    }
    //funcion que ejecute una consulta
    public static function query(string $sql, array $param=[]): array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($param);
        return $stmt->fetchAll();
    }

    //Funcion para ejecutar upsate o add
    
    public static function execute($sql, array $param = [], $id = false)
    {
        $db = self::connect();
        $stmt = $db->prepare($sql);
        $res = $stmt->execute($param);
        if ($id == true) {
            return $db->lastInsertId();
        }
        return $res;
    }
}
