<?php
$path = dirname(DIR, 2) . '/.env';

// 1. Si el archivo .env existe (en local), lo lee. Si no existe (en Render), usa las variables del sistema.
if (file_exists($path)) {
$config = parse_ini_file($path);
} else {
$config = [];
}
$config=parse_ini_file($path);
define("HOST",$config['DB_HOST']);
define("DATABASE",$config['DB_NAME']);
define("USERNAME",$config['DB_USER']);
define("PASSWORD",$config['DB_PASSWORD']);
define("PORT",$config['DB_PORT']);
define("CHARSET",$config['DB_CHARSET']);
