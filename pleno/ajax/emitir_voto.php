<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';
require_once dirname(__DIR__) . '/helpers/votacion_pleno.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $auth = plenoRequireAuthorizedUser();

    if (!$auth['authorized']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para votar.'], JSON_UNESCAPED_UNICODE);
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

    $idUsuario = (int)($_SESSION['idUsuario'] ?? 0);
    $idSesion = (int)($payload['id_sesion'] ?? 0);
    $puntoNumero = trim((string)($payload['punto_numero'] ?? ''));
    $opcion = strtoupper(trim((string)($payload['opcion'] ?? '')));

    if ($idUsuario <= 0 || $idSesion <= 0 || $puntoNumero === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if (!in_array($opcion, ['SI', 'NO', 'ABSTENCION'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'mensaje' => 'Opción de voto no válida.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if (!plenoPuntoPermiteVotacion($puntoNumero)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'mensaje' => 'Este punto no requiere votación.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $conn = plenoVotacionConn();
    plenoAsegurarTablaVotacion($conn);

    $stmtSesion = $conn->prepare(
        "SELECT estado
         FROM sesiones_plenarias
         WHERE id_sesion = :id_sesion
           AND vigencia = 1
         LIMIT 1"
    );
    $stmtSesion->execute([':id_sesion' => $idSesion]);
    $estadoSesion = trim((string)$stmtSesion->fetchColumn());

    if ($estadoSesion !== 'en_curso') {
        http_response_code(409);
        echo json_encode(['success' => false, 'mensaje' => 'La sesión no está en curso.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $estadoVotacion = plenoObtenerEstadoVotacion($conn, $idSesion, $puntoNumero);
    if ($estadoVotacion !== 'votacion_en_curso') {
        http_response_code(409);
        echo json_encode(['success' => false, 'mensaje' => 'No hay votación abierta para este punto.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $stmtAsistencia = $conn->prepare(
        "SELECT COUNT(*)
         FROM t_asistencia
         WHERE id_sesion_plenaria = :id_sesion
           AND t_usuario_idUsuario = :id_usuario
           AND TRIM(UPPER(estadoAsistencia)) = 'PRESENTE'"
    );
    $stmtAsistencia->execute([
        ':id_sesion' => $idSesion,
        ':id_usuario' => $idUsuario,
    ]);

    if ((int)$stmtAsistencia->fetchColumn() === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'mensaje' => 'Debe registrar asistencia antes de votar.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $votacion = plenoObtenerCabeceraVotacion($conn, $idSesion, $puntoNumero);
    if (!$votacion || (int)($votacion['habilitada'] ?? 0) !== 1) {
        http_response_code(409);
        echo json_encode(['success' => false, 'mensaje' => 'La cabecera de votación no está habilitada.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $idVotacion = (int)$votacion['idVotacion'];
    $stmtDuplicado = $conn->prepare(
        'SELECT idVoto
         FROM t_voto
         WHERE t_votacion_idVotacion = :id_votacion
           AND t_usuario_idUsuario = :id_usuario
         LIMIT 1'
    );
    $stmtDuplicado->execute([
        ':id_votacion' => $idVotacion,
        ':id_usuario' => $idUsuario,
    ]);

    if ($stmtDuplicado->fetchColumn()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'ya_voto' => true, 'mensaje' => 'Usted ya emitió su voto en este punto.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $stmtInsert = $conn->prepare(
        "INSERT INTO t_voto
            (t_votacion_idVotacion, t_usuario_idUsuario, idVotacion, idUsuario, opcionVoto, fechaVoto, fechaHoraVoto, origenVoto)
         VALUES
            (:id_votacion, :id_usuario, 0, 0, :opcion, NOW(), NOW(), 'PLENO')"
    );
    $stmtInsert->execute([
        ':id_votacion' => $idVotacion,
        ':id_usuario' => $idUsuario,
        ':opcion' => $opcion,
    ]);

    echo json_encode([
        'success' => true,
        'id_votacion' => $idVotacion,
        'opcion' => $opcion,
        'mensaje' => 'Voto registrado correctamente.',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible registrar el voto.'], JSON_UNESCAPED_UNICODE);
}
