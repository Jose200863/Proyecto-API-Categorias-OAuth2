<?php

function obtenerConexion(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $datos = require __DIR__ . '/datos_conexion.php';
    $dsn = "mysql:host={$datos['host']};dbname={$datos['base']};charset={$datos['charset']}";

    try {
        $pdo = new PDO($dsn, $datos['usuario'], $datos['clave'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error'             => 'server_error',
            'error_description' => 'No se pudo conectar a la base de datos',
        ]);
        exit;
    }

    return $pdo;
}


