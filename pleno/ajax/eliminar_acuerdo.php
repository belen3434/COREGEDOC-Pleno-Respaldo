<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';
require_once dirname(__DIR__) . '/models/AcuerdoPleno.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $auth = plenoRequireAuthorizedUser();

    if (!$auth['authorized'] || !in_array((int)($auth['tipoUsuarioId'] ?? 0), [6, 20], true)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para eliminar acuerdos.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'mensaje' => 'Metodo no permitido.'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $payload = $_POST;
    $jsonPayload = json_decode(file_get_contents('php://input'), true);
    if (is_array($jsonPayload)) {
        $payload = $jsonPayload;
    }

    $idAcuerdo = (int)($payload['id_acuerdo'] ?? 0);
    $idUsuario = (int)($_SESSION['idUsuario'] ?? 0);

    $modelo = new AcuerdoPleno();
    $resultado = $modelo->eliminar($idAcuerdo, $idUsuario);

    if (empty($resultado['success'])) {
        http_response_code(400);
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible eliminar el acuerdo.'], JSON_UNESCAPED_UNICODE);
}
