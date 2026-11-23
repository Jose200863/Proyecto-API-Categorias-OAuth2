<?php

class SolicitudRepositorio
{
    public function __construct(private PDO $pdo)
    {
    }

    public function contarRecientes(int $clienteId, int $ventanaSegundos = 60): int
    {
        $segundos = max(1, (int) $ventanaSegundos);

        $sql = "SELECT COUNT(*) AS total
                FROM solicitudes_api
                WHERE cliente_id = :cliente_id
                  AND fecha_solicitud >= (NOW() - INTERVAL {$segundos} SECOND)";

        $st = $this->pdo->prepare($sql);
        $st->execute([':cliente_id' => $clienteId]);
        $fila = $st->fetch();

        return (int) ($fila['total'] ?? 0);
    }

    public function registrar(int $clienteId): void
    {
        $sql = 'INSERT INTO solicitudes_api (cliente_id, fecha_solicitud)
                VALUES (:cliente_id, NOW())';

        $st = $this->pdo->prepare($sql);
        $st->execute([':cliente_id' => $clienteId]);
    }

    public function limpiarAntiguas(int $segundosAntiguedad = 3600): void
    {
        $segundos = max(60, (int) $segundosAntiguedad);
        $sql = "DELETE FROM solicitudes_api
                WHERE fecha_solicitud < (NOW() - INTERVAL {$segundos} SECOND)";
        $this->pdo->exec($sql);
    }
}
