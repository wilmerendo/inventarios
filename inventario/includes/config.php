<?php
// Configuración desde variables de entorno
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'inventario');

define('STOCK_MINIMO', getenv('STOCK_MINIMO') ?: 5);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>