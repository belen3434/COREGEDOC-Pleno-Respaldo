<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';
require_once dirname(__DIR__) . '/helpers/votacion_pleno.php';

header('Content-Type: application/json; charset=utf-8');

function plenoVotacionRespuestaSinActiva(): void
{
    echo json_encode([
        'success' => true,
        'hay_votacion' => false,
        'requiere_votacion' => false,
        'modo' => 'sin_votacion',
        'mensaje' => 'No hay votación activa en este momento.',
        'sesion' => null,
        'votacion' => [
            'id_votacion' => 0,
            'punto_numero' => '',
            'estado_votacion' => 'pendiente',
            'titulo' => 'Sin votación activa',
            'descripcion' => 'No hay votación activa en este momento.',
        ],
        'conteo' => ['SI' => 0, 'NO' => 0, 'ABSTENCION' => 0],
        'total_participantes' => 0,
        'total_votos' => 0,
        'participantes' => [],
    ], JSON_UNESCAPED_UNICODE);
}

function plenoVotacionResolverDetallePunto($conn, int $idSesion, string $puntoNumero): array
{
    $puntoNumero = trim($puntoNumero);

    if ($puntoNumero === '1') {
        $stmt = $conn->prepare('SELECT aprobacion_actas FROM sesiones_plenarias WHERE id_sesion = :id_sesion LIMIT 1');
        $stmt->execute([':id_sesion' => $idSesion]);
        $titulo = trim((string)$stmt->fetchColumn());

        return [
            'titulo' => $titulo !== '' ? $titulo : 'Aprobación de acta',
            'descripcion' => '',
            'tipo_punto' => 'ACTA',
            'seccion_orden' => '',
        ];
    }

    $seccion = '';
    $orden = 0;

    if (preg_match('/^3\.(\d+)$/', $puntoNumero, $matches) === 1) {
        $seccion = 'cuenta_comisiones';
        $orden = (int)$matches[1];
    }

    if (preg_match('/^4\.(\d+)$/', $puntoNumero, $matches) === 1) {
        $seccion = 'varios';
        $orden = (int)$matches[1];
    }

    if ($seccion === '' || $orden <= 0) {
        return [
            'titulo' => 'Punto de comisión',
            'descripcion' => '',
            'tipo_punto' => 'COMISION',
            'seccion_orden' => '',
        ];
    }

    $stmt = $conn->prepare(
        "SELECT
            spt.tipo_punto,
            spt.nombre_imprevista,
            spt.observacion_imprevista,
            spt.titulo_punto,
            spt.descripcion_punto,
            COALESCE(NULLIF(TRIM(spt.seccion_orden), ''), 'varios') AS seccion_orden,
            t.nombreTema,
            t.objetivo
         FROM sesion_plenaria_temas spt
         LEFT JOIN t_tema t ON t.idTema = spt.id_tema
         WHERE spt.id_sesion = :id_sesion
           AND spt.vigente = 1
           AND COALESCE(NULLIF(TRIM(spt.seccion_orden), ''), 'varios') = :seccion
           AND spt.orden = :orden
         ORDER BY spt.orden ASC, spt.id ASC
         LIMIT 1"
    );
    $stmt->execute([
        ':id_sesion' => $idSesion,
        ':seccion' => $seccion,
        ':orden' => $orden,
    ]);
    $punto = $stmt->fetch();

    if (!is_array($punto)) {
        return [
            'titulo' => 'Punto de comisión',
            'descripcion' => '',
            'tipo_punto' => 'COMISION',
            'seccion_orden' => $seccion,
        ];
    }

    $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));
    $titulo = '';
    $descripcion = '';

    if ($tipoPunto === 'IMPREVISTA') {
        $titulo = trim((string)($punto['nombre_imprevista'] ?? ''));
        $descripcion = trim((string)($punto['observacion_imprevista'] ?? ''));
    } elseif ($tipoPunto === 'TABLA') {
        $titulo = trim((string)($punto['titulo_punto'] ?? ''));
        $descripcion = trim((string)($punto['descripcion_punto'] ?? ''));
    } else {
        $titulo = trim((string)($punto['nombreTema'] ?? ''));
        $descripcion = trim((string)($punto['objetivo'] ?? $punto['descripcion_punto'] ?? ''));
    }

    return [
        'titulo' => $titulo !== '' ? $titulo : 'Punto de comisión',
        'descripcion' => $descripcion,
        'tipo_punto' => $tipoPunto,
        'seccion_orden' => (string)($punto['seccion_orden'] ?? $seccion),
    ];
}

try {
    $auth = plenoRequireAuthorizedUser();

    if (!$auth['authorized']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para ver la votación.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $conn = plenoVotacionConn();
    $idSesionSolicitada = (int)($_GET['id_sesion'] ?? 0);

    if ($idSesionSolicitada > 0) {
        $stmtSesion = $conn->prepare(
            "SELECT id_sesion, numero_sesion, estado, punto_actual
             FROM sesiones_plenarias
             WHERE id_sesion = :id_sesion
               AND vigencia = 1
               AND estado = 'en_curso'
             LIMIT 1"
        );
        $stmtSesion->execute([':id_sesion' => $idSesionSolicitada]);
    } else {
        $stmtSesion = $conn->prepare(
            "SELECT id_sesion, numero_sesion, estado, punto_actual
             FROM sesiones_plenarias
             WHERE vigencia = 1
               AND estado = 'en_curso'
             ORDER BY fecha DESC, hora DESC, id_sesion DESC
             LIMIT 1"
        );
        $stmtSesion->execute();
    }

    $sesionActiva = $stmtSesion->fetch();
    if (!is_array($sesionActiva)) {
        plenoVotacionRespuestaSinActiva();
        exit();
    }

    $idSesion = (int)$sesionActiva['id_sesion'];
    $puntoActual = trim((string)($sesionActiva['punto_actual'] ?? ''));

    if (preg_match('/^4\.\d+$/', $puntoActual) === 1) {
        $detallePunto = plenoVotacionResolverDetallePunto($conn, $idSesion, $puntoActual);

        echo json_encode([
            'success' => true,
            'hay_votacion' => true,
            'requiere_votacion' => false,
            'modo' => 'exposicion',
            'mensaje' => 'Punto en exposición. No requiere votación.',
            'sesion' => [
                'id_sesion' => $idSesion,
                'numero_sesion' => $sesionActiva['numero_sesion'] ?? '',
                'estado' => $sesionActiva['estado'] ?? '',
            ],
            'votacion' => [
                'id_votacion' => 0,
                'punto_numero' => $puntoActual,
                'estado_votacion' => 'no_vota',
                'titulo' => $detallePunto['titulo'],
                'descripcion' => $detallePunto['descripcion'],
                'tipo_punto' => $detallePunto['tipo_punto'],
                'seccion_orden' => 'varios',
            ],
            'conteo' => ['SI' => 0, 'NO' => 0, 'ABSTENCION' => 0],
            'total_participantes' => 0,
            'total_votos' => 0,
            'participantes' => [],
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $stmtVotacion = $conn->prepare(
        "SELECT
            p.punto_numero,
            p.estado_votacion
         FROM pleno_votacion_punto p
         WHERE p.id_sesion = :id_sesion
           AND p.estado_votacion = 'votacion_en_curso'
         ORDER BY COALESCE(p.fecha_inicio_votacion, p.fecha_actualizacion) DESC, p.id DESC
         LIMIT 1"
    );
    $stmtVotacion->execute([':id_sesion' => $idSesion]);

    $votacionActiva = $stmtVotacion->fetch();
    if (!is_array($votacionActiva)) {
        plenoVotacionRespuestaSinActiva();
        exit();
    }

    $puntoNumero = trim((string)$votacionActiva['punto_numero']);
    $votacion = plenoObtenerCabeceraVotacion($conn, $idSesion, $puntoNumero);

    if (!is_array($votacion)) {
        plenoVotacionRespuestaSinActiva();
        exit();
    }

    $idVotacion = (int)$votacion['idVotacion'];
    $participantes = [];
    $conteo = ['SI' => 0, 'NO' => 0, 'ABSTENCION' => 0];

    $stmtParticipantes = $conn->prepare(
        "SELECT
            u.idUsuario,
            TRIM(CONCAT(u.pNombre, ' ', COALESCE(NULLIF(u.sNombre, ''), ''), ' ', u.aPaterno, ' ', u.aMaterno)) AS nombreCompleto,
            u.tipoUsuario_id,
            v.opcionVoto,
            COALESCE(v.fechaHoraVoto, v.fechaVoto) AS fechaVoto
         FROM t_usuario u
         LEFT JOIN t_voto v
            ON v.t_usuario_idUsuario = u.idUsuario
           AND v.t_votacion_idVotacion = :id_votacion
         WHERE u.estado = 1
           AND u.tipoUsuario_id IN (1, 22)
         ORDER BY u.aPaterno ASC, u.aMaterno ASC, u.pNombre ASC"
    );
    $stmtParticipantes->execute([':id_votacion' => $idVotacion]);

    while ($row = $stmtParticipantes->fetch()) {
        if (!is_array($row)) {
            continue;
        }

        $opcion = strtoupper(trim((string)($row['opcionVoto'] ?? '')));
        if (!isset($conteo[$opcion])) {
            $opcion = '';
        } else {
            $conteo[$opcion]++;
        }

        $participantes[] = [
            'id_usuario' => (int)$row['idUsuario'],
            'nombre' => trim((string)$row['nombreCompleto']),
            'rol' => (int)$row['tipoUsuario_id'] === 22 ? 'Gobernador' : 'Consejero/a',
            'voto' => $opcion,
            'estado' => $opcion !== '' ? $opcion : 'SIN_VOTAR',
            'fecha_voto' => $row['fechaVoto'] ?? null,
        ];
    }

    $detallePunto = plenoVotacionResolverDetallePunto($conn, $idSesion, $puntoNumero);

    echo json_encode([
        'success' => true,
        'hay_votacion' => true,
        'requiere_votacion' => true,
        'modo' => 'votacion',
        'sesion' => [
            'id_sesion' => $idSesion,
            'numero_sesion' => $sesionActiva['numero_sesion'] ?? '',
            'estado' => $sesionActiva['estado'] ?? '',
        ],
        'votacion' => [
            'id_votacion' => $idVotacion,
            'punto_numero' => $puntoNumero,
            'estado_votacion' => 'votacion_en_curso',
            'titulo' => $detallePunto['titulo'],
            'descripcion' => $detallePunto['descripcion'],
            'tipo_punto' => $detallePunto['tipo_punto'],
            'seccion_orden' => $detallePunto['seccion_orden'],
        ],
        'conteo' => $conteo,
        'total_participantes' => count($participantes),
        'total_votos' => array_sum($conteo),
        'participantes' => $participantes,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible cargar el estado de la votación.'], JSON_UNESCAPED_UNICODE);
}
