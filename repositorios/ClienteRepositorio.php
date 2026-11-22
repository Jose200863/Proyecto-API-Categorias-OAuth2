<?php

class ClienteRepositorio
{
    public function __construct(private PDO $pdo)
    {
    }

    public function buscarPorClientId(string $clientId): ?array
    {

        $sql = 'SELECT id,
                       identificador AS client_id,
                       secreto_hash  AS client_secret_hash,
                       nombre,
                       permisos      AS scopes,
                       activo
                FROM clientes_oauth
                WHERE identificador = :identificador
                LIMIT 1';
        $st = $this->pdo->prepare($sql);
        $st->execute([':identificador' => $clientId]);
        $fila = $st->fetch();

        return $fila ?: null;
    }
}
