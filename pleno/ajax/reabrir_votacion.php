<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';
require_once dirname(__DIR__) . '/helpers/votacion_pleno.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $auth = plenoRequireAuthorizedUser();

    if (!$auth['authorized'] || !in_array((int)($auth['tipoUsuarioId'] ?? 0), [6, 20], true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para reabrir votación.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'mensaje' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $payload = $_POST;
    $jsonPayload = json_decode(file_get_contents('php://input'), true);
    if (is_array($jsonPayload)) {
        $payload = $jsonPayload;
    }

    $idSesion = (int)($payload['id_sesion'] ?? 0);
    if ($idSesion <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'mensaje' => 'Debe indicar una sesión válida.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $conn = plenoVotacionConn();
    plenoAsegurarTablaVotacion($conn);

    $stmtSesion = $conn->prepare('SELECT punto_actual, estado FROM sesiones_plenarias WHERE id_sesion = :id LIMIT 1');
    $stmtSesion->execute([':id' => $idSesion]);
    $sesion = $stmtSesion->fetch();

    if (!is_array($sesion) || (string)($sesion['estado'] ?? '') !== 'en_curso') {
        http_response_code(409);
        echo json_encode(['success' => false, 'mensaje' => 'La sesión no está en curso.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $puntoActual = trim((string)($sesion['punto_actual'] ?? '1'));
    $puntoActual = $puntoActual !== '' ? $puntoActual : '1';

    if (!plenoPuntoPermiteVotacion($puntoActual)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'mensaje' => 'Este punto no requiere votación.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $estadoActual = plenoObtenerEstadoVotacion($conn, $idSesion, $puntoActual);
    if ($estadoActual !== 'votacion_cerrada') {
        http_response_code(409);
        echo json_encode(['success' => false, 'mensaje' => 'Solo se puede reabrir una votación cerrada.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $conn->beginTransaction();

    $stmt = $conn->prepare(
        "UPDATE pleno_votacion_punto
         SET estado_votacion = 'votacion_en_curso',
             fecha_cierre_votacion = NULL,
             fecha_actualizacion = NOW()
         WHERE id_sesion = :id_sesion
           AND punto_numero = :punto_numero"
    );
    $stmt->execute([
        ':id_sesion' => $idSesion,
        ':punto_numero' => $puntoActual,
    ]);

    $votacion = plenoObtenerOCrearCabeceraVotacion($conn, $idSesion, $puntoActual);
    $stmtCabecera = $conn->prepare('UPDATE t_votacion SET habilitada = 1 WHERE idVotacion = :id');
    $stmtCabecera->execute([':id' => (int)$votacion['idVotacion']]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'punto_actual' => $puntoActual,
        'id_votacion' => (int)$votacion['idVotacion'],
        'estado_votacion' => 'votacion_en_curso',
        'mensaje' => 'Votación reabierta correctamente.',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible reabrir la votación.'], JSON_UNESCAPED_UNICODE);
}
