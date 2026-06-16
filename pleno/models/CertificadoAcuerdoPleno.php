<?php

use App\Config\Database;

class CertificadoAcuerdoPleno
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

    public function contarAcuerdosVigentes(int $idSesion): int
    {
        if ($idSesion <= 0) {
            return 0;
        }

        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total
             FROM pleno_acuerdos
             WHERE id_sesion = :id_sesion
               AND estado = 'vigente'"
        );
        $stmt->execute([':id_sesion' => $idSesion]);

        return (int)$stmt->fetchColumn();
    }

    public function existenAcuerdos(int $idSesion): bool
    {
        return $this->contarAcuerdosVigentes($idSesion) > 0;
    }

    public function obtenerUltimoCertificado(int $idSesion): ?array
    {
        if ($idSesion <= 0 || !$this->tablaExiste('pleno_certificados_acuerdos')) {
            return null;
        }

        $columnas = $this->obtenerColumnas('pleno_certificados_acuerdos');
        if (!isset($columnas['id_sesion'])) {
            return null;
        }

        $usuarioColumna = $this->primeraColumnaDisponible($columnas, [
            'usuario_generador',
            'usuario_creador',
            'id_usuario_generador',
            'id_usuario',
        ]);

        $select = 'SELECT c.*';
        $join = '';
        if ($usuarioColumna !== null) {
            $select .= ", TRIM(CONCAT(u.pNombre, ' ', COALESCE(NULLIF(u.sNombre, ''), ''), ' ', u.aPaterno, ' ', u.aMaterno)) AS usuario_generador_nombre";
            $join = " LEFT JOIN t_usuario u ON u.idUsuario = c.$usuarioColumna";
        }

        $where = 'WHERE c.id_sesion = :id_sesion';
        if (isset($columnas['estado'])) {
            $where .= " AND c.estado IN ('activo', 'vigente', 'generado')";
        } elseif (isset($columnas['activo'])) {
            $where .= ' AND c.activo = 1';
        } elseif (isset($columnas['vigente'])) {
            $where .= ' AND c.vigente = 1';
        }

        $orderParts = [];
        foreach (['fecha_generacion', 'fecha_creacion', 'created_at'] as $columnaFecha) {
            if (isset($columnas[$columnaFecha])) {
                $orderParts[] = "c.$columnaFecha DESC";
                break;
            }
        }
        foreach (['version', 'numero_version', 'id_certificado'] as $columnaOrden) {
            if (isset($columnas[$columnaOrden])) {
                $orderParts[] = "c.$columnaOrden DESC";
                break;
            }
        }
        if (empty($orderParts)) {
            $orderParts[] = 'c.id_sesion DESC';
        }

        $stmt = $this->conn->prepare(
            "$select
             FROM pleno_certificados_acuerdos c
             $join
             $where
             ORDER BY " . implode(', ', $orderParts) . '
             LIMIT 1'
        );
        $stmt->execute([':id_sesion' => $idSesion]);
        $certificado = $stmt->fetch();

        return is_array($certificado) ? $this->normalizarCertificado($certificado, $idSesion) : null;
    }

    public function existeCertificado(int $idSesion): bool
    {
        return $this->obtenerUltimoCertificado($idSesion) !== null;
    }

    public function listarHistorial(array $filtros = []): array
    {
        if (!$this->tablaExiste('pleno_certificados_acuerdos') || !$this->tablaExiste('sesiones_plenarias')) {
            return [];
        }

        $numeroSesion = trim((string)($filtros['numero_sesion'] ?? ''));
        $fechaFiltro = trim((string)($filtros['fecha'] ?? ''));
        $estado = strtolower(trim((string)($filtros['estado'] ?? 'todos')));
        $numeroCertificado = trim((string)($filtros['numero_certificado'] ?? ''));
        $joinUsuario = $this->tablaExiste('t_usuario');

        $selectUsuario = $joinUsuario
            ? ", TRIM(CONCAT(u.pNombre, ' ', COALESCE(NULLIF(u.sNombre, ''), ''), ' ', u.aPaterno, ' ', u.aMaterno)) AS usuario_generador_nombre"
            : ", NULL AS usuario_generador_nombre";
        $joinUsuarioSql = $joinUsuario
            ? ' LEFT JOIN t_usuario u ON u.idUsuario = c.usuario_generador'
            : '';

        $sql = "SELECT
                    c.id_certificado,
                    c.id_sesion,
                    c.version,
                    c.numero_certificado,
                    c.total_acuerdos,
                    c.usuario_generador,
                    c.fecha_generacion,
                    c.estado,
                    c.nombre_archivo,
                    c.path_archivo,
                    s.numero_sesion,
                    s.tipo_pleno,
                    s.fecha AS fecha_sesion
                    $selectUsuario
                FROM pleno_certificados_acuerdos c
                INNER JOIN sesiones_plenarias s ON s.id_sesion = c.id_sesion
                $joinUsuarioSql
                WHERE 1 = 1";
        $params = [];

        if ($numeroSesion !== '') {
            $sql .= ' AND s.numero_sesion LIKE :numero_sesion';
            $params[':numero_sesion'] = '%' . $numeroSesion . '%';
        }

        if ($numeroCertificado !== '') {
            $sql .= ' AND c.numero_certificado LIKE :numero_certificado';
            $params[':numero_certificado'] = '%' . $numeroCertificado . '%';
        }

        if ($fechaFiltro !== '') {
            $fechaNormalizada = $this->normalizarFechaBusqueda($fechaFiltro);
            if ($fechaNormalizada !== '') {
                $sql .= ' AND s.fecha = :fecha';
                $params[':fecha'] = $fechaNormalizada;
            }
        }

        if ($estado !== '' && $estado !== 'todos') {
            $sql .= ' AND LOWER(TRIM(c.estado)) = :estado';
            $params[':estado'] = $estado;
        }

        $sql .= ' ORDER BY c.fecha_generacion DESC, c.id_certificado DESC';

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }

        return is_array($rows) ? $rows : [];
    }

    public function generarCertificado(int $idSesion, int $idUsuario): array
    {
        error_log('[CertificadoModelo] generarCertificado id_sesion=' . $idSesion . ' id_usuario=' . $idUsuario);

        if ($idSesion <= 0) {
            error_log('[CertificadoModelo] id_sesion_invalido');
            return ['success' => false, 'mensaje' => 'No se pudo identificar la sesion plenaria.'];
        }

        if (!$this->tablaExiste('pleno_certificados_acuerdos')) {
            error_log('[CertificadoModelo] tabla pleno_certificados_acuerdos no disponible');
            return ['success' => false, 'mensaje' => 'La tabla de certificados no esta disponible.'];
        }

        $rutaFisicaGenerada = null;

        try {
            $this->conn->beginTransaction();
            error_log('[CertificadoModelo] transaccion_iniciada');

            $sesion = $this->obtenerSesionParaCertificado($idSesion, true);
            if (!$sesion) {
                error_log('[CertificadoModelo] sesion_no_encontrada id_sesion=' . $idSesion);
                $this->conn->rollBack();
                return ['success' => false, 'mensaje' => 'La sesion plenaria no existe.'];
            }

            $acuerdos = $this->listarAcuerdosVigentes($idSesion);
            $totalAcuerdos = count($acuerdos);
            error_log('[CertificadoModelo] total_acuerdos=' . $totalAcuerdos);
            if ($totalAcuerdos === 0) {
                $this->conn->rollBack();
                return ['success' => false, 'mensaje' => 'No existen acuerdos para certificar.'];
            }

            $version = $this->calcularSiguienteVersion($idSesion);
            $numeroCertificado = 'CERT-' . trim((string)$sesion['numero_sesion']) . '-V' . $version;
            $usuario = $this->obtenerUsuario($idUsuario);
            $fechaGeneracion = date('Y-m-d H:i:s');
            error_log('[CertificadoModelo] version=' . $version . ' numero_certificado=' . $numeroCertificado . ' fecha_generacion=' . $fechaGeneracion);

            $snapshot = [
                'datos_sesion' => $sesion,
                'acuerdos' => $acuerdos,
                'usuario' => $usuario,
                'fecha_generacion' => $fechaGeneracion,
                'version' => $version,
            ];
            $snapshotJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($snapshotJson === false) {
                $this->conn->rollBack();
                return ['success' => false, 'mensaje' => 'No fue posible preparar el snapshot del certificado.'];
            }

            $hashValidacion = hash('sha256', $snapshotJson);
            error_log('[CertificadoModelo] snapshot_length=' . strlen($snapshotJson) . ' hash=' . $hashValidacion);

            $stmt = $this->conn->prepare(
                "INSERT INTO pleno_certificados_acuerdos
                    (id_sesion, version, numero_certificado, hash_validacion, total_acuerdos, usuario_generador, fecha_generacion, estado, snapshot_json, nombre_archivo, path_archivo)
                 VALUES
                    (:id_sesion, :version, :numero_certificado, :hash_validacion, :total_acuerdos, :usuario_generador, :fecha_generacion, 'vigente', :snapshot_json, NULL, NULL)"
            );
            $stmt->execute([
                ':id_sesion' => $idSesion,
                ':version' => $version,
                ':numero_certificado' => $numeroCertificado,
                ':hash_validacion' => $hashValidacion,
                ':total_acuerdos' => $totalAcuerdos,
                ':usuario_generador' => $idUsuario > 0 ? $idUsuario : null,
                ':fecha_generacion' => $fechaGeneracion,
                ':snapshot_json' => $snapshotJson,
            ]);

            $idCertificado = (int)$this->conn->lastInsertId();
            error_log('[CertificadoModelo] insert_ok id_certificado=' . $idCertificado);
            $certificadoPdf = [
                'id_certificado' => $idCertificado,
                'version' => $version,
                'numero_certificado' => $numeroCertificado,
                'hash_validacion' => $hashValidacion,
                'total_acuerdos' => $totalAcuerdos,
                'usuario_generador' => trim((string)($usuario['nombre_completo'] ?? '')) ?: 'Sin registro',
                'fecha_generacion' => $fechaGeneracion,
            ];

            if (!class_exists('CertificadoAcuerdoPdfService')) {
                require_once dirname(__DIR__) . '/services/CertificadoAcuerdoPdfService.php';
            }

            $archivoPdf = (new CertificadoAcuerdoPdfService())->generar($snapshot, $certificadoPdf);
            $rutaFisicaGenerada = $archivoPdf['ruta_fisica'] ?? null;
            error_log('[CertificadoModelo] pdf_service_result=' . json_encode($archivoPdf, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            error_log('[CertificadoModelo] pdf_file_exists_model=' . ($rutaFisicaGenerada && file_exists($rutaFisicaGenerada) ? 'true' : 'false'));

            $stmtArchivo = $this->conn->prepare(
                'UPDATE pleno_certificados_acuerdos
                 SET nombre_archivo = :nombre_archivo,
                     path_archivo = :path_archivo
                 WHERE id_certificado = :id_certificado'
            );
            $stmtArchivo->execute([
                ':nombre_archivo' => $archivoPdf['nombre_archivo'],
                ':path_archivo' => $archivoPdf['path_archivo'],
                ':id_certificado' => $idCertificado,
            ]);
            error_log('[CertificadoModelo] update_archivo_rowcount=' . $stmtArchivo->rowCount() . ' id_certificado=' . $idCertificado);

            $this->conn->commit();
            error_log('[CertificadoModelo] commit_ok id_certificado=' . $idCertificado);

            return [
                'success' => true,
                'mensaje' => 'Certificado registrado correctamente.',
                'certificado' => [
                    'id_certificado' => $idCertificado,
                    'version' => $version,
                    'numero_certificado' => $numeroCertificado,
                    'hash_validacion' => $hashValidacion,
                    'total_acuerdos' => $totalAcuerdos,
                    'usuario_generador' => trim((string)($usuario['nombre_completo'] ?? '')) ?: 'Sin registro',
                    'fecha_generacion' => $fechaGeneracion,
                    'nombre_archivo' => $archivoPdf['nombre_archivo'],
                    'path_archivo' => $archivoPdf['path_archivo'],
                ],
            ];
        } catch (Throwable $e) {
            error_log('[CertificadoModelo] exception=' . $e->getMessage() . ' file=' . $e->getFile() . ' line=' . $e->getLine());
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
                error_log('[CertificadoModelo] rollback_ok');
            }

            if ($rutaFisicaGenerada && is_file($rutaFisicaGenerada)) {
                $unlinkResult = @unlink($rutaFisicaGenerada);
                error_log('[CertificadoModelo] unlink_pdf_after_rollback path=' . $rutaFisicaGenerada . ' result=' . ($unlinkResult ? 'true' : 'false'));
            }

            return ['success' => false, 'mensaje' => 'No fue posible registrar el certificado.'];
        }
    }

    private function normalizarCertificado(array $certificado, int $idSesion): array
    {
        return [
            'version' => $this->primerValorDisponible($certificado, ['version', 'numero_version', 'nro_version', 'version_certificado']) ?: '1',
            'fecha_generacion' => $this->primerValorDisponible($certificado, ['fecha_generacion', 'fecha_creacion', 'created_at']),
            'usuario_generador' => trim((string)($certificado['usuario_generador_nombre'] ?? '')) ?: (string)($this->primerValorDisponible($certificado, ['usuario_generador', 'usuario_creador', 'id_usuario_generador', 'id_usuario']) ?: 'Sin registro'),
            'total_acuerdos' => (int)($this->primerValorDisponible($certificado, ['total_acuerdos', 'cantidad_acuerdos']) ?: $this->contarAcuerdosVigentes($idSesion)),
            'raw' => $certificado,
        ];
    }

    private function calcularSiguienteVersion(int $idSesion): int
    {
        $stmt = $this->conn->prepare(
            'SELECT COALESCE(MAX(version), 0) + 1
             FROM pleno_certificados_acuerdos
             WHERE id_sesion = :id_sesion
             FOR UPDATE'
        );
        $stmt->execute([':id_sesion' => $idSesion]);

        return max(1, (int)$stmt->fetchColumn());
    }

    private function normalizarFechaBusqueda(string $busqueda): string
    {
        $busqueda = trim($busqueda);

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $busqueda, $matches) === 1) {
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $busqueda, $matches) === 1) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        return '';
    }

    private function obtenerSesionParaCertificado(int $idSesion, bool $bloquear = false): ?array
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

    private function listarAcuerdosVigentes(int $idSesion): array
    {
        $stmt = $this->conn->prepare(
            'SELECT *
             FROM pleno_acuerdos
             WHERE id_sesion = :id_sesion
               AND estado = :estado
             ORDER BY fecha_creacion ASC, id_acuerdo ASC'
        );
        $stmt->execute([
            ':id_sesion' => $idSesion,
            ':estado' => 'vigente',
        ]);
        $acuerdos = $stmt->fetchAll();

        return is_array($acuerdos) ? $acuerdos : [];
    }

    private function obtenerUsuario(int $idUsuario): array
    {
        if ($idUsuario <= 0) {
            return [
                'id_usuario' => null,
                'nombre_completo' => 'Sin registro',
            ];
        }

        $stmt = $this->conn->prepare(
            "SELECT
                idUsuario AS id_usuario,
                pNombre,
                sNombre,
                aPaterno,
                aMaterno,
                TRIM(CONCAT(pNombre, ' ', COALESCE(NULLIF(sNombre, ''), ''), ' ', aPaterno, ' ', aMaterno)) AS nombre_completo
             FROM t_usuario
             WHERE idUsuario = :id_usuario
             LIMIT 1"
        );
        $stmt->execute([':id_usuario' => $idUsuario]);
        $usuario = $stmt->fetch();

        if (!is_array($usuario)) {
            return [
                'id_usuario' => $idUsuario,
                'nombre_completo' => 'Usuario #' . $idUsuario,
            ];
        }

        return $usuario;
    }

    private function tablaExiste(string $tabla): bool
    {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*)
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :tabla'
        );
        $stmt->execute([':tabla' => $tabla]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function obtenerColumnas(string $tabla): array
    {
        $stmt = $this->conn->prepare(
            'SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :tabla'
        );
        $stmt->execute([':tabla' => $tabla]);

        $columnas = [];
        foreach ($stmt->fetchAll() as $row) {
            $columnas[(string)$row['COLUMN_NAME']] = true;
        }

        return $columnas;
    }

    private function primeraColumnaDisponible(array $columnas, array $candidatas): ?string
    {
        foreach ($candidatas as $columna) {
            if (isset($columnas[$columna])) {
                return $columna;
            }
        }

        return null;
    }

    private function primerValorDisponible(array $fila, array $candidatas)
    {
        foreach ($candidatas as $columna) {
            if (array_key_exists($columna, $fila) && $fila[$columna] !== null && $fila[$columna] !== '') {
                return $fila[$columna];
            }
        }

        return null;
    }
}
