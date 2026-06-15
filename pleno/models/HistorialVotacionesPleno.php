<?php

use App\Config\Database;

class HistorialVotacionesPleno
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

    public function listar(array $filtros = []): array
    {
        $busqueda = trim((string)($filtros['q'] ?? ''));
        $numeroSesion = trim((string)($filtros['numero_sesion'] ?? ''));
        $fechaFiltro = trim((string)($filtros['fecha'] ?? ''));
        $tipoPlenoFiltro = strtolower(trim((string)($filtros['tipo_pleno'] ?? 'todas')));
        $resultadoFiltro = trim((string)($filtros['resultado'] ?? 'todas'));

        $sql = "SELECT
                    p.id,
                    p.id_sesion,
                    p.punto_numero,
                    p.estado_votacion,
                    p.fecha_inicio_votacion,
                    p.fecha_cierre_votacion,
                    s.numero_sesion,
                    s.tipo_pleno,
                    s.fecha,
                    s.hora,
                    s.estado AS estado_sesion
                FROM pleno_votacion_punto p
                INNER JOIN sesiones_plenarias s ON s.id_sesion = p.id_sesion
                WHERE p.estado_votacion = 'votacion_cerrada'";
        $params = [];

        if ($numeroSesion !== '') {
            $sql .= ' AND s.numero_sesion LIKE :numero_sesion';
            $params[':numero_sesion'] = '%' . $numeroSesion . '%';
        }

        if ($fechaFiltro !== '') {
            $fechaNormalizada = $this->normalizarFechaBusqueda($fechaFiltro);
            if ($fechaNormalizada !== '') {
                $sql .= ' AND s.fecha = :fecha';
                $params[':fecha'] = $fechaNormalizada;
            } else {
                $sql .= " AND (DATE_FORMAT(s.fecha, '%d/%m/%Y') LIKE :fecha_texto OR s.fecha LIKE :fecha_texto)";
                $params[':fecha_texto'] = '%' . $fechaFiltro . '%';
            }
        }

        if (in_array($tipoPlenoFiltro, ['ordinario', 'extraordinario'], true)) {
            $sql .= ' AND LOWER(TRIM(s.tipo_pleno)) = :tipo_pleno';
            $params[':tipo_pleno'] = $tipoPlenoFiltro;
        }

        if ($busqueda !== '') {
            $fechaBusqueda = $this->normalizarFechaBusqueda($busqueda);
            $sql .= " AND (
                s.numero_sesion LIKE :busqueda
                OR s.tipo_pleno LIKE :busqueda
                OR DATE_FORMAT(s.fecha, '%d/%m/%Y') LIKE :busqueda
                OR s.fecha LIKE :busqueda
                " . ($fechaBusqueda !== '' ? ' OR s.fecha = :fecha_busqueda' : '') . '
            )';
            $params[':busqueda'] = '%' . $busqueda . '%';
            if ($fechaBusqueda !== '') {
                $params[':fecha_busqueda'] = $fechaBusqueda;
            }
        }

        $sql .= ' ORDER BY s.fecha DESC, s.hora DESC, p.fecha_cierre_votacion DESC, p.id DESC';

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }

        if (!is_array($rows)) {
            return [];
        }

        $historial = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $idSesion = (int)($row['id_sesion'] ?? 0);
            $puntoNumero = trim((string)($row['punto_numero'] ?? ''));
            $idVotacion = $this->obtenerIdVotacion($idSesion, $puntoNumero);
            $conteo = $idVotacion > 0 ? $this->contarVotos($idVotacion) : ['SI' => 0, 'NO' => 0, 'ABSTENCION' => 0];
            $total = (int)$conteo['SI'] + (int)$conteo['NO'] + (int)$conteo['ABSTENCION'];
            $resultado = $this->resolverResultado((int)$conteo['SI'], (int)$conteo['NO'], $total);

            if (!$this->coincideResultado($resultado, $resultadoFiltro)) {
                continue;
            }

            $historial[] = [
                'id' => (int)($row['id'] ?? 0),
                'id_sesion' => $idSesion,
                'id_votacion' => $idVotacion,
                'fecha' => $row['fecha'] ?? null,
                'hora' => $row['hora'] ?? null,
                'numero_sesion' => $row['numero_sesion'] ?? '',
                'tipo_pleno' => $row['tipo_pleno'] ?? '',
                'punto_numero' => $puntoNumero,
                'punto_titulo' => $this->resolverTituloPunto($idSesion, $puntoNumero),
                'fecha_inicio_votacion' => $row['fecha_inicio_votacion'] ?? null,
                'fecha_cierre_votacion' => $row['fecha_cierre_votacion'] ?? null,
                'resultado' => $resultado,
                'si' => (int)$conteo['SI'],
                'no' => (int)$conteo['NO'],
                'abstencion' => (int)$conteo['ABSTENCION'],
                'total' => $total,
                'votos' => $idVotacion > 0 ? $this->listarVotos($idVotacion) : [],
            ];
        }

        return $historial;
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

    private function obtenerIdVotacion(int $idSesion, string $puntoNumero): int
    {
        try {
            $stmt = $this->conn->prepare(
                'SELECT idVotacion
                 FROM t_votacion
                 WHERE nombreVotacion = :nombre
                 ORDER BY idVotacion DESC
                 LIMIT 1'
            );
            $stmt->execute([':nombre' => 'Pleno ' . $idSesion . ' - Punto ' . trim($puntoNumero)]);

            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    private function contarVotos(int $idVotacion): array
    {
        $conteo = ['SI' => 0, 'NO' => 0, 'ABSTENCION' => 0];

        try {
            $stmt = $this->conn->prepare(
                'SELECT UPPER(TRIM(opcionVoto)) AS opcion, COUNT(*) AS total
                 FROM t_voto
                 WHERE t_votacion_idVotacion = :id_votacion
                 GROUP BY UPPER(TRIM(opcionVoto))'
            );
            $stmt->execute([':id_votacion' => $idVotacion]);
        } catch (Throwable $e) {
            return $conteo;
        }

        while ($row = $stmt->fetch()) {
            if (!is_array($row)) {
                continue;
            }

            $opcion = (string)($row['opcion'] ?? '');
            if (isset($conteo[$opcion])) {
                $conteo[$opcion] = (int)($row['total'] ?? 0);
            }
        }

        return $conteo;
    }

    private function listarVotos(int $idVotacion): array
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT
                    TRIM(CONCAT(u.pNombre, ' ', COALESCE(NULLIF(u.sNombre, ''), ''), ' ', u.aPaterno, ' ', u.aMaterno)) AS consejero,
                    v.opcionVoto,
                    COALESCE(v.fechaHoraVoto, v.fechaVoto) AS hora
                 FROM t_voto v
                 INNER JOIN t_usuario u ON u.idUsuario = v.t_usuario_idUsuario
                 WHERE v.t_votacion_idVotacion = :id_votacion
                 ORDER BY COALESCE(v.fechaHoraVoto, v.fechaVoto) ASC, u.aPaterno ASC, u.aMaterno ASC, u.pNombre ASC"
            );
            $stmt->execute([':id_votacion' => $idVotacion]);
            $votos = $stmt->fetchAll();
        } catch (Throwable $e) {
            return [];
        }

        return is_array($votos) ? $votos : [];
    }

    private function resolverResultado(int $si, int $no, int $total): string
    {
        if ($total === 0) {
            return 'Sin votos';
        }

        if ($si > $no) {
            return 'Aprobado';
        }

        if ($no > $si) {
            return 'Rechazado';
        }

        return 'Empate';
    }

    private function coincideResultado(string $resultado, string $filtro): bool
    {
        $mapa = [
            'aprobadas' => 'Aprobado',
            'rechazadas' => 'Rechazado',
            'empatadas' => 'Empate',
            'sin_votos' => 'Sin votos',
        ];

        return $filtro === '' || $filtro === 'todas' || (($mapa[$filtro] ?? null) === $resultado);
    }

    private function resolverTituloPunto(int $idSesion, string $puntoNumero): string
    {
        if ($puntoNumero === '1') {
            try {
                $stmt = $this->conn->prepare('SELECT aprobacion_actas FROM sesiones_plenarias WHERE id_sesion = :id LIMIT 1');
                $stmt->execute([':id' => $idSesion]);
                $acta = trim((string)$stmt->fetchColumn());
            } catch (Throwable $e) {
                $acta = '';
            }

            return 'Aprobación de Acta' . ($acta !== '' ? ': ' . $acta : '');
        }

        if ($puntoNumero === '2') {
            return 'Cuenta Presidente del Consejo Regional';
        }

        if ($puntoNumero === '3') {
            return 'Cuenta Comisiones';
        }

        if ($puntoNumero === '4') {
            return 'Varios';
        }

        if (preg_match('/^3\.(\d+)$/', $puntoNumero, $matches) === 1) {
            return $this->resolverTituloSubpunto($idSesion, 'cuenta_comisiones', (int)$matches[1]);
        }

        if (preg_match('/^4\.(\d+)$/', $puntoNumero, $matches) === 1) {
            return $this->resolverTituloSubpunto($idSesion, 'varios', (int)$matches[1]);
        }

        return 'Punto ' . $puntoNumero;
    }

    private function resolverTituloSubpunto(int $idSesion, string $seccion, int $orden): string
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT
                    spt.tipo_punto,
                    spt.nombre_imprevista,
                    spt.titulo_punto,
                    c.nombreComision,
                    t.nombreTema
                 FROM sesion_plenaria_temas spt
                 LEFT JOIN t_comision c ON c.idComision = spt.id_comision
                 LEFT JOIN t_tema t ON t.idTema = spt.id_tema
                 WHERE spt.id_sesion = :id_sesion
                   AND spt.vigente = 1
                   AND COALESCE(NULLIF(TRIM(spt.seccion_orden), ''), 'varios') = :seccion
                 ORDER BY spt.orden ASC, spt.id ASC
                 LIMIT 1 OFFSET " . max(0, $orden - 1)
            );
            $stmt->execute([
                ':id_sesion' => $idSesion,
                ':seccion' => $seccion,
            ]);
            $row = $stmt->fetch();
        } catch (Throwable $e) {
            $row = null;
        }

        if (!is_array($row)) {
            return 'Punto ' . ($seccion === 'varios' ? '4.' : '3.') . $orden;
        }

        $tipoPunto = strtoupper(trim((string)($row['tipo_punto'] ?? 'COMISION')));
        if ($tipoPunto === 'TABLA') {
            return trim((string)($row['titulo_punto'] ?? '')) ?: 'Punto de tabla';
        }

        if ($tipoPunto === 'IMPREVISTA') {
            return trim((string)($row['nombre_imprevista'] ?? '')) ?: 'Comisión imprevista';
        }

        $comision = trim((string)($row['nombreComision'] ?? ''));
        $tema = trim((string)($row['nombreTema'] ?? ''));

        if ($comision !== '' && $tema !== '') {
            return $comision . ' - ' . $tema;
        }

        return $tema !== '' ? $tema : ($comision !== '' ? $comision : 'Punto de comisión');
    }
}
