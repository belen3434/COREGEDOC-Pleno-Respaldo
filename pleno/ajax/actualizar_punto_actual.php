<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';
require_once dirname(__DIR__) . '/models/SesionPlenaria.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $auth = plenoRequireAuthorizedUser();

    if (!$auth['authorized'] || !in_array((int)($auth['tipoUsuarioId'] ?? 0), [6, 20], true)) {
        http_response_code(403);
        echo json_encode([
            'ok' => false,
            'error' => 'No tiene permisos para actualizar el punto actual.',
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'ok' => false,
            'error' => 'Método no permitido.',
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $payload = $_POST;
    $rawBody = file_get_contents('php://input');
    $jsonPayload = json_decode($rawBody, true);

    if (is_array($jsonPayload)) {
        $payload = $jsonPayload;
    }

    $idSesion = (int)($payload['id_sesion'] ?? 0);
    $puntoActual = trim((string)($payload['punto_actual'] ?? ''));

    if ($idSesion <= 0) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Debe indicar una sesión válida.',
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($puntoActual === '' || strlen($puntoActual) > 20 || !preg_match('/^\d+(?:\.\d+)?$/', $puntoActual)) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Debe indicar un punto actual válido.',
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $sesiones = new SesionPlenaria();
    $actualizado = $sesiones->actualizarPuntoActual($idSesion, $puntoActual);

    if (!$actualizado) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'No fue posible guardar el punto actual.',
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    echo json_encode([
        'ok' => true,
        'punto_actual' => $puntoActual,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'No fue posible guardar el punto actual.',
    ], JSON_UNESCAPED_UNICODE);
}
