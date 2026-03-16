<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('DB_HOST', getenv('MYSQL_HOST') ?: 'db');
define('DB_NAME', getenv('MYSQL_DB')   ?: 'securite_lab');
define('DB_USER', getenv('MYSQL_USER') ?: 'labuser');
define('DB_PASS', getenv('MYSQL_PASS') ?: 'labpass123');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

session_start();
