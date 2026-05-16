<?php

use App\Config\Database;

class SesionPlenaria
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

    public function listar(): array
    {
        $stmt = $this->conn->prepare(
            'SELECT id_sesion, tipo_pleno, numero_sesion, fecha, hora, estado, observaciones, usuario_creador, fecha_creacion, fecha_actualizacion
             FROM sesiones_plenarias
             ORDER BY fecha DESC, hora DESC, id_sesion DESC'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT id_sesion, tipo_pleno, numero_sesion, fecha, hora, estado, observaciones, usuario_creador, fecha_creacion, fecha_actualizacion
             FROM sesiones_plenarias
             WHERE id_sesion = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $sesion = $stmt->fetch();

        return is_array($sesion) ? $sesion : null;
    }

    public function crear(array $data): int
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO sesiones_plenarias
                (tipo_pleno, numero_sesion, fecha, hora, estado, observaciones, usuario_creador)
             VALUES
                (:tipo_pleno, :numero_sesion, :fecha, :hora, :estado, :observaciones, :usuario_creador)'
        );

        $stmt->execute([
            ':tipo_pleno' => $data['tipo_pleno'],
            ':numero_sesion' => $data['numero_sesion'],
            ':fecha' => $data['fecha'],
            ':hora' => $data['hora'],
            ':estado' => $data['estado'],
            ':observaciones' => $data['observaciones'] ?? null,
            ':usuario_creador' => $data['usuario_creador'] ?? null,
        ]);

        return (int)$this->conn->lastInsertId();
    }

    public function actualizar(int $id, array $data): bool
    {
        $stmt = $this->conn->prepare(
            'UPDATE sesiones_plenarias
             SET tipo_pleno = :tipo_pleno,
                 numero_sesion = :numero_sesion,
                 fecha = :fecha,
                 hora = :hora,
                 estado = :estado,
                 observaciones = :observaciones,
                 fecha_actualizacion = NOW()
             WHERE id_sesion = :id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':tipo_pleno' => $data['tipo_pleno'],
            ':numero_sesion' => $data['numero_sesion'],
            ':fecha' => $data['fecha'],
            ':hora' => $data['hora'],
            ':estado' => $data['estado'],
            ':observaciones' => $data['observaciones'] ?? null,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM sesiones_plenarias WHERE id_sesion = :id');

        return $stmt->execute([':id' => $id]);
    }

    public function generarNumeroSesion(): string
    {
        $stmt = $this->conn->prepare('SELECT COALESCE(MAX(id_sesion), 0) + 1 AS siguiente_id FROM sesiones_plenarias');
        $stmt->execute();
        $row = $stmt->fetch();
        $siguienteId = (int)($row['siguiente_id'] ?? 1);

        return 'PL-' . date('Y') . '-' . str_pad((string)$siguienteId, 3, '0', STR_PAD_LEFT);
    }

    public function listarComisionesActivas(): array
    {
        $stmt = $this->conn->prepare(
            'SELECT idComision, nombreComision
             FROM t_comision
             WHERE vigencia = 1
             ORDER BY nombreComision ASC'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listarTemasPorComision(int $idComision): array
    {
        $stmt = $this->conn->prepare(
            'SELECT
                t.idTema,
                t.nombreTema,
                m.idMinuta,
                c.idComision,
                c.nombreComision
             FROM t_tema t
             INNER JOIN t_minuta m ON t.t_minuta_idMinuta = m.idMinuta
             INNER JOIN t_comision c ON m.t_comision_idComision = c.idComision
             WHERE c.idComision = :idComision
               AND c.vigencia = 1
             ORDER BY t.idTema DESC'
        );
        $stmt->execute([':idComision' => $idComision]);

        return $stmt->fetchAll();
    }
}
