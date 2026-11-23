<?php

require_once __DIR__ . '/../utilidades/respuesta.php';

function rechazarPermiso(string $descripcion): void
{
    $descripcionCabecera = addcslashes($descripcion, "\\\"");
    header(
        'WWW-Authenticate: Bearer realm="API Categorias", '
        . 'error="insufficient_scope", '
        . 'error_description="' . $descripcionCabecera . '"'
    );
    responderJson(403, [
        'error'             => 'insufficient_scope',
        'error_description' => $descripcion,
    ]);
}

function exigirPermiso(array|string $scopes, string $metodo): void
{
    $metodoNormalizado = strtoupper(trim($metodo));

    $mapaPermisos = [
        'GET'    => 'categorias:leer',
        'POST'   => 'categorias:escribir',
        'PUT'    => 'categorias:escribir',
        'DELETE' => 'categorias:eliminar',
    ];

    if (!isset($mapaPermisos[$metodoNormalizado])) {
        return;
    }

    $permisoRequerido = $mapaPermisos[$metodoNormalizado];

    $listaScopes = is_array($scopes)
        ? $scopes
        : array_values(array_filter(explode(' ', (string) $scopes)));

    if (!in_array($permisoRequerido, $listaScopes, true)) {
        rechazarPermiso("Permiso insuficiente. Se requiere el permiso '{$permisoRequerido}' para esta operación.");
    }
}
