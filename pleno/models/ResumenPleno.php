<?php

use App\Config\Database;

class ResumenPleno
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

    public function obtenerResultadosPuntos(int $idSesion, array $sesion): array
    {
        if ($idSesion <= 0) {
            return [];
        }

        $puntos = $this->armarPuntosOrdenDia($idSesion, $sesion);
        $votaciones = $this->obtenerEstadosVotacion($idSesion);
        $resultados = [];

        foreach ($puntos as $punto) {
            $numero = (string)$punto['numero'];
            $estadoVotacion = $votaciones[$numero]['estado_votacion'] ?? 'no_vota';
            $esInformativo = !plenoPuntoPermiteVotacion($numero);
            $idVotacion = $this->obtenerIdVotacion($idSesion, $numero);
            $conteo = ['SI' => 0, 'NO' => 0, 'ABSTENCION' => 0];
            $votos = [];

            if ($idVotacion > 0) {
                $conteo = $this->contarVotos($idVotacion);
                $votos = $this->listarVotos($idVotacion);
            }

            $total = (int)$conteo['SI'] + (int)$conteo['NO'] + (int)$conteo['ABSTENCION'];
            $resultado = $esInformativo ? 'Exposición' : 'Sin votos';

            if ($estadoVotacion === 'votacion_cerrada' && !$esInformativo) {
                if ($total === 0) {
                    $resultado = 'Sin votos';
                } elseif ((int)$conteo['SI'] > (int)$conteo['NO']) {
                    $resultado = 'Aprobado';
                } elseif ((int)$conteo['NO'] > (int)$conteo['SI']) {
                    $resultado = 'Rechazado';
                } else {
                    $resultado = 'Empate';
                }
            } elseif ($estadoVotacion === 'votacion_en_curso' && !$esInformativo) {
                $resultado = 'Votación en curso';
            }

            $resultados[] = [
                'numero' => $numero,
                'titulo' => $punto['titulo'],
                'resultado' => $resultado,
                'estado_votacion' => $estadoVotacion,
                'es_informativo' => $esInformativo,
                'tuvo_votacion' => $idVotacion > 0,
                'si' => (int)$conteo['SI'],
                'no' => (int)$conteo['NO'],
                'abstencion' => (int)$conteo['ABSTENCION'],
                'total' => $total,
                'votos' => $estadoVotacion === 'votacion_cerrada' ? $votos : [],
            ];
        }

        return $resultados;
    }

    private function armarPuntosOrdenDia(int $idSesion, array $sesion): array
    {
        $puntos = [
            [
                'numero' => '1',
                'titulo' => 'Aprobación de Acta: ' . (trim((string)($sesion['aprobacion_actas'] ?? '')) ?: 'Sin acta registrada'),
            ],
            [
                'numero' => '2',
                'titulo' => 'Cuenta Presidente del Consejo Regional',
            ],
            [
                'numero' => '3',
                'titulo' => 'Cuenta Comisiones',
            ],
        ];

        foreach ($this->listarPuntosSesion($idSesion, 'cuenta_comisiones') as $indice => $punto) {
            $puntos[] = [
                'numero' => '3.' . ((int)$indice + 1),
                'titulo' => $this->resolverTituloPunto($punto),
            ];
        }

        $puntos[] = [
            'numero' => '4',
            'titulo' => 'Varios',
        ];

        foreach ($this->listarPuntosSesion($idSesion, 'varios') as $indice => $punto) {
            $puntos[] = [
                'numero' => '4.' . ((int)$indice + 1),
                'titulo' => $this->resolverTituloPunto($punto),
            ];
        }

        return $puntos;
    }

    private function listarPuntosSesion(int $idSesion, string $seccion): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                spt.tipo_punto,
                spt.nombre_imprevista,
                spt.observacion_imprevista,
                spt.titulo_punto,
                spt.descripcion_punto,
                c.nombreComision,
                t.nombreTema,
                t.objetivo
             FROM sesion_plenaria_temas spt
             LEFT JOIN t_comision c ON c.idComision = spt.id_comision
             LEFT JOIN t_tema t ON t.idTema = spt.id_tema
             WHERE spt.id_sesion = :id_sesion
               AND spt.vigente = 1
               AND COALESCE(NULLIF(TRIM(spt.seccion_orden), ''), 'varios') = :seccion
             ORDER BY spt.orden ASC, spt.id ASC"
        );
        $stmt->execute([
            ':id_sesion' => $idSesion,
            ':seccion' => $seccion,
        ]);

        $puntos = $stmt->fetchAll();

        return is_array($puntos) ? $puntos : [];
    }

    private function resolverTituloPunto(array $punto): string
    {
        $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));

        if ($tipoPunto === 'TABLA') {
            return trim((string)($punto['titulo_punto'] ?? '')) ?: 'Punto de tabla';
        }

        if ($tipoPunto === 'IMPREVISTA') {
            return trim((string)($punto['nombre_imprevista'] ?? '')) ?: 'Comisión imprevista';
        }

        $comision = trim((string)($punto['nombreComision'] ?? ''));
        $tema = trim((string)($punto['nombreTema'] ?? ''));

        if ($comision !== '' && $tema !== '') {
            return $comision . ' - ' . $tema;
        }

        return $tema !== '' ? $tema : ($comision !== '' ? $comision : 'Punto de comisión');
    }

    private function obtenerEstadosVotacion(int $idSesion): array
    {
        try {
            $stmt = $this->conn->prepare(
                'SELECT punto_numero, estado_votacion
                 FROM pleno_votacion_punto
                 WHERE id_sesion = :id_sesion'
            );
            $stmt->execute([':id_sesion' => $idSesion]);
        } catch (Throwable $e) {
            return [];
        }

        $estados = [];
        while ($row = $stmt->fetch()) {
            if (!is_array($row)) {
                continue;
            }
            $estados[(string)$row['punto_numero']] = [
                'estado_votacion' => (string)$row['estado_votacion'],
            ];
        }

        return $estados;
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
}
