<?php

class TokenRepositorio
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function guardar(
        string $tokenHash,
        int $clienteId,
        string $permisos,
        int $duracion
    ): void {
        $sql = 'INSERT INTO tokens_oauth
                    (token_hash, cliente_id, permisos, fecha_vencimiento)
                VALUES (?, ?, ?, TIMESTAMPADD(SECOND, ?, NOW()))';

        $consulta = $this->pdo->prepare($sql);

        $consulta->execute([
            $tokenHash,
            $clienteId,
            $permisos,
            $duracion
        ]);
    }
}