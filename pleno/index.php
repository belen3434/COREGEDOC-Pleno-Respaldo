<?php

require_once dirname(__DIR__) . '/app/config/Constants.php';
require_once dirname(__DIR__) . '/app/config/Database.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/models/SesionPlenaria.php';
require_once __DIR__ . '/models/TemaPleno.php';
require_once __DIR__ . '/controllers/PlenoController.php';

$plenoAuth = plenoRequireAuthorizedUser();

$vista = $_GET['vista'] ?? 'index';
$action = $_GET['action'] ?? null;
$tipoUsuarioId = (int)($plenoAuth['tipoUsuarioId'] ?? 0);
$rolesSoloVisualizacion = [1, 21, 22];

$vistasPermitidas = [
    'index' => __DIR__ . '/views/index.php',
    'sesiones' => __DIR__ . '/views/sesiones.php',
    'crear_sesion' => __DIR__ . '/views/crear_sesion.php',
    'editar_sesion' => __DIR__ . '/views/editar_sesion.php',
    'tabla' => __DIR__ . '/views/tabla.php',
    'configuracion' => __DIR__ . '/views/index.php',
    'comisiones' => __DIR__ . '/views/comisiones.php',
    'pleno_vivo' => __DIR__ . '/views/pleno_vivo.php',
    'votacion' => __DIR__ . '/views/votacion.php',
    'resumen' => __DIR__ . '/views/resumen.php',
    'ayuda' => __DIR__ . '/views/ayuda.php',
];

$vistasSoloVisualizacion = ['tabla', 'pleno_vivo', 'votacion', 'resumen'];

if (in_array($tipoUsuarioId, $rolesSoloVisualizacion, true) && !in_array($vista, $vistasSoloVisualizacion, true)) {
    header('Location: index.php?vista=tabla');
    exit();
}

$childView = $vistasPermitidas[$vista] ?? $vistasPermitidas['index'];
$plenoController = null;
$plenoFlash = null;
$plenoSesiones = [];
$plenoSesionActual = null;
$plenoNumeroSugerido = '';
$plenoCrudError = null;
$plenoComisionesActivas = [];
$plenoPuntosSesion = [];
$plenoTemasComisionSesion = [];
$plenoPuntosVariosSesion = [];
$plenoTotalConsejerosGobernador = 0;

if ($plenoAuth['authorized']) {
    try {
        $plenoController = new PlenoController(new SesionPlenaria(), new TemaPleno(), $plenoAuth);

        if ($action === 'listar_temas_comision') {
            header('Content-Type: application/json; charset=utf-8');

            $idComision = (int)($_GET['idComision'] ?? 0);
            $temas = $plenoController->listarTemasPorComision($idComision);

            echo json_encode([
                'success' => true,
                'temas' => $temas,
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $plenoController->manejarAccion($action);
        $plenoFlash = $plenoController->consumirFlash();
        $plenoNumeroSugerido = $plenoController->numeroSesionSugerido();

        if ($vista === 'sesiones') {
            $plenoSesiones = $plenoController->listarSesiones();
        }

        if ($vista === 'tabla' || $vista === 'pleno_vivo') {
            $plenoSesionActual = $plenoController->obtenerSesionVigenteHoy();
            if ($plenoSesionActual) {
                $plenoPuntosSesion = $plenoController->listarPuntosSesion((int)$plenoSesionActual['id_sesion']);
            }
        }

        if ($vista === 'pleno_vivo' && $plenoSesionActual) {
            $plenoTemasComisionSesion = $plenoController->listarTemasComisionSesion((int)$plenoSesionActual['id_sesion']);
            $plenoPuntosVariosSesion = $plenoController->listarPuntosVariosSesion((int)$plenoSesionActual['id_sesion']);
        }

        if ($vista === 'comisiones') {
            $idSesion = (int)($_GET['id_sesion'] ?? 0);
            $plenoSesionActual = $idSesion > 0
                ? $plenoController->obtenerSesion($idSesion)
                : $plenoController->obtenerUltimaSesionVigente();

            if ($plenoSesionActual) {
                $plenoTemasComisionSesion = $plenoController->listarTemasComisionSesion((int)$plenoSesionActual['id_sesion']);
                $plenoPuntosVariosSesion = $plenoController->listarPuntosVariosSesion((int)$plenoSesionActual['id_sesion']);
            }

            $plenoTotalConsejerosGobernador = $plenoController->contarConsejerosYGobernador();
        }

        if ($vista === 'editar_sesion') {
            $plenoSesionActual = $plenoController->obtenerSesion((int)($_GET['id'] ?? 0));
            if ($plenoSesionActual) {
                $plenoPuntosSesion = $plenoController->listarPuntosSesion((int)$plenoSesionActual['id_sesion']);
            }
        }

        if ($vista === 'crear_sesion' || $vista === 'editar_sesion') {
            $plenoComisionesActivas = $plenoController->listarComisionesActivas();
        }
    } catch (Throwable $e) {
        $plenoCrudError = 'No fue posible cargar los datos de sesiones plenarias. Verifique que la tabla exista.';
    }
}

$data = [
    'usuario' => [
        'nombre' => $_SESSION['pNombre'] ?? '',
        'apellido' => $_SESSION['aPaterno'] ?? '',
        'rol' => $_SESSION['tipoUsuario_id'] ?? 0,
    ],
    'pagina_actual' => 'pleno',
    'pleno_auth' => $plenoAuth,
    'pleno_puede_gestionar' => $plenoController ? $plenoController->puedeGestionar() : false,
    'pleno_flash' => $plenoFlash,
    'pleno_sesiones' => $plenoSesiones,
    'pleno_sesion_actual' => $plenoSesionActual,
    'pleno_numero_sugerido' => $plenoNumeroSugerido,
    'pleno_comisiones_activas' => $plenoComisionesActivas,
    'pleno_puntos_sesion' => $plenoPuntosSesion,
    'pleno_temas_comision_sesion' => $plenoTemasComisionSesion,
    'pleno_puntos_varios_sesion' => $plenoPuntosVariosSesion,
    'pleno_total_consejeros_gobernador' => $plenoTotalConsejerosGobernador,
    'pleno_crud_error' => $plenoCrudError,
];

if (!$plenoAuth['authorized']) {
    $childView = __DIR__ . '/views/acceso_denegado.php';
}

require_once dirname(__DIR__) . '/app/views/layouts/main.php';
