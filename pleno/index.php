<?php

$plenoEsListadoTemasComision = ($_GET['action'] ?? null) === 'listar_temas_comision';
if ($plenoEsListadoTemasComision) {
    ob_start();

    require_once dirname(__DIR__) . '/app/config/Constants.php';
    require_once dirname(__DIR__) . '/app/config/Database.php';
    require_once __DIR__ . '/helpers/auth.php';
    require_once __DIR__ . '/models/SesionPlenaria.php';

    try {
        $plenoAuthTemas = plenoRequireAuthorizedUser();

        if (ob_get_level() > 0) {
            ob_clean();
        }

        header('Content-Type: application/json; charset=utf-8');

        if (empty($plenoAuthTemas['authorized'])) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'No autorizado',
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $idComision = (int)($_GET['idComision'] ?? 0);
        $temas = (new SesionPlenaria())->listarTemasPorComision($idComision);

        echo json_encode([
            'success' => true,
            'temas' => $temas,
        ], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        if (ob_get_level() > 0) {
            ob_clean();
        }

        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'No fue posible cargar los temas',
        ], JSON_UNESCAPED_UNICODE);
    }

    exit();
}

require_once dirname(__DIR__) . '/app/config/Constants.php';
require_once dirname(__DIR__) . '/app/config/Database.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/votacion_pleno.php';
require_once __DIR__ . '/models/SesionPlenaria.php';
require_once __DIR__ . '/models/TemaPleno.php';
require_once __DIR__ . '/models/AsistenciaPleno.php';
require_once __DIR__ . '/models/ResumenPleno.php';
require_once __DIR__ . '/models/HistorialVotacionesPleno.php';
require_once __DIR__ . '/models/AcuerdoPleno.php';
require_once __DIR__ . '/models/CertificadoAcuerdoPleno.php';
require_once __DIR__ . '/controllers/PlenoController.php';

$plenoAuth = plenoRequireAuthorizedUser();

$vistaSolicitada = $_GET['vista'] ?? null;
$vista = $vistaSolicitada ?? 'index';
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
    'historial_votaciones' => __DIR__ . '/views/historial_votaciones.php',
    'ayuda' => __DIR__ . '/views/ayuda.php',
];

$vistasSoloVisualizacion = ['tabla', 'pleno_vivo', 'votacion', 'resumen', 'historial_votaciones'];

if ($tipoUsuarioId === 1 && ($vistaSolicitada === null || $vistaSolicitada === '' || in_array($vista, ['index', 'configuracion', 'tabla'], true))) {
    header('Location: index.php?vista=pleno_vivo');
    exit();
}

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
$plenoEstadoVotacionActual = 'pendiente';
$plenoAsistenciaUsuario = null;
$plenoAsistenciaResumen = null;
$plenoVotoUsuarioActual = null;
$plenoResumenResultados = [];
$plenoAcuerdos = [];
$plenoPuntosAcuerdo = [];
$plenoCertificadoAcuerdos = [
    'total_acuerdos' => 0,
    'existen_acuerdos' => false,
    'certificado' => null,
    'existe_certificado' => false,
];
$plenoHistorialVotaciones = [];
$plenoHistorialFiltros = [
    'numero_sesion' => '',
    'fecha' => '',
    'tipo_pleno' => 'todas',
    'resultado' => 'todas',
];

if ($plenoAuth['authorized']) {
    try {
        $plenoController = new PlenoController(new SesionPlenaria(), new TemaPleno(), $plenoAuth);
        $plenoAsistencia = new AsistenciaPleno();

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

            if (in_array((int)$tipoUsuarioId, [1, 22], true)) {
                $plenoAsistenciaUsuario = $plenoAsistencia->obtenerEstadoUsuario((int)($_SESSION['idUsuario'] ?? 0));
            }
        }

        if ($vista === 'pleno_vivo' && $plenoSesionActual) {
            $plenoTemasComisionSesion = $plenoController->listarTemasComisionSesion((int)$plenoSesionActual['id_sesion']);
            $plenoPuntosVariosSesion = $plenoController->listarPuntosVariosSesion((int)$plenoSesionActual['id_sesion']);
            $plenoEstadoVotacionActual = plenoObtenerEstadoVotacion(
                plenoVotacionConn(),
                (int)$plenoSesionActual['id_sesion'],
                trim((string)($plenoSesionActual['punto_actual'] ?? '1')) ?: '1'
            );
            $plenoVotoUsuarioActual = plenoObtenerVotoUsuarioPunto(
                plenoVotacionConn(),
                (int)$plenoSesionActual['id_sesion'],
                trim((string)($plenoSesionActual['punto_actual'] ?? '1')) ?: '1',
                (int)($_SESSION['idUsuario'] ?? 0)
            );
        }

        if ($vista === 'comisiones') {
            $idSesion = (int)($_GET['id_sesion'] ?? 0);
            $plenoSesionActual = $idSesion > 0
                ? $plenoController->obtenerSesion($idSesion)
                : $plenoController->obtenerUltimaSesionVigente();

            if ($plenoSesionActual) {
                $plenoTemasComisionSesion = $plenoController->listarTemasComisionSesion((int)$plenoSesionActual['id_sesion']);
                $plenoPuntosVariosSesion = $plenoController->listarPuntosVariosSesion((int)$plenoSesionActual['id_sesion']);
                $plenoAsistenciaResumen = $plenoAsistencia->obtenerResumenSesion((int)$plenoSesionActual['id_sesion']);
            }

            $plenoTotalConsejerosGobernador = $plenoController->contarConsejerosYGobernador();
        }

        if ($vista === 'resumen') {
            $idSesion = (int)($_GET['id_sesion'] ?? 0);
            $plenoSesionActual = $idSesion > 0
                ? $plenoController->obtenerSesion($idSesion)
                : $plenoController->obtenerUltimaSesionVigente();

            if ($plenoSesionActual) {
                $idSesionActual = (int)$plenoSesionActual['id_sesion'];
                $plenoAsistenciaResumen = $plenoAsistencia->obtenerResumenSesion($idSesionActual);
                $plenoResumen = new ResumenPleno();
                $plenoResumenResultados = $plenoResumen->obtenerResultadosPuntos($idSesionActual, $plenoSesionActual);
                $plenoAcuerdosModelo = new AcuerdoPleno();
                $plenoAcuerdos = $plenoAcuerdosModelo->listarPorSesion($idSesionActual);
                $plenoPuntosAcuerdo = $plenoAcuerdosModelo->listarPuntosSesion($idSesionActual, $plenoSesionActual);
                $plenoCertificadoModelo = new CertificadoAcuerdoPleno();
                $plenoTotalAcuerdosCertificado = $plenoCertificadoModelo->contarAcuerdosVigentes($idSesionActual);
                $plenoUltimoCertificado = $plenoCertificadoModelo->obtenerUltimoCertificado($idSesionActual);
                $plenoCertificadoAcuerdos = [
                    'total_acuerdos' => $plenoTotalAcuerdosCertificado,
                    'existen_acuerdos' => $plenoTotalAcuerdosCertificado > 0,
                    'certificado' => $plenoUltimoCertificado,
                    'existe_certificado' => $plenoUltimoCertificado !== null,
                ];
            }
        }

        if ($vista === 'historial_votaciones') {
            $plenoHistorialFiltros = [
                'numero_sesion' => trim((string)($_GET['numero_sesion'] ?? '')),
                'fecha' => trim((string)($_GET['fecha'] ?? '')),
                'tipo_pleno' => trim((string)($_GET['tipo_pleno'] ?? 'todas')),
                'resultado' => trim((string)($_GET['resultado'] ?? 'todas')),
                'q' => trim((string)($_GET['q'] ?? '')),
            ];
            $historialVotaciones = new HistorialVotacionesPleno();
            $plenoHistorialVotaciones = $historialVotaciones->listar($plenoHistorialFiltros);
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
    'pleno_estado_votacion_actual' => $plenoEstadoVotacionActual,
    'pleno_asistencia_usuario' => $plenoAsistenciaUsuario,
    'pleno_asistencia_resumen' => $plenoAsistenciaResumen,
    'pleno_voto_usuario_actual' => $plenoVotoUsuarioActual,
    'pleno_resumen_resultados' => $plenoResumenResultados,
    'pleno_acuerdos' => $plenoAcuerdos,
    'pleno_puntos_acuerdo' => $plenoPuntosAcuerdo,
    'pleno_certificado_acuerdos' => $plenoCertificadoAcuerdos,
    'pleno_historial_votaciones' => $plenoHistorialVotaciones,
    'pleno_historial_filtros' => $plenoHistorialFiltros,
    'pleno_crud_error' => $plenoCrudError,
];

if (!$plenoAuth['authorized']) {
    $childView = __DIR__ . '/views/acceso_denegado.php';
}

require_once dirname(__DIR__) . '/app/views/layouts/main.php';
