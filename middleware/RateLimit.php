<?php

require_once __DIR__ . '/../utilidades/respuesta.php';
require_once __DIR__ . '/../repositorios/SolicitudRepositorio.php';

function verificarRateLimit(
    PDO $pdo,
    int $clienteId,
    int $limite = 10,
    int $ventanaSegundos = 60
): void {
    $repo = new SolicitudRepositorio($pdo);

    $totalRecientes = $repo->contarRecientes($clienteId, $ventanaSegundos);

    if ($totalRecientes >= $limite) {
        header('Retry-After: ' . $ventanaSegundos);
        header('X-RateLimit-Limit: ' . $limite);
        header('X-RateLimit-Remaining: 0');
        responderJson(429, [
            'error'             => 'too_many_requests',
            'error_description' => "Límite de solicitudes excedido. Se permite un máximo de {$limite} solicitudes cada {$ventanaSegundos} segundos.",
        ]);
    }

    $repo->registrar($clienteId);

    $restantes = max(0, $limite - ($totalRecientes + 1));
    header('X-RateLimit-Limit: ' . $limite);
    header('X-RateLimit-Remaining: ' . $restantes);
}
