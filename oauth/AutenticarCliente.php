<?php

require_once __DIR__ . '/../utilidades/respuesta.php';          
require_once __DIR__ . '/../repositorios/ClienteRepositorio.php';

function leerCredenciales(): array
{
    $id = $_SERVER['PHP_AUTH_USER'] ?? null;
    $secreto = $_SERVER['PHP_AUTH_PW'] ?? null;

    if ($id === null) {
        $cabecera = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';
        if (stripos($cabecera, 'Basic ') === 0) {
            $decodificado = base64_decode(substr($cabecera, 6), true);
            if ($decodificado !== false && str_contains($decodificado, ':')) {
                [$id, $secreto] = explode(':', $decodificado, 2);
            }
        }
    }

    if ($id === null) {
        $id = $_POST['client_id'] ?? null;
        $secreto = $_POST['client_secret'] ?? null;
    }

    return [$id, $secreto];
}

function autenticarCliente(PDO $pdo): array
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        responderJson(405, [
            'error'             => 'method_not_allowed',
            'error_description' => 'El endpoint solo acepta POST',
        ]);
    }

    $grantType = $_POST['grant_type'] ?? '';
    if ($grantType === '') {
        responderJson(400, [
            'error'             => 'invalid_request',
            'error_description' => 'Falta el parámetro grant_type',
        ]);
    }
    if ($grantType !== 'client_credentials') {
        responderJson(400, [
            'error'             => 'unsupported_grant_type',
            'error_description' => 'Solo se admite grant_type=client_credentials',
        ]);
    }
    [$clientId, $clientSecret] = leerCredenciales();
    if ($clientId === null || $clientId === '' || $clientSecret === null || $clientSecret === '') {
        responderJson(400, [
            'error'             => 'invalid_request',
            'error_description' => 'Faltan client_id o client_secret',
        ]);
    }

    $repo = new ClienteRepositorio($pdo);
    $cliente = $repo->buscarPorClientId($clientId);

    if ($cliente === null
        || (int) $cliente['activo'] !== 1
        || !password_verify($clientSecret, $cliente['client_secret_hash'])) {
        header('WWW-Authenticate: Basic realm="OAuth"');
        responderJson(401, [
            'error'             => 'invalid_client',
            'error_description' => 'Credenciales de cliente inválidas',
        ]);
    }

    unset($cliente['client_secret_hash']);
    return $cliente;
}

