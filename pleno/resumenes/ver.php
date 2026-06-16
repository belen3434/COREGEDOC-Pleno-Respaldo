<?php

require_once dirname(__DIR__, 2) . '/app/config/Constants.php';
require_once dirname(__DIR__, 2) . '/app/config/Database.php';
require_once dirname(__DIR__) . '/helpers/auth.php';

use App\Config\Database;

function resumenEjecutivoMensaje(string $mensaje, int $codigo = 400): void
{
    http_response_code($codigo);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mensaje;
    exit();
}

try {
    $auth = plenoRequireAuthorizedUser();
    if (empty($auth['authorized'])) {
        resumenEjecutivoMensaje('No autorizado.', 403);
    }

    $idResumen = (int)($_GET['id_resumen'] ?? 0);
    if ($idResumen <= 0) {
        resumenEjecutivoMensaje('Resumen ejecutivo no valido.', 400);
    }

    $database = new Database();
    $conn = $database->getConnection();
    $stmt = $conn->prepare(
        'SELECT id_resumen, estado, nombre_archivo, path_archivo
         FROM pleno_resumenes_ejecutivos
         WHERE id_resumen = :id_resumen
         LIMIT 1'
    );
    $stmt->execute([':id_resumen' => $idResumen]);
    $resumen = $stmt->fetch();

    if (!is_array($resumen)) {
        resumenEjecutivoMensaje('El resumen ejecutivo solicitado no existe.', 404);
    }

    $estadoResumen = strtolower(trim((string)($resumen['estado'] ?? '')));
    if (!in_array($estadoResumen, ['vigente', 'reemplazado'], true)) {
        resumenEjecutivoMensaje('El resumen ejecutivo solicitado no esta disponible.', 403);
    }

    $pathArchivo = trim((string)($resumen['path_archivo'] ?? ''));
    if ($pathArchivo === '') {
        resumenEjecutivoMensaje('El resumen ejecutivo aun no tiene PDF generado.', 404);
    }

    $projectRoot = dirname(__DIR__, 2);
    $storageRoot = realpath(dirname(__DIR__) . '/storage/resumenes');
    $pathNormalizado = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pathArchivo);
    $esRutaAbsoluta = preg_match('/^[A-Za-z]:\\\\/', $pathNormalizado) === 1 || strpos($pathNormalizado, DIRECTORY_SEPARATOR) === 0;
    $rutaCandidata = $esRutaAbsoluta
        ? $pathNormalizado
        : $projectRoot . DIRECTORY_SEPARATOR . $pathNormalizado;
    $rutaFisica = realpath($rutaCandidata);

    if ($rutaFisica === false || !is_file($rutaFisica)) {
        resumenEjecutivoMensaje('El archivo del resumen ejecutivo no fue encontrado.', 404);
    }

    if ($storageRoot === false || strpos($rutaFisica, $storageRoot . DIRECTORY_SEPARATOR) !== 0) {
        resumenEjecutivoMensaje('La ruta del resumen ejecutivo no es valida.', 403);
    }

    $nombreArchivo = trim((string)($resumen['nombre_archivo'] ?? ''));
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
    resumenEjecutivoMensaje('No fue posible abrir el resumen ejecutivo.', 500);
}
