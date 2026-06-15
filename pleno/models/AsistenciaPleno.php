<?php

use App\Config\Database;

class AsistenciaPleno
{
    private $conn;
    private $rolesParticipantes = [1, 22];

    public function __construct($conn = null)
    {
        if ($conn instanceof \PDO) {
            $this->conn = $conn;
            return;
        }

        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function obtenerEstadoUsuario(int $idUsuario): array
    {
        $sesion = $this->obtenerSesionEnCursoHoy();

        if (!$sesion) {
            return [
                'status' => 'none',
                'sesion' => null,
                'asistencia' => null,
                'ya_marco' => false,
            ];
        }

        $asistencia = $this->obtenerAsistenciaUsuario((int)$sesion['id_sesion'], $idUsuario);

        return [
            'status' => 'active',
            'sesion' => $sesion,
            'asistencia' => $asistencia,
            'ya_marco' => is_array($asistencia) && strtoupper((string)($asistencia['estadoAsistencia'] ?? '')) === 'PRESENTE',
        ];
    }

    public function registrarAsistencia(int $idSesion, int $idUsuario): array
    {
        if ($idSesion <= 0 || $idUsuario <= 0) {
            return ['success' => false, 'mensaje' => 'Debe indicar una sesión válida.'];
        }

        $sesion = $this->obtenerSesion($idSesion);
        if (!$sesion || trim((string)($sesion['estado'] ?? '')) !== 'en_curso') {
            return ['success' => false, 'mensaje' => 'El pleno no se encuentra en curso.'];
        }

        if (!$this->usuarioPuedeRegistrar($idUsuario)) {
            return ['success' => false, 'mensaje' => 'Su perfil no está habilitado para registrar asistencia al pleno.'];
        }

        $minutaReferencia = $this->obtenerMinutaReferencia($idSesion);
        if ($minutaReferencia <= 0) {
            return ['success' => false, 'mensaje' => 'No existe una minuta de referencia para registrar la asistencia.'];
        }

        $existente = $this->obtenerAsistenciaUsuario($idSesion, $idUsuario);

        if ($existente) {
            $stmt = $this->conn->prepare(
                "UPDATE t_asistencia
                 SET estadoAsistencia = 'PRESENTE',
                     origenAsistencia = 'AUTOREGISTRO',
                     fechaRegistroAsistencia = NOW(),
                     fechaMarca = NOW(),
                     id_sesion_plenaria = :id_sesion
                 WHERE idAsistencia = :id_asistencia"
            );
            $stmt->execute([
                ':id_sesion' => $idSesion,
                ':id_asistencia' => (int)$existente['idAsistencia'],
            ]);

            return ['success' => true, 'mensaje' => 'Asistencia registrada correctamente.'];
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO t_asistencia
                (id_sesion_plenaria, t_minuta_idMinuta, t_usuario_idUsuario, t_tipoReunion_idTipoReunion, estadoAsistencia, origenAsistencia, fechaRegistroAsistencia, fechaMarca)
             VALUES
                (:id_sesion, :id_minuta, :id_usuario, 1, 'PRESENTE', 'AUTOREGISTRO', NOW(), NOW())"
        );
        $stmt->execute([
            ':id_sesion' => $idSesion,
            ':id_minuta' => $minutaReferencia,
            ':id_usuario' => $idUsuario,
        ]);

        return ['success' => true, 'mensaje' => 'Asistencia registrada correctamente.'];
    }

    public function obtenerResumenSesion(int $idSesion): array
    {
        $participantes = $this->listarParticipantesConAsistencia($idSesion);
        $presentes = 0;

        foreach ($participantes as $participante) {
            if (strtoupper((string)($participante['estadoAsistencia'] ?? '')) === 'PRESENTE') {
                $presentes++;
            }
        }

        return [
            'total' => count($participantes),
            'presentes' => $presentes,
            'ausentes' => max(count($participantes) - $presentes, 0),
            'participantes' => $participantes,
        ];
    }

    public function usuarioPresente(int $idSesion, int $idUsuario): bool
    {
        $asistencia = $this->obtenerAsistenciaUsuario($idSesion, $idUsuario);

        return is_array($asistencia) && strtoupper((string)($asistencia['estadoAsistencia'] ?? '')) === 'PRESENTE';
    }

    private function obtenerSesionEnCursoHoy(): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id_sesion, tipo_pleno, numero_sesion, fecha, hora, lugar, estado, punto_actual
             FROM sesiones_plenarias
             WHERE fecha = CURDATE()
               AND vigencia = 1
               AND estado = 'en_curso'
             ORDER BY hora ASC, id_sesion ASC
             LIMIT 1"
        );
        $stmt->execute();
        $sesion = $stmt->fetch();

        return is_array($sesion) ? $sesion : null;
    }

    private function obtenerSesion(int $idSesion): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT id_sesion, tipo_pleno, numero_sesion, fecha, hora, lugar, estado
             FROM sesiones_plenarias
             WHERE id_sesion = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $idSesion]);
        $sesion = $stmt->fetch();

        return is_array($sesion) ? $sesion : null;
    }

    private function obtenerAsistenciaUsuario(int $idSesion, int $idUsuario): ?array
    {
        $stmt = $this->conn->prepare(
            'SELECT idAsistencia, estadoAsistencia, origenAsistencia, fechaRegistroAsistencia, fechaMarca
             FROM t_asistencia
             WHERE id_sesion_plenaria = :id_sesion
               AND t_usuario_idUsuario = :id_usuario
             ORDER BY idAsistencia DESC
             LIMIT 1'
        );
        $stmt->execute([
            ':id_sesion' => $idSesion,
            ':id_usuario' => $idUsuario,
        ]);
        $asistencia = $stmt->fetch();

        return is_array($asistencia) ? $asistencia : null;
    }

    private function listarParticipantesConAsistencia(int $idSesion): array
    {
        $placeholders = implode(',', array_fill(0, count($this->rolesParticipantes), '?'));
        $sql = "SELECT
                    u.idUsuario,
                    TRIM(CONCAT(u.pNombre, ' ', COALESCE(NULLIF(u.sNombre, ''), ''), ' ', u.aPaterno, ' ', u.aMaterno)) AS nombreCompleto,
                    u.tipoUsuario_id,
                    a.idAsistencia,
                    a.fechaRegistroAsistencia,
                    a.estadoAsistencia,
                    a.origenAsistencia
                FROM t_usuario u
                LEFT JOIN t_asistencia a
                    ON a.t_usuario_idUsuario = u.idUsuario
                   AND a.id_sesion_plenaria = ?
                WHERE u.estado = 1
                  AND u.tipoUsuario_id IN ($placeholders)
                ORDER BY u.aPaterno ASC, u.aMaterno ASC, u.pNombre ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(array_merge([$idSesion], $this->rolesParticipantes));
        $participantes = $stmt->fetchAll();

        return is_array($participantes) ? $participantes : [];
    }

    private function usuarioPuedeRegistrar(int $idUsuario): bool
    {
        $placeholders = implode(',', array_fill(0, count($this->rolesParticipantes), '?'));
        $stmt = $this->conn->prepare(
            "SELECT 1
             FROM t_usuario
             WHERE idUsuario = ?
               AND estado = 1
               AND tipoUsuario_id IN ($placeholders)
             LIMIT 1"
        );
        $stmt->execute(array_merge([$idUsuario], $this->rolesParticipantes));

        return (bool)$stmt->fetchColumn();
    }

    private function obtenerMinutaReferencia(int $idSesion): int
    {
        $stmtTema = $this->conn->prepare(
            'SELECT m.idMinuta
             FROM sesion_plenaria_temas spt
             INNER JOIN t_tema t ON t.idTema = spt.id_tema
             INNER JOIN t_minuta m ON m.idMinuta = t.t_minuta_idMinuta
             WHERE spt.id_sesion = :id_sesion
               AND spt.vigente = 1
             ORDER BY spt.orden ASC, spt.id ASC
             LIMIT 1'
        );
        $stmtTema->execute([':id_sesion' => $idSesion]);
        $idMinuta = (int)$stmtTema->fetchColumn();

        if ($idMinuta > 0) {
            return $idMinuta;
        }

        $stmtFallback = $this->conn->query('SELECT idMinuta FROM t_minuta ORDER BY idMinuta ASC LIMIT 1');

        return $stmtFallback ? (int)$stmtFallback->fetchColumn() : 0;
    }
}
