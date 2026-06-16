<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';
require_once dirname(__DIR__) . '/helpers/votacion_pleno.php';
require_once dirname(__DIR__) . '/models/SesionPlenaria.php';
require_once dirname(__DIR__) . '/models/AsistenciaPleno.php';
require_once dirname(__DIR__) . '/models/ResumenPleno.php';
require_once dirname(__DIR__) . '/models/AcuerdoPleno.php';
require_once dirname(__DIR__) . '/models/CertificadoAcuerdoPleno.php';
require_once dirname(__DIR__) . '/services/ResumenEjecutivoPdfService.php';
require_once dirname(__DIR__) . '/models/ResumenEjecutivoPleno.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $auth = plenoRequireAuthorizedUser();

    if (!$auth['authorized'] || !plenoPuedeGenerarCertificados($auth['tipoUsuarioId'] ?? 0)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'mensaje' => 'No tiene permisos para generar resumenes ejecutivos.'], JSON_UNESCAPED_UNICODE);
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
    if ($idSesion <= 0) {
        $sesionActual = (new SesionPlenaria())->obtenerUltimaVigente();
        $idSesion = (int)($sesionActual['id_sesion'] ?? 0);
    }

    $modelo = new ResumenEjecutivoPleno();
    $resultado = $modelo->generarResumen($idSesion, (int)($_SESSION['idUsuario'] ?? 0));

    if (empty($resultado['success'])) {
        http_response_code(400);
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[ResumenEjecutivoAjax] exception=' . $e->getMessage() . ' file=' . $e->getFile() . ' line=' . $e->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'mensaje' => 'No fue posible generar el resumen ejecutivo.'], JSON_UNESCAPED_UNICODE);
}
