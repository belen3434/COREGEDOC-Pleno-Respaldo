<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';
require_once dirname(__DIR__) . '/models/AsistenciaPleno.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $auth = plenoRequireAuthorizedUser();

    if (!$auth['authorized']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para acceder al Pleno.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $idUsuario = (int)($_SESSION['idUsuario'] ?? 0);
    if ($idUsuario <= 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'mensaje' => 'Sesión de usuario no válida.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $asistencia = new AsistenciaPleno();
    $estado = $asistencia->obtenerEstadoUsuario($idUsuario);

    echo json_encode([
        'success' => true,
        'status' => $estado['status'],
        'sesion' => $estado['sesion'],
        'asistencia' => $estado['asistencia'],
        'ya_marco' => $estado['ya_marco'],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible consultar la asistencia.'], JSON_UNESCAPED_UNICODE);
}
