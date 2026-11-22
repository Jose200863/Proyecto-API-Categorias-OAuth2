<?php

require_once __DIR__ . '/../utilidades/respuesta.php';
require_once __DIR__ . '/../configuracion/conexion.php';
require_once __DIR__ . '/AutenticarCliente.php';
require_once __DIR__ . '/../repositorios/TokenRepositorio.php';

header('Cache-Control: no-store');
header('Pragma: no-cache');

try {
    $pdo = obtenerConexion();
    $cliente = autenticarCliente($pdo);

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $duracion = 900;
    $clienteId = (int) $cliente['id'];
    $permisos = $cliente['scopes'];

    $repositorio = new TokenRepositorio($pdo);
    $repositorio->guardar(
        $tokenHash,
        $clienteId,
        $permisos,
        $duracion
    );

    responderJson(200, [
        'access_token' => $token,
        'token_type' => 'Bearer',
        'expires_in' => $duracion,
        'scope' => $permisos
    ]);
} catch (Exception $e) {
    responderJson(500, [
        'error' => 'server_error',
        'error_description' => 'No se pudo generar o guardar el token'
    ]);
}