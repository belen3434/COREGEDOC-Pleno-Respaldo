<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';
require_once dirname(__DIR__) . '/models/AsistenciaPleno.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $auth = plenoRequireAuthorizedUser();

    if (!$auth['authorized'] || !in_array((int)($auth['tipoUsuarioId'] ?? 0), [6, 20], true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para ver la asistencia.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $idSesion = (int)($_GET['id_sesion'] ?? 0);
    if ($idSesion <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'mensaje' => 'Debe indicar una sesión válida.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $asistencia = new AsistenciaPleno();

    echo json_encode([
        'success' => true,
        'resumen' => $asistencia->obtenerResumenSesion($idSesion),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible cargar la asistencia.'], JSON_UNESCAPED_UNICODE);
}
