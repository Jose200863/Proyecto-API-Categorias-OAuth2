<?php

class ClienteRepositorio
{
    public function __construct(private PDO $pdo)
    {
    }

    public function buscarPorClientId(string $clientId): ?array
    {
        $sql = 'SELECT id, client_id, client_secret_hash, nombre, scopes, activo
                FROM oauth_clients
                WHERE client_id = :client_id
                LIMIT 1';
        $st = $this->pdo->prepare($sql);
        $st->execute([':client_id' => $clientId]);
        $fila = $st->fetch();

        return $fila ?: null;
    }
}

