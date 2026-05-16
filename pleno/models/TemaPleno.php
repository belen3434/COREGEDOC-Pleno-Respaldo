<?php

use App\Config\Database;

class TemaPleno
{
    private $conn;

    public function __construct($conn = null)
    {
        if ($conn instanceof \PDO) {
            $this->conn = $conn;
            return;
        }

        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function guardarPuntosSesion(int $idSesion, array $puntos): void
    {
        $existentes = $this->listarPuntosSesion($idSesion);
        $existentesPorId = [];

        foreach ($existentes as $puntoExistente) {
            $id = (int)$puntoExistente['id'];
            $existentesPorId[$id] = true;
        }

        $orden = 1;

        foreach ($puntos as $punto) {
            $idPunto = (int)($punto['id'] ?? 0);
            $idComision = (int)($punto['id_comision'] ?? 0);
            $idTema = isset($punto['id_tema']) && $punto['id_tema'] !== '' ? (int)$punto['id_tema'] : null;
            $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));
            $tipoPunto = $tipoPunto !== '' ? $tipoPunto : 'COMISION';

            if ($idComision <= 0) {
                continue;
            }

            if ($idPunto > 0 && isset($existentesPorId[$idPunto])) {
                $stmt = $this->conn->prepare(
                    'UPDATE sesion_plenaria_temas
                     SET id_comision = :id_comision,
                         id_tema = :id_tema,
                         tipo_punto = :tipo_punto,
                         orden = :orden,
                         vigente = 1,
                         fecha_actualizacion = NOW()
                     WHERE id = :id AND id_sesion = :id_sesion'
                );
                $stmt->execute([
                    ':id' => $idPunto,
                    ':id_sesion' => $idSesion,
                    ':id_comision' => $idComision,
                    ':id_tema' => $idTema,
                    ':tipo_punto' => $tipoPunto,
                    ':orden' => $orden,
                ]);
            } else {
                $stmt = $this->conn->prepare(
                    'INSERT INTO sesion_plenaria_temas (id_sesion, id_comision, id_tema, tipo_punto, orden, vigente)
                     VALUES (:id_sesion, :id_comision, :id_tema, :tipo_punto, :orden, 1)'
                );
                $stmt->execute([
                    ':id_sesion' => $idSesion,
                    ':id_comision' => $idComision,
                    ':id_tema' => $idTema,
                    ':tipo_punto' => $tipoPunto,
                    ':orden' => $orden,
                ]);
            }

            $orden += 1;
        }
    }

    public function listarPuntosSesion(int $idSesion): array
    {
        $stmt = $this->conn->prepare(
            'SELECT
                spt.id,
                spt.id_sesion,
                spt.id_comision,
                spt.id_tema,
                spt.tipo_punto,
                spt.orden,
                c.nombreComision,
                t.nombreTema
             FROM sesion_plenaria_temas spt
             INNER JOIN t_comision c ON c.idComision = spt.id_comision
             LEFT JOIN t_tema t ON t.idTema = spt.id_tema
             WHERE spt.id_sesion = :id_sesion
               AND spt.vigente = 1
             ORDER BY spt.orden ASC, spt.id ASC'
        );
        $stmt->execute([':id_sesion' => $idSesion]);

        return $stmt->fetchAll();
    }

    public function eliminarLogicoPunto(int $idPunto, int $idSesion): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE sesion_plenaria_temas
             SET vigente = 0,
                 fecha_actualizacion = NOW()
             WHERE id = :id
               AND id_sesion = :id_sesion'
        );

        return $stmt->execute([
            ':id' => $idPunto,
            ':id_sesion' => $idSesion,
        ]);
    }
}
