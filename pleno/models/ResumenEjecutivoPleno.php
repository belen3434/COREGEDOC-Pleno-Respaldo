<?php

use App\Config\Database;

class ResumenEjecutivoPleno
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

    public function obtenerUltimoResumen(int $idSesion): ?array
    {
        if ($idSesion <= 0) {
            return null;
        }

        try {
            $this->asegurarTablaHistorial();
            $stmt = $this->conn->prepare(
                "SELECT r.*,
                    TRIM(CONCAT(u.pNombre, ' ', COALESCE(NULLIF(u.sNombre, ''), ''), ' ', u.aPaterno, ' ', u.aMaterno)) AS usuario_generador_nombre
                 FROM pleno_resumenes_ejecutivos r
                 LEFT JOIN t_usuario u ON u.idUsuario = r.usuario_generador
                 WHERE r.id_sesion = :id_sesion
                   AND r.estado IN ('vigente', 'generado', 'activo')
                 ORDER BY r.fecha_generacion DESC, r.version DESC, r.id_resumen DESC
                 LIMIT 1"
            );
            $stmt->execute([':id_sesion' => $idSesion]);
            $resumen = $stmt->fetch();
        } catch (Throwable $e) {
            return null;
        }

        return is_array($resumen) ? $this->normalizarResumen($resumen) : null;
    }

    public function generarResumen(int $idSesion, int $idUsuario): array
    {
        if ($idSesion <= 0) {
            return ['success' => false, 'mensaje' => 'No se pudo identificar la sesion plenaria.'];
        }

        $rutaFisicaGenerada = null;

        try {
            $this->asegurarTablaHistorial();
            $this->conn->beginTransaction();

            $sesion = $this->obtenerSesion($idSesion, true);
            if (!$sesion) {
                $this->conn->rollBack();
                return ['success' => false, 'mensaje' => 'La sesion plenaria no existe.'];
            }

            $version = $this->calcularSiguienteVersion($idSesion);
            $numeroResumen = 'RES-EJEC-' . trim((string)$sesion['numero_sesion']) . '-V' . $version;
            $usuario = $this->obtenerUsuario($idUsuario);
            $fechaGeneracion = date('Y-m-d H:i:s');
            $snapshot = $this->construirSnapshot($idSesion, $sesion, $usuario, $fechaGeneracion, $version);
            $snapshotJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($snapshotJson === false) {
                $this->conn->rollBack();
                return ['success' => false, 'mensaje' => 'No fue posible preparar el snapshot del resumen ejecutivo.'];
            }

            $hashValidacion = hash('sha256', $snapshotJson);

            $stmtReemplazar = $this->conn->prepare(
                "UPDATE pleno_resumenes_ejecutivos
                 SET estado = 'reemplazado',
                     usuario_actualizador = :usuario_actualizador,
                     fecha_actualizacion = NOW()
                 WHERE id_sesion = :id_sesion
                   AND estado = 'vigente'"
            );
            $stmtReemplazar->execute([
                ':usuario_actualizador' => $idUsuario > 0 ? $idUsuario : null,
                ':id_sesion' => $idSesion,
            ]);

            $totales = $snapshot['totales'] ?? [];
            $stmt = $this->conn->prepare(
                "INSERT INTO pleno_resumenes_ejecutivos
                    (id_sesion, version, numero_resumen, hash_validacion, total_asistentes, total_votaciones, total_acuerdos, usuario_generador, fecha_generacion, estado, snapshot_json, nombre_archivo, path_archivo)
                 VALUES
                    (:id_sesion, :version, :numero_resumen, :hash_validacion, :total_asistentes, :total_votaciones, :total_acuerdos, :usuario_generador, :fecha_generacion, 'vigente', :snapshot_json, NULL, NULL)"
            );
            $stmt->execute([
                ':id_sesion' => $idSesion,
                ':version' => $version,
                ':numero_resumen' => $numeroResumen,
                ':hash_validacion' => $hashValidacion,
                ':total_asistentes' => (int)($totales['asistentes'] ?? 0),
                ':total_votaciones' => (int)($totales['votaciones'] ?? 0),
                ':total_acuerdos' => (int)($totales['acuerdos'] ?? 0),
                ':usuario_generador' => $idUsuario > 0 ? $idUsuario : null,
                ':fecha_generacion' => $fechaGeneracion,
                ':snapshot_json' => $snapshotJson,
            ]);

            $idResumen = (int)$this->conn->lastInsertId();
            $resumenPdf = [
                'id_resumen' => $idResumen,
                'version' => $version,
                'numero_resumen' => $numeroResumen,
                'hash_validacion' => $hashValidacion,
                'usuario_generador' => trim((string)($usuario['nombre_completo'] ?? '')) ?: 'Sin registro',
                'fecha_generacion' => $fechaGeneracion,
            ];

            if (!class_exists('ResumenEjecutivoPdfService')) {
                require_once dirname(__DIR__) . '/services/ResumenEjecutivoPdfService.php';
            }

            $archivoPdf = (new ResumenEjecutivoPdfService())->generar($snapshot, $resumenPdf);
            $rutaFisicaGenerada = $archivoPdf['ruta_fisica'] ?? null;

            $stmtArchivo = $this->conn->prepare(
                'UPDATE pleno_resumenes_ejecutivos
                 SET nombre_archivo = :nombre_archivo,
                     path_archivo = :path_archivo
                 WHERE id_resumen = :id_resumen'
            );
            $stmtArchivo->execute([
                ':nombre_archivo' => $archivoPdf['nombre_archivo'],
                ':path_archivo' => $archivoPdf['path_archivo'],
                ':id_resumen' => $idResumen,
            ]);

            $this->conn->commit();

            return [
                'success' => true,
                'mensaje' => 'Resumen ejecutivo generado correctamente.',
                'resumen' => [
                    'id_resumen' => $idResumen,
                    'version' => $version,
                    'numero_resumen' => $numeroResumen,
                    'hash_validacion' => $hashValidacion,
                    'total_asistentes' => (int)($totales['asistentes'] ?? 0),
                    'total_votaciones' => (int)($totales['votaciones'] ?? 0),
                    'total_acuerdos' => (int)($totales['acuerdos'] ?? 0),
                    'usuario_generador' => trim((string)($usuario['nombre_completo'] ?? '')) ?: 'Sin registro',
                    'fecha_generacion' => $fechaGeneracion,
                    'nombre_archivo' => $archivoPdf['nombre_archivo'],
                    'path_archivo' => $archivoPdf['path_archivo'],
                ],
            ];
        } catch (Throwable $e) {
            error_log('[ResumenEjecutivoModelo] exception=' . $e->getMessage() . ' file=' . $e->getFile() . ' line=' . $e->getLine());
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            if ($rutaFisicaGenerada && is_file($rutaFisicaGenerada)) {
                @unlink($rutaFisicaGenerada);
            }

            return ['success' => false, 'mensaje' => 'No fue posible generar el resumen ejecutivo.'];
        }
    }

    private function construirSnapshot(int $idSesion, array $sesion, array $usuario, string $fechaGeneracion, int $version): array
    {
        $asistencia = class_exists('AsistenciaPleno')
            ? (new AsistenciaPleno($this->conn))->obtenerResumenSesion($idSesion)
            : ['total' => 0, 'presentes' => 0, 'ausentes' => 0, 'participantes' => []];
        $resultados = class_exists('ResumenPleno')
            ? (new ResumenPleno($this->conn))->obtenerResultadosPuntos($idSesion, $sesion)
            : [];
        $resultados = $this->agregarFechasVotacion($idSesion, $resultados);
        $acuerdos = class_exists('AcuerdoPleno')
            ? (new AcuerdoPleno($this->conn))->listarPorSesion($idSesion)
            : [];
        $certificado = class_exists('CertificadoAcuerdoPleno')
            ? (new CertificadoAcuerdoPleno($this->conn))->obtenerUltimoCertificado($idSesion)
            : null;

        $votaciones = array_values(array_filter($resultados, static function ($punto): bool {
            return !empty($punto['tuvo_votacion']);
        }));

        return [
            'datos_sesion' => $sesion,
            'asistencia' => $asistencia,
            'orden_dia' => $resultados,
            'votaciones' => $votaciones,
            'acuerdos' => $acuerdos,
            'documentos' => [
                'certificado_acuerdos' => $certificado,
            ],
            'usuario' => $usuario,
            'fecha_generacion' => $fechaGeneracion,
            'version' => $version,
            'totales' => [
                'asistentes' => (int)($asistencia['total'] ?? 0),
                'presentes' => (int)($asistencia['presentes'] ?? 0),
                'ausentes' => (int)($asistencia['ausentes'] ?? 0),
                'votaciones' => count($votaciones),
                'acuerdos' => count($acuerdos),
            ],
        ];
    }

    private function agregarFechasVotacion(int $idSesion, array $resultados): array
    {
        $fechas = [];
        try {
            $stmt = $this->conn->prepare(
                'SELECT punto_numero, fecha_inicio_votacion, fecha_cierre_votacion
                 FROM pleno_votacion_punto
                 WHERE id_sesion = :id_sesion'
            );
            $stmt->execute([':id_sesion' => $idSesion]);
            while ($row = $stmt->fetch()) {
                if (is_array($row)) {
                    $fechas[(string)$row['punto_numero']] = $row;
                }
            }
        } catch (Throwable $e) {
            return $resultados;
        }

        foreach ($resultados as &$punto) {
            $numero = (string)($punto['numero'] ?? '');
            $punto['fecha_inicio_votacion'] = $fechas[$numero]['fecha_inicio_votacion'] ?? null;
            $punto['fecha_cierre_votacion'] = $fechas[$numero]['fecha_cierre_votacion'] ?? null;
        }
        unset($punto);

        return $resultados;
    }

    private function obtenerSesion(int $idSesion, bool $bloquear = false): ?array
    {
        $sql = 'SELECT *
                FROM sesiones_plenarias
                WHERE id_sesion = :id_sesion
                LIMIT 1';
        if ($bloquear) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id_sesion' => $idSesion]);
        $sesion = $stmt->fetch();

        return is_array($sesion) ? $sesion : null;
    }

    private function obtenerUsuario(int $idUsuario): array
    {
        if ($idUsuario <= 0) {
            return ['id_usuario' => null, 'nombre_completo' => 'Sin registro'];
        }

        $stmt = $this->conn->prepare(
            "SELECT
                idUsuario AS id_usuario,
                TRIM(CONCAT(pNombre, ' ', COALESCE(NULLIF(sNombre, ''), ''), ' ', aPaterno, ' ', aMaterno)) AS nombre_completo
             FROM t_usuario
             WHERE idUsuario = :id_usuario
             LIMIT 1"
        );
        $stmt->execute([':id_usuario' => $idUsuario]);
        $usuario = $stmt->fetch();

        return is_array($usuario)
            ? $usuario
            : ['id_usuario' => $idUsuario, 'nombre_completo' => 'Usuario #' . $idUsuario];
    }

    private function calcularSiguienteVersion(int $idSesion): int
    {
        $stmt = $this->conn->prepare(
            'SELECT COALESCE(MAX(version), 0) + 1
             FROM pleno_resumenes_ejecutivos
             WHERE id_sesion = :id_sesion
             FOR UPDATE'
        );
        $stmt->execute([':id_sesion' => $idSesion]);

        return max(1, (int)$stmt->fetchColumn());
    }

    private function normalizarResumen(array $resumen): array
    {
        return [
            'id_resumen' => (int)($resumen['id_resumen'] ?? 0),
            'version' => (int)($resumen['version'] ?? 1),
            'numero_resumen' => (string)($resumen['numero_resumen'] ?? ''),
            'hash_validacion' => (string)($resumen['hash_validacion'] ?? ''),
            'fecha_generacion' => $resumen['fecha_generacion'] ?? null,
            'usuario_generador' => trim((string)($resumen['usuario_generador_nombre'] ?? '')) ?: (string)($resumen['usuario_generador'] ?? 'Sin registro'),
            'total_asistentes' => (int)($resumen['total_asistentes'] ?? 0),
            'total_votaciones' => (int)($resumen['total_votaciones'] ?? 0),
            'total_acuerdos' => (int)($resumen['total_acuerdos'] ?? 0),
            'raw' => $resumen,
        ];
    }

    private function asegurarTablaHistorial(): void
    {
        $this->conn->exec(
            "CREATE TABLE IF NOT EXISTS pleno_resumenes_ejecutivos (
                id_resumen INT AUTO_INCREMENT PRIMARY KEY,
                id_sesion INT NOT NULL,
                version INT NOT NULL DEFAULT 1,
                numero_resumen VARCHAR(100) NOT NULL,
                hash_validacion VARCHAR(64) NULL,
                total_asistentes INT NOT NULL DEFAULT 0,
                total_votaciones INT NOT NULL DEFAULT 0,
                total_acuerdos INT NOT NULL DEFAULT 0,
                usuario_generador INT NULL,
                fecha_generacion DATETIME NOT NULL,
                estado VARCHAR(30) NOT NULL DEFAULT 'vigente',
                snapshot_json LONGTEXT NULL,
                nombre_archivo VARCHAR(255) NULL,
                path_archivo VARCHAR(500) NULL,
                usuario_actualizador INT NULL,
                fecha_actualizacion DATETIME NULL,
                INDEX idx_resumen_sesion_estado (id_sesion, estado),
                INDEX idx_resumen_sesion_version (id_sesion, version)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }
}
