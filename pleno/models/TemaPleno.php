<?php

use App\Config\Database;

class TemaPleno
{
    private $conn;
    private $tieneColumnaSeccionOrden = null;

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

        $ordenPorSeccion = [
            'cuenta_gobernador' => 1,
            'cuenta_comisiones' => 1,
            'varios' => 1,
        ];

        foreach ($puntos as $punto) {
            $idPunto = (int)($punto['id'] ?? 0);
            $idComision = isset($punto['id_comision']) && $punto['id_comision'] !== '' ? (int)$punto['id_comision'] : null;
            $idTema = isset($punto['id_tema']) && $punto['id_tema'] !== '' ? (int)$punto['id_tema'] : null;
            $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));
            $tipoPunto = $tipoPunto !== '' ? $tipoPunto : 'COMISION';
            $nombreImprevista = trim((string)($punto['nombre_imprevista'] ?? ''));
            $observacionImprevista = trim((string)($punto['observacion_imprevista'] ?? ''));
            $tituloPunto = trim((string)($punto['titulo_punto'] ?? ''));
            $descripcionPunto = trim((string)($punto['descripcion_punto'] ?? ''));
            $seccionOrden = $this->normalizarSeccionOrden($punto['seccion_orden'] ?? 'varios');
            $orden = $ordenPorSeccion[$seccionOrden];

            if ($tipoPunto === 'IMPREVISTA' && $nombreImprevista === '') {
                continue;
            }

            if ($tipoPunto === 'TABLA' && $tituloPunto === '') {
                continue;
            }

            if (!in_array($tipoPunto, ['IMPREVISTA', 'TABLA'], true) && (($idComision ?? 0) <= 0)) {
                continue;
            }

            if ($idPunto > 0 && isset($existentesPorId[$idPunto])) {
                $asignacionSeccion = $this->tieneColumnaSeccionOrden() ? 'seccion_orden = :seccion_orden,' : '';
                $stmt = $this->conn->prepare(
                    'UPDATE sesion_plenaria_temas
                     SET id_comision = :id_comision,
                         id_tema = :id_tema,
                         tipo_punto = :tipo_punto,
                         nombre_imprevista = :nombre_imprevista,
                         observacion_imprevista = :observacion_imprevista,
                         titulo_punto = :titulo_punto,
                         descripcion_punto = :descripcion_punto,
                         ' . $asignacionSeccion . '
                         orden = :orden,
                         vigente = 1,
                         fecha_actualizacion = NOW()
                     WHERE id = :id AND id_sesion = :id_sesion'
                );
                $params = [
                    ':id' => $idPunto,
                    ':id_sesion' => $idSesion,
                    ':id_comision' => $idComision,
                    ':id_tema' => $idTema,
                    ':tipo_punto' => $tipoPunto,
                    ':nombre_imprevista' => $nombreImprevista !== '' ? $nombreImprevista : null,
                    ':observacion_imprevista' => $observacionImprevista !== '' ? $observacionImprevista : null,
                    ':titulo_punto' => $tituloPunto !== '' ? $tituloPunto : null,
                    ':descripcion_punto' => $descripcionPunto !== '' ? $descripcionPunto : null,
                    ':orden' => $orden,
                ];

                if ($this->tieneColumnaSeccionOrden()) {
                    $params[':seccion_orden'] = $seccionOrden;
                }

                $stmt->execute($params);
            } else {
                $columnasSeccion = $this->tieneColumnaSeccionOrden() ? ', seccion_orden' : '';
                $valoresSeccion = $this->tieneColumnaSeccionOrden() ? ', :seccion_orden' : '';
                $stmt = $this->conn->prepare(
                    'INSERT INTO sesion_plenaria_temas
                        (id_sesion, id_comision, id_tema, tipo_punto, nombre_imprevista, observacion_imprevista, titulo_punto, descripcion_punto, orden' . $columnasSeccion . ', vigente)
                     VALUES
                        (:id_sesion, :id_comision, :id_tema, :tipo_punto, :nombre_imprevista, :observacion_imprevista, :titulo_punto, :descripcion_punto, :orden' . $valoresSeccion . ', 1)'
                );
                $params = [
                    ':id_sesion' => $idSesion,
                    ':id_comision' => $idComision,
                    ':id_tema' => $idTema,
                    ':tipo_punto' => $tipoPunto,
                    ':nombre_imprevista' => $nombreImprevista !== '' ? $nombreImprevista : null,
                    ':observacion_imprevista' => $observacionImprevista !== '' ? $observacionImprevista : null,
                    ':titulo_punto' => $tituloPunto !== '' ? $tituloPunto : null,
                    ':descripcion_punto' => $descripcionPunto !== '' ? $descripcionPunto : null,
                    ':orden' => $orden,
                ];

                if ($this->tieneColumnaSeccionOrden()) {
                    $params[':seccion_orden'] = $seccionOrden;
                }

                $stmt->execute($params);
            }

            $ordenPorSeccion[$seccionOrden] += 1;
        }
    }

    public function listarPuntosSesion(int $idSesion): array
    {
        $selectSeccion = $this->tieneColumnaSeccionOrden() ? "COALESCE(NULLIF(TRIM(spt.seccion_orden), ''), 'varios') AS seccion_orden," : "'varios' AS seccion_orden,";
        $orderBySeccion = $this->tieneColumnaSeccionOrden() ? "COALESCE(NULLIF(TRIM(spt.seccion_orden), ''), 'varios')," : '';

        $stmt = $this->conn->prepare(
            'SELECT
                spt.id,
                spt.id_sesion,
                spt.id_comision,
                spt.id_tema,
                spt.tipo_punto,
                spt.nombre_imprevista,
                spt.observacion_imprevista,
                spt.titulo_punto,
                spt.descripcion_punto,
                spt.orden,
                ' . $selectSeccion . '
                c.nombreComision,
                t.nombreTema
             FROM sesion_plenaria_temas spt
             LEFT JOIN t_comision c ON c.idComision = spt.id_comision
             LEFT JOIN t_tema t ON t.idTema = spt.id_tema
             WHERE spt.id_sesion = :id_sesion
               AND spt.vigente = 1
             ORDER BY ' . $orderBySeccion . ' spt.orden ASC, spt.id ASC'
        );
        $stmt->execute([':id_sesion' => $idSesion]);

        return $stmt->fetchAll();
    }

    public function actualizarOrdenPuntosSesion(int $idSesion, array $ordenPuntos): bool
    {
        if ($idSesion <= 0 || empty($ordenPuntos) || !$this->tieneColumnaSeccionOrden()) {
            return false;
        }

        $itemsNormalizados = [];
        $idsRecibidos = [];
        $ordenesPorSeccion = [
            'cuenta_gobernador' => [],
            'cuenta_comisiones' => [],
            'varios' => [],
        ];

        foreach ($ordenPuntos as $item) {
            if (!is_array($item)) {
                return false;
            }

            $idPunto = (int)($item['id'] ?? 0);
            $orden = (int)($item['orden'] ?? 0);
            $seccionOrden = $this->normalizarSeccionOrden($item['seccion_orden'] ?? 'varios');

            if ($idPunto <= 0 || $orden <= 0) {
                return false;
            }

            if (isset($idsRecibidos[$idPunto]) || isset($ordenesPorSeccion[$seccionOrden][$orden])) {
                return false;
            }

            $idsRecibidos[$idPunto] = true;
            $ordenesPorSeccion[$seccionOrden][$orden] = true;
            $itemsNormalizados[] = [
                'id' => $idPunto,
                'orden' => $orden,
                'seccion_orden' => $seccionOrden,
            ];
        }

        $idPlaceholders = [];
        $params = [
            ':id_sesion' => $idSesion,
        ];

        foreach ($itemsNormalizados as $index => $itemNormalizado) {
            $placeholder = ':id_' . $index;
            $idPlaceholders[] = $placeholder;
            $params[$placeholder] = $itemNormalizado['id'];
        }

        $stmt = $this->conn->prepare(
            'SELECT id
             FROM sesion_plenaria_temas
             WHERE id_sesion = :id_sesion
               AND vigente = 1
               AND id IN (' . implode(', ', $idPlaceholders) . ')'
        );
        $stmt->execute($params);
        $puntosActivos = $stmt->fetchAll();

        if (count($puntosActivos) !== count($itemsNormalizados)) {
            return false;
        }

        foreach ($ordenesPorSeccion as $ordenesRecibidos) {
            if (empty($ordenesRecibidos)) {
                continue;
            }

            $ordenesEsperados = range(1, count($ordenesRecibidos));
            $ordenesNormalizados = array_map('intval', array_keys($ordenesRecibidos));
            sort($ordenesNormalizados);

            if ($ordenesNormalizados !== $ordenesEsperados) {
                return false;
            }
        }

        $stmtActualizar = $this->conn->prepare(
            'UPDATE sesion_plenaria_temas
             SET orden = :orden,
                 seccion_orden = :seccion_orden,
                 fecha_actualizacion = NOW()
             WHERE id = :id
               AND id_sesion = :id_sesion
               AND vigente = 1'
        );

        try {
            $this->conn->beginTransaction();

            foreach ($itemsNormalizados as $itemNormalizado) {
                $params = [
                    ':orden' => $itemNormalizado['orden'],
                    ':id' => $itemNormalizado['id'],
                    ':id_sesion' => $idSesion,
                ];

                $params[':seccion_orden'] = $itemNormalizado['seccion_orden'];

                $stmtActualizar->execute($params);
            }

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            return false;
        }
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

    private function normalizarSeccionOrden($seccion): string
    {
        $seccion = trim((string)$seccion);
        $seccionesPermitidas = ['cuenta_gobernador', 'cuenta_comisiones', 'varios'];

        if ($seccion === 'cuenta_intendente') {
            return 'cuenta_gobernador';
        }

        return in_array($seccion, $seccionesPermitidas, true) ? $seccion : 'varios';
    }

    private function tieneColumnaSeccionOrden(): bool
    {
        if ($this->tieneColumnaSeccionOrden !== null) {
            return $this->tieneColumnaSeccionOrden;
        }

        try {
            $stmt = $this->conn->query("SHOW COLUMNS FROM sesion_plenaria_temas LIKE 'seccion_orden'");
            $this->tieneColumnaSeccionOrden = $stmt && $stmt->fetch() ? true : false;
        } catch (\Throwable $e) {
            $this->tieneColumnaSeccionOrden = false;
        }

        return $this->tieneColumnaSeccionOrden;
    }
}
