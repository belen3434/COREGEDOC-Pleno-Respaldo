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
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para registrar asistencia.'], JSON_UNESCAPED_UNICODE);
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
    $idUsuario = (int)($_SESSION['idUsuario'] ?? 0);

    if ($idUsuario <= 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'mensaje' => 'Sesión de usuario no válida.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $asistencia = new AsistenciaPleno();
    $resultado = $asistencia->registrarAsistencia($idSesion, $idUsuario);

    if (!$resultado['success']) {
        http_response_code(409);
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible registrar la asistencia.'], JSON_UNESCAPED_UNICODE);
}
