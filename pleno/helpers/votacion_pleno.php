<?php

use App\Config\Database;

if (!function_exists('plenoVotacionConn')) {
    function plenoVotacionConn()
    {
        $database = new Database();
        return $database->getConnection();
    }
}

if (!function_exists('plenoAsegurarTablaVotacion')) {
    function plenoAsegurarTablaVotacion($conn): void
    {
        $conn->exec(
            "CREATE TABLE IF NOT EXISTS pleno_votacion_punto (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_sesion INT NOT NULL,
                punto_numero VARCHAR(20) NOT NULL,
                estado_votacion VARCHAR(30) NOT NULL DEFAULT 'pendiente',
                fecha_inicio_votacion DATETIME NULL,
                fecha_cierre_votacion DATETIME NULL,
                fecha_actualizacion DATETIME NULL,
                UNIQUE KEY uq_pleno_votacion_punto (id_sesion, punto_numero)
            )"
        );
    }
}

if (!function_exists('plenoPuntoPermiteVotacion')) {
    function plenoPuntoPermiteVotacion(string $puntoNumero): bool
    {
        return $puntoNumero === '1' || preg_match('/^3\.\d+$/', $puntoNumero) === 1;
    }
}

if (!function_exists('plenoNormalizarEstadoVotacion')) {
    function plenoNormalizarEstadoVotacion(?string $estado, string $puntoNumero): string
    {
        if (!plenoPuntoPermiteVotacion($puntoNumero)) {
            return 'no_vota';
        }

        $estado = trim((string)$estado);
        if ($estado === '' || $estado === 'sin_votacion') {
            return 'pendiente';
        }

        return in_array($estado, ['pendiente', 'votacion_en_curso', 'votacion_cerrada', 'no_vota'], true)
            ? $estado
            : 'pendiente';
    }
}

if (!function_exists('plenoObtenerEstadoVotacion')) {
    function plenoObtenerEstadoVotacion($conn, int $idSesion, string $puntoNumero): string
    {
        plenoAsegurarTablaVotacion($conn);

        $stmt = $conn->prepare(
            'SELECT estado_votacion
             FROM pleno_votacion_punto
             WHERE id_sesion = :id_sesion
               AND punto_numero = :punto_numero
             LIMIT 1'
        );
        $stmt->execute([
            ':id_sesion' => $idSesion,
            ':punto_numero' => $puntoNumero,
        ]);
        $row = $stmt->fetch();

        return plenoNormalizarEstadoVotacion(
            is_array($row) && !empty($row['estado_votacion']) ? (string)$row['estado_votacion'] : null,
            $puntoNumero
        );
    }
}

if (!function_exists('plenoObtenerEstadosVotacion')) {
    function plenoObtenerEstadosVotacion($conn, int $idSesion): array
    {
        plenoAsegurarTablaVotacion($conn);

        $stmt = $conn->prepare(
            'SELECT punto_numero, estado_votacion
             FROM pleno_votacion_punto
             WHERE id_sesion = :id_sesion'
        );
        $stmt->execute([':id_sesion' => $idSesion]);

        $estados = [];
        while ($row = $stmt->fetch()) {
            if (!is_array($row)) {
                continue;
            }

            $puntoNumero = trim((string)($row['punto_numero'] ?? ''));
            if ($puntoNumero === '') {
                continue;
            }

            $estados[$puntoNumero] = plenoNormalizarEstadoVotacion(
                (string)($row['estado_votacion'] ?? ''),
                $puntoNumero
            );
        }

        return $estados;
    }
}

if (!function_exists('plenoNombreVotacionPunto')) {
    function plenoNombreVotacionPunto(int $idSesion, string $puntoNumero): string
    {
        return 'Pleno ' . $idSesion . ' - Punto ' . trim($puntoNumero);
    }
}

if (!function_exists('plenoObtenerDescripcionPunto')) {
    function plenoObtenerDescripcionPunto($conn, int $idSesion, string $puntoNumero): string
    {
        $puntoNumero = trim($puntoNumero);

        if ($puntoNumero === '1') {
            $stmt = $conn->prepare('SELECT aprobacion_actas FROM sesiones_plenarias WHERE id_sesion = :id LIMIT 1');
            $stmt->execute([':id' => $idSesion]);
            $descripcion = trim((string)$stmt->fetchColumn());

            return $descripcion !== '' ? $descripcion : 'Aprobación de acta';
        }

        if (preg_match('/^3\.(\d+)$/', $puntoNumero, $matches) === 1) {
            $orden = (int)$matches[1];
            $stmt = $conn->prepare(
                "SELECT
                    COALESCE(NULLIF(TRIM(spt.titulo_punto), ''), NULLIF(TRIM(t.nombreTema), ''), NULLIF(TRIM(c.nombreComision), ''), 'Punto de comisión') AS titulo,
                    COALESCE(NULLIF(TRIM(spt.descripcion_punto), ''), NULLIF(TRIM(t.objetivo), ''), '') AS descripcion
                 FROM sesion_plenaria_temas spt
                 LEFT JOIN t_tema t ON t.idTema = spt.id_tema
                 LEFT JOIN t_comision c ON c.idComision = spt.id_comision
                 WHERE spt.id_sesion = :id_sesion
                   AND spt.vigente = 1
                   AND COALESCE(NULLIF(TRIM(spt.seccion_orden), ''), 'varios') = 'cuenta_comisiones'
                 ORDER BY spt.orden ASC, spt.id ASC
                 LIMIT 1 OFFSET " . max(0, $orden - 1)
            );
            $stmt->execute([':id_sesion' => $idSesion]);
            $row = $stmt->fetch();

            if (is_array($row)) {
                $titulo = trim((string)($row['titulo'] ?? ''));
                $descripcion = trim((string)($row['descripcion'] ?? ''));

                return trim($titulo . ($descripcion !== '' ? ' - ' . $descripcion : ''));
            }
        }

        return 'Punto ' . $puntoNumero;
    }
}

if (!function_exists('plenoObtenerCabeceraVotacion')) {
    function plenoObtenerCabeceraVotacion($conn, int $idSesion, string $puntoNumero): ?array
    {
        $stmt = $conn->prepare(
            'SELECT idVotacion, nombreVotacion, descripcion, habilitada
             FROM t_votacion
             WHERE nombreVotacion = :nombre
             ORDER BY idVotacion DESC
             LIMIT 1'
        );
        $stmt->execute([':nombre' => plenoNombreVotacionPunto($idSesion, $puntoNumero)]);
        $votacion = $stmt->fetch();

        return is_array($votacion) ? $votacion : null;
    }
}

if (!function_exists('plenoObtenerOCrearCabeceraVotacion')) {
    function plenoObtenerOCrearCabeceraVotacion($conn, int $idSesion, string $puntoNumero): array
    {
        $nombre = plenoNombreVotacionPunto($idSesion, $puntoNumero);
        $descripcion = plenoObtenerDescripcionPunto($conn, $idSesion, $puntoNumero);
        $votacion = plenoObtenerCabeceraVotacion($conn, $idSesion, $puntoNumero);

        if ($votacion) {
            $stmt = $conn->prepare(
                'UPDATE t_votacion
                 SET descripcion = :descripcion,
                     habilitada = 1
                 WHERE idVotacion = :id'
            );
            $stmt->execute([
                ':descripcion' => $descripcion,
                ':id' => (int)$votacion['idVotacion'],
            ]);

            $votacion['descripcion'] = $descripcion;
            $votacion['habilitada'] = 1;
            return $votacion;
        }

        $stmt = $conn->prepare(
            'INSERT INTO t_votacion
                (nombreVotacion, descripcion, fechaCreacion, habilitada)
             VALUES
                (:nombre, :descripcion, NOW(), 1)'
        );
        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
        ]);

        return [
            'idVotacion' => (int)$conn->lastInsertId(),
            'nombreVotacion' => $nombre,
            'descripcion' => $descripcion,
            'habilitada' => 1,
        ];
    }
}

if (!function_exists('plenoObtenerVotoUsuarioPunto')) {
    function plenoObtenerVotoUsuarioPunto($conn, int $idSesion, string $puntoNumero, int $idUsuario): ?array
    {
        $votacion = plenoObtenerCabeceraVotacion($conn, $idSesion, $puntoNumero);
        if (!$votacion) {
            return null;
        }

        $stmt = $conn->prepare(
            'SELECT idVoto, opcionVoto, fechaVoto, fechaHoraVoto
             FROM t_voto
             WHERE t_votacion_idVotacion = :id_votacion
               AND t_usuario_idUsuario = :id_usuario
             LIMIT 1'
        );
        $stmt->execute([
            ':id_votacion' => (int)$votacion['idVotacion'],
            ':id_usuario' => $idUsuario,
        ]);
        $voto = $stmt->fetch();

        return is_array($voto) ? $voto : null;
    }
}
