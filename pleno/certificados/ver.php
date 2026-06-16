<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';

use App\Config\Database;

function certificadoMensaje(string $mensaje, int $codigo = 400): void
{
    http_response_code($codigo);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mensaje;
    exit();
}

try {
    $auth = plenoRequireAuthorizedUser();
    if (empty($auth['authorized'])) {
        certificadoMensaje('No autorizado.', 403);
    }

    $idCertificado = (int)($_GET['id_certificado'] ?? 0);
    if ($idCertificado <= 0) {
        certificadoMensaje('Certificado no valido.', 400);
    }

    $database = new Database();
    $conn = $database->getConnection();
    $stmt = $conn->prepare(
        'SELECT id_certificado, estado, nombre_archivo, path_archivo
         FROM pleno_certificados_acuerdos
         WHERE id_certificado = :id_certificado
         LIMIT 1'
    );
    $stmt->execute([':id_certificado' => $idCertificado]);
    $certificado = $stmt->fetch();

    if (!is_array($certificado)) {
        certificadoMensaje('El certificado solicitado no existe.', 404);
    }

    if ((string)($certificado['estado'] ?? '') !== 'vigente') {
        certificadoMensaje('El certificado solicitado no esta vigente.', 403);
    }

    $pathArchivo = trim((string)($certificado['path_archivo'] ?? ''));
    if ($pathArchivo === '') {
        certificadoMensaje('El certificado aún no tiene PDF generado.', 404);
    }

    $projectRoot = dirname(__DIR__, 2);
    $storageRoot = realpath(dirname(__DIR__) . '/storage/certificados');
    $pathNormalizado = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pathArchivo);
    $esRutaAbsoluta = preg_match('/^[A-Za-z]:\\\\/', $pathNormalizado) === 1 || strpos($pathNormalizado, DIRECTORY_SEPARATOR) === 0;
    $rutaCandidata = $esRutaAbsoluta
        ? $pathNormalizado
        : $projectRoot . DIRECTORY_SEPARATOR . $pathNormalizado;
    $rutaFisica = realpath($rutaCandidata);

    if ($rutaFisica === false || !is_file($rutaFisica)) {
        certificadoMensaje('El archivo del certificado no fue encontrado.', 404);
    }

    if ($storageRoot === false || strpos($rutaFisica, $storageRoot . DIRECTORY_SEPARATOR) !== 0) {
        certificadoMensaje('La ruta del certificado no es valida.', 403);
    }

    $nombreArchivo = trim((string)($certificado['nombre_archivo'] ?? ''));
    if ($nombreArchivo === '') {
        $nombreArchivo = basename($rutaFisica);
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . addcslashes($nombreArchivo, '"\\') . '"');
    header('Content-Length: ' . filesize($rutaFisica));
    header('X-Content-Type-Options: nosniff');
    readfile($rutaFisica);
    exit();
} catch (Throwable $e) {
    certificadoMensaje('No fue posible abrir el certificado.', 500);
}
