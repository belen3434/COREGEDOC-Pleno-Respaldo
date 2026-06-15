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

function plenoVotacionObtenerTituloPunto($conn, int $idSesion, string $puntoNumero): string
{
    $puntoNumero = trim($puntoNumero);

    if ($puntoNumero === '1') {
        $stmt = $conn->prepare('SELECT aprobacion_actas FROM sesiones_plenarias WHERE id_sesion = :id_sesion LIMIT 1');
        $stmt->execute([':id_sesion' => $idSesion]);
        $titulo = trim((string)$stmt->fetchColumn());

        return $titulo !== '' ? $titulo : 'Aprobacion de acta';
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
        return 'Punto de comisión';
    }

    $stmt = $conn->prepare(
        "SELECT
            spt.tipo_punto,
            spt.nombre_imprevista,
            spt.titulo_punto,
            t.nombreTema
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
        return 'Punto de comisión';
    }

    $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));

    if ($tipoPunto === 'IMPREVISTA') {
        $titulo = trim((string)($punto['nombre_imprevista'] ?? ''));
        return $titulo !== '' ? $titulo : 'Punto de comisión';
    }

    if ($tipoPunto === 'TABLA') {
        $titulo = trim((string)($punto['titulo_punto'] ?? ''));
        return $titulo !== '' ? $titulo : 'Punto de comisión';
    }

    $titulo = trim((string)($punto['nombreTema'] ?? ''));
    return $titulo !== '' ? $titulo : 'Punto de comisión';
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
        $stmtVotacion = $conn->prepare(
            "SELECT
                s.id_sesion,
                s.numero_sesion,
                s.estado AS estado_sesion,
                p.punto_numero,
                p.estado_votacion
             FROM pleno_votacion_punto p
             INNER JOIN sesiones_plenarias s ON s.id_sesion = p.id_sesion
             WHERE s.id_sesion = :id_sesion
               AND s.vigencia = 1
               AND s.estado = 'en_curso'
               AND p.estado_votacion = 'votacion_en_curso'
             ORDER BY COALESCE(p.fecha_inicio_votacion, p.fecha_actualizacion) DESC, p.id DESC
             LIMIT 1"
        );
        $stmtVotacion->execute([':id_sesion' => $idSesionSolicitada]);
    } else {
        $stmtVotacion = $conn->prepare(
            "SELECT
                s.id_sesion,
                s.numero_sesion,
                s.estado AS estado_sesion,
                p.punto_numero,
                p.estado_votacion
             FROM pleno_votacion_punto p
             INNER JOIN sesiones_plenarias s ON s.id_sesion = p.id_sesion
             WHERE s.vigencia = 1
               AND s.estado = 'en_curso'
               AND p.estado_votacion = 'votacion_en_curso'
             ORDER BY COALESCE(p.fecha_inicio_votacion, p.fecha_actualizacion) DESC, p.id DESC
             LIMIT 1"
        );
        $stmtVotacion->execute();
    }

    $votacionActiva = $stmtVotacion->fetch();
    if (!is_array($votacionActiva)) {
        plenoVotacionRespuestaSinActiva();
        exit();
    }

    $idSesion = (int)$votacionActiva['id_sesion'];
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

    $tituloPunto = plenoVotacionObtenerTituloPunto($conn, $idSesion, $puntoNumero);

    echo json_encode([
        'success' => true,
        'hay_votacion' => true,
        'sesion' => [
            'id_sesion' => $idSesion,
            'numero_sesion' => $votacionActiva['numero_sesion'] ?? '',
            'estado' => $votacionActiva['estado_sesion'] ?? '',
        ],
        'votacion' => [
            'id_votacion' => $idVotacion,
            'punto_numero' => $puntoNumero,
            'estado_votacion' => 'votacion_en_curso',
            'titulo' => $tituloPunto,
            'descripcion' => '',
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
