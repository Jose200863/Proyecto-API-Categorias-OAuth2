<?php

require_once __DIR__ . '/../utilidades/respuesta.php';

function obtenerTokenBearer(): ?string
{
    $cabecera = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if ($cabecera === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $nombre => $valor) {
            if (strcasecmp($nombre, 'Authorization') === 0) {
                $cabecera = $valor;
                break;
            }
        }
    }

    if (preg_match('/^Bearer\s+(\S+)$/i', trim($cabecera), $coincidencia)) {
        return $coincidencia[1];
    }
    return null;
}

function rechazarToken(string $descripcion): void
{
    header('WWW-Authenticate: Bearer realm="API Categorias"');
    responderJson(401, [
        'error'             => 'invalid_token',
        'error_description' => $descripcion,
    ]);
}

function validarToken(PDO $pdo): array
{
    $token = obtenerTokenBearer();
    if ($token === null) {
        rechazarToken('Falta el token de acceso (Authorization: Bearer <token>)');
    }

    $hash = hash('sha256', $token);

    $sql = 'SELECT t.cliente_id,
                   t.permisos,
                   (t.fecha_vencimiento > NOW()) AS vigente
            FROM tokens_oauth t
            INNER JOIN clientes_oauth c ON c.id = t.cliente_id
            WHERE t.token_hash = :hash
              AND c.activo = 1
            LIMIT 1';
    $st = $pdo->prepare($sql);
    $st->execute([':hash' => $hash]);
    $fila = $st->fetch();

    if (!$fila) {
        rechazarToken('Token inválido');
    }

    if ((int) $fila['vigente'] !== 1) {
        rechazarToken('Token vencido');
    }

    $permisos = array_values(array_filter(explode(' ', $fila['permisos'])));

    return [
        'id'     => (int) $fila['cliente_id'],
        'scopes' => $permisos,
    ];
}

