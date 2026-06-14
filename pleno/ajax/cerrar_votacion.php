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
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para cerrar votación.'], JSON_UNESCAPED_UNICODE);
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
    $estadoActual = plenoObtenerEstadoVotacion($conn, $idSesion, $puntoActual);

    if ($estadoActual !== 'votacion_en_curso') {
        http_response_code(409);
        echo json_encode(['success' => false, 'mensaje' => 'No hay una votación en curso para cerrar.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $stmt = $conn->prepare(
        "UPDATE pleno_votacion_punto
         SET estado_votacion = 'votacion_cerrada',
             fecha_cierre_votacion = NOW(),
             fecha_actualizacion = NOW()
         WHERE id_sesion = :id_sesion
           AND punto_numero = :punto_numero"
    );
    $stmt->execute([
        ':id_sesion' => $idSesion,
        ':punto_numero' => $puntoActual,
    ]);

    echo json_encode([
        'success' => true,
        'punto_actual' => $puntoActual,
        'estado_votacion' => 'votacion_cerrada',
        'mensaje' => 'Votación cerrada.',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible cerrar la votación.'], JSON_UNESCAPED_UNICODE);
}
