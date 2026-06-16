<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';
require_once dirname(__DIR__) . '/models/SesionPlenaria.php';
require_once dirname(__DIR__) . '/services/CertificadoAcuerdoPdfService.php';
require_once dirname(__DIR__) . '/models/CertificadoAcuerdoPleno.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $auth = plenoRequireAuthorizedUser();
    error_log('[CertificadoAjax] request_start method=' . ($_SERVER['REQUEST_METHOD'] ?? ''));

    if (!$auth['authorized'] || !in_array((int)($auth['tipoUsuarioId'] ?? 0), [6, 20], true)) {
        error_log('[CertificadoAjax] forbidden tipo_usuario=' . (int)($auth['tipoUsuarioId'] ?? 0));
        http_response_code(403);
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para generar certificados.'], JSON_UNESCAPED_UNICODE);
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

    $idSesion = (int)($payload['id_sesion'] ?? 0);
    error_log('[CertificadoAjax] payload_id_sesion=' . $idSesion . ' usuario=' . (int)($_SESSION['idUsuario'] ?? 0));
    if ($idSesion <= 0) {
        $sesionActual = (new SesionPlenaria())->obtenerUltimaVigente();
        $idSesion = (int)($sesionActual['id_sesion'] ?? 0);
        error_log('[CertificadoAjax] fallback_id_sesion=' . $idSesion);
    }

    $modelo = new CertificadoAcuerdoPleno();
    $resultado = $modelo->generarCertificado($idSesion, (int)($_SESSION['idUsuario'] ?? 0));
    error_log('[CertificadoAjax] resultado=' . json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    if (empty($resultado['success'])) {
        http_response_code(400);
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[CertificadoAjax] exception=' . $e->getMessage() . ' file=' . $e->getFile() . ' line=' . $e->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible generar el certificado.'], JSON_UNESCAPED_UNICODE);
}
