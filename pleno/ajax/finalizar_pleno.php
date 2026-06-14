<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';

use App\Config\Database;

header('Content-Type: application/json; charset=utf-8');

try {
    $auth = plenoRequireAuthorizedUser();

    if (!$auth['authorized'] || !in_array((int)($auth['tipoUsuarioId'] ?? 0), [6, 20], true)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'mensaje' => 'No tiene permisos para finalizar el pleno.',
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Método no permitido.',
        ], JSON_UNESCAPED_UNICODE);
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
        echo json_encode([
            'success' => false,
            'mensaje' => 'Debe indicar una sesión válida.',
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

    $stmt = $conn->prepare('SELECT id_sesion, estado FROM sesiones_plenarias WHERE id_sesion = :id LIMIT 1');
    $stmt->execute([':id' => $idSesion]);
    $sesion = $stmt->fetch();

    if (!is_array($sesion)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'mensaje' => 'La sesión solicitada no existe.',
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    if (trim((string)($sesion['estado'] ?? '')) !== 'en_curso') {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Solo se puede finalizar una sesión en curso.',
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $stmtActualizar = $conn->prepare(
        'UPDATE sesiones_plenarias
         SET estado = :estado,
             fecha_actualizacion = NOW()
         WHERE id_sesion = :id'
    );
    $actualizado = $stmtActualizar->execute([
        ':id' => $idSesion,
        ':estado' => 'finalizada',
    ]);

    if (!$actualizado) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'mensaje' => 'No fue posible finalizar el pleno.',
        ], JSON_UNESCAPED_UNICODE);
        exit();
    }

    echo json_encode([
        'success' => true,
        'estado' => 'finalizada',
        'mensaje' => 'Pleno finalizado correctamente.',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'mensaje' => 'No fue posible finalizar el pleno.',
    ], JSON_UNESCAPED_UNICODE);
}
