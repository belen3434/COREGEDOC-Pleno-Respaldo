<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$sesion = $data['pleno_sesion_actual'] ?? null;
$temasComision = $data['pleno_temas_comision_sesion'] ?? [];
$puntosVarios = $data['pleno_puntos_varios_sesion'] ?? [];
$crudError = $data['pleno_crud_error'] ?? null;

$formatearFecha = static function (?string $fecha): string {
    if (!$fecha) {
        return '';
    }

    $timestamp = strtotime($fecha);
    return $timestamp ? date('d/m/Y', $timestamp) : $fecha;
};

$formatearHora = static function (?string $hora): string {
    if (!$hora) {
        return '';
    }

    return substr($hora, 0, 5) . ' hrs.';
};

$formatearTipo = static function (?string $tipo): string {
    $tipo = trim((string)$tipo);
    $tipoNormalizado = function_exists('mb_strtolower')
        ? mb_strtolower($tipo, 'UTF-8')
        : strtolower($tipo);

    if ($tipoNormalizado === 'normal' || $tipoNormalizado === 'ordinario') {
        return 'Ordinario';
    }

    if ($tipoNormalizado === 'extraordinario') {
        return 'Extraordinario';
    }

    return $tipo;
};

$formatearEstado = static function (?string $estado): string {
    $estado = trim((string)$estado);
    return $estado !== '' ? strtoupper(str_replace('_', ' ', $estado)) : 'SIN ESTADO';
};

$resolverPunto = static function (array $punto): array {
    $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));

    if ($tipoPunto === 'TABLA') {
        $titulo = trim((string)($punto['titulo_punto'] ?? ''));
        $descripcion = trim((string)($punto['descripcion_punto'] ?? ''));

        return [
            'tipo' => 'Punto de tabla',
            'titulo' => $titulo !== '' ? $titulo : 'Punto de Tabla',
            'tema' => '',
            'descripcion' => $descripcion,
            'origen' => 'Tabla',
        ];
    }

    if ($tipoPunto === 'IMPREVISTA') {
        $nombre = trim((string)($punto['nombre_imprevista'] ?? ''));
        $observacion = trim((string)($punto['observacion_imprevista'] ?? ''));

        return [
            'tipo' => 'Comisión imprevista',
            'titulo' => $nombre !== '' ? $nombre : 'Comisión Imprevista',
            'tema' => '',
            'descripcion' => $observacion,
            'origen' => 'Comisión Imprevista',
        ];
    }

    $comision = trim((string)($punto['nombreComision'] ?? ''));
    $tema = trim((string)($punto['nombreTema'] ?? ''));
    $objetivo = trim((string)($punto['objetivo'] ?? ''));

    return [
        'tipo' => 'Comisión',
        'titulo' => $comision !== '' ? $comision : 'Comisión',
        'tema' => $tema,
        'descripcion' => $objetivo,
        'origen' => $comision !== '' ? $comision : 'Comisión',
    ];
};

$aprobacionActas = trim((string)($sesion['aprobacion_actas'] ?? ''));
$observaciones = trim((string)($sesion['observaciones'] ?? ''));
$estadoSesion = trim((string)($sesion['estado'] ?? ''));
$tipoPleno = $formatearTipo($sesion['tipo_pleno'] ?? '');
$numeroSesion = trim((string)($sesion['numero_sesion'] ?? ''));
$tituloSesion = trim('Plenario ' . $tipoPleno . ($numeroSesion !== '' ? ' N° ' . $numeroSesion : ''));

$puntosOrdenDia = [];
$puntoActual = null;
$indicePuntoActual = 0;
if ($sesion) {
    $puntosOrdenDia[] = [
        'numero' => '1',
        'titulo' => 'Aprobación de Acta: ' . ($aprobacionActas !== '' ? $aprobacionActas : 'Sin acta registrada'),
        'tipo' => 'Aprobación de acta',
        'tema' => '',
        'descripcion' => '',
        'origen' => 'Sesión plenaria',
    ];
    $puntosOrdenDia[] = [
        'numero' => '2',
        'titulo' => 'Cuenta Presidente del Consejo Regional',
        'tipo' => 'Cuenta institucional',
        'tema' => '',
        'descripcion' => '',
        'origen' => 'Orden del día',
    ];
    $puntosOrdenDia[] = [
        'numero' => '3',
        'titulo' => 'Cuenta Comisiones',
        'tipo' => 'Sección',
        'tema' => '',
        'descripcion' => '',
        'origen' => 'Orden del día',
    ];

    foreach ($temasComision as $indiceTema => $tema) {
        $puntoRender = $resolverPunto($tema);
        $puntosOrdenDia[] = [
            'numero' => '3.' . ((int)$indiceTema + 1),
            'titulo' => $puntoRender['titulo'] . ($puntoRender['tema'] !== '' ? ' - Tema: ' . $puntoRender['tema'] : ''),
            'tipo' => $puntoRender['tipo'],
            'tema' => $puntoRender['tema'],
            'descripcion' => $puntoRender['descripcion'],
            'origen' => $puntoRender['origen'],
        ];
    }

    $puntosOrdenDia[] = [
        'numero' => '4',
        'titulo' => 'Varios',
        'tipo' => 'Sección',
        'tema' => '',
        'descripcion' => '',
        'origen' => 'Orden del día',
    ];

    foreach ($puntosVarios as $indiceVario => $puntoVario) {
        $puntoRender = $resolverPunto($puntoVario);
        $puntosOrdenDia[] = [
            'numero' => '4.' . ((int)$indiceVario + 1),
            'titulo' => $puntoRender['titulo'] . ($puntoRender['tema'] !== '' ? ' - Tema: ' . $puntoRender['tema'] : ''),
            'tipo' => $puntoRender['tipo'],
            'tema' => $puntoRender['tema'],
            'descripcion' => $puntoRender['descripcion'],
            'origen' => $puntoRender['origen'],
        ];
    }
}
$puntoActualGuardado = trim((string)($sesion['punto_actual'] ?? '1'));
if ($puntoActualGuardado === '') {
    $puntoActualGuardado = '1';
}

foreach ($puntosOrdenDia as $indicePunto => $puntoOrdenDia) {
    if (($puntoOrdenDia['numero'] ?? '') === $puntoActualGuardado) {
        $indicePuntoActual = (int)$indicePunto;
        break;
    }
}

if ($sesion && !empty($puntosOrdenDia) && function_exists('plenoObtenerEstadosVotacion')) {
    try {
        $estadosVotacion = plenoObtenerEstadosVotacion(plenoVotacionConn(), (int)($sesion['id_sesion'] ?? 0));

        foreach ($puntosOrdenDia as &$puntoOrdenDia) {
            $numeroPunto = trim((string)($puntoOrdenDia['numero'] ?? ''));
            $puntoOrdenDia['estado_votacion'] = plenoNormalizarEstadoVotacion(
                $estadosVotacion[$numeroPunto] ?? null,
                $numeroPunto
            );
        }
        unset($puntoOrdenDia);
    } catch (Throwable $e) {
        foreach ($puntosOrdenDia as &$puntoOrdenDia) {
            $numeroPunto = trim((string)($puntoOrdenDia['numero'] ?? ''));
            $puntoOrdenDia['estado_votacion'] = plenoPuntoPermiteVotacion($numeroPunto) ? 'pendiente' : 'no_vota';
        }
        unset($puntoOrdenDia);
    }
}

$puntoActual = $puntosOrdenDia[$indicePuntoActual] ?? ($puntosOrdenDia[0] ?? null);
$detallePuntoActual = $puntoActual;
?>

<style>
    .secretaria-pleno-view {
        min-height: calc(100vh - 130px);
        padding: 0.25rem 0 1.5rem;
        color: #203949;
        background: #f4f7f9;
    }

    .secretaria-hero,
    .secretaria-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15, 38, 56, 0.08);
    }

    .secretaria-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
        padding: 1.45rem 1.55rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    }

    .secretaria-live-label {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0.45rem;
        color: #c62828;
        font-size: 0.78rem;
        font-weight: 850;
        letter-spacing: 0.08em;
    }

    .secretaria-live-dot {
        width: 0.48rem;
        height: 0.48rem;
        border-radius: 999px;
        background: #dc3545;
        box-shadow: 0 0 0 5px rgba(220, 53, 69, 0.12);
    }

    .secretaria-title {
        margin: 0 0 0.55rem;
        color: #17324d;
        font-size: clamp(1.6rem, 2.4vw, 2.35rem);
        font-weight: 850;
        letter-spacing: 0;
    }

    .secretaria-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.55rem;
    }

    .secretaria-title-status {
        display: inline-flex;
        align-items: center;
        gap: 0.85rem;
        min-width: 0;
    }

    .secretaria-title-row .secretaria-title {
        margin-bottom: 0;
    }

    .secretaria-start-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 48px;
        padding: 0.72rem 1.15rem;
        border: 1px solid #198754;
        border-radius: 12px;
        color: #ffffff;
        background: #198754;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(25, 135, 84, 0.24);
        white-space: nowrap;
    }

    .secretaria-start-btn:hover,
    .secretaria-start-btn:focus {
        color: #ffffff;
        background: #157347;
        border-color: #157347;
    }

    .secretaria-session-name {
        margin-bottom: 0.85rem;
        color: #526979;
        font-size: 1.05rem;
        font-weight: 760;
    }

    .secretaria-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem 1.25rem;
        color: #5f7483;
        font-size: 0.96rem;
        font-weight: 650;
    }

    .secretaria-meta span,
    .agenda-subtext,
    .secretaria-card-title,
    .pending-state-icon,
    .latest-voter,
    .secretaria-control-btn {
        display: inline-flex;
        align-items: center;
    }

    .secretaria-meta span {
        gap: 0.45rem;
    }

    .secretaria-meta i,
    .secretaria-card-title i {
        color: #198754;
    }

    .secretaria-observations {
        max-width: 820px;
        margin-top: 0.9rem;
        padding-top: 0.85rem;
        border-top: 1px solid #e8eef2;
        color: #526979;
        font-weight: 600;
        line-height: 1.45;
    }

    .secretaria-status-badge,
    .secretaria-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        white-space: nowrap;
    }

    .secretaria-status-badge {
        min-width: 112px;
        padding: 0.55rem 0.9rem;
        border: 1px solid #b9ddc8;
        color: #12633d;
        background: #eaf7ef;
        font-size: 0.78rem;
        font-weight: 850;
        letter-spacing: 0.06em;
    }

    .secretaria-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(330px, 0.85fr);
        gap: 1.25rem;
        align-items: start;
    }

    .secretaria-stack {
        display: grid;
        gap: 1.25rem;
    }

    .secretaria-card {
        overflow: hidden;
    }

    .secretaria-card-body {
        padding: 1.25rem;
    }

    .secretaria-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .secretaria-card-title {
        gap: 0.55rem;
        margin: 0;
        color: #17324d;
        font-size: 0.9rem;
        font-weight: 850;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .agenda-list {
        display: grid;
        gap: 0.72rem;
    }

    .agenda-item {
        border: 1px solid #e4ebf0;
        border-radius: 14px;
        background: #ffffff;
    }

    .agenda-item-current {
        border-color: #8fd0a9;
        background: #eaf7ef;
        box-shadow: inset 4px 0 0 #198754;
    }

    .agenda-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 54px;
        padding: 0.85rem 1rem;
    }

    .agenda-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        color: #243f52;
        font-weight: 760;
    }

    .agenda-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        flex: 0 0 2rem;
        border-radius: 10px;
        color: #12633d;
        background: #eaf7ef;
        font-weight: 850;
    }

    .agenda-sublist {
        display: grid;
        gap: 0.5rem;
        padding: 0 1rem 1rem 3.75rem;
    }

    .agenda-subitem {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        min-height: 46px;
        padding: 0.72rem 0.85rem;
        border: 1px solid #e5edf2;
        border-radius: 12px;
        color: #314b5e;
        background: #fbfdfe;
        font-weight: 680;
    }

    .agenda-subitem-current {
        border-color: #8fd0a9;
        background: #eaf7ef;
        box-shadow: inset 4px 0 0 #198754;
    }

    .secretaria-control-btn:disabled {
        cursor: not-allowed;
        opacity: 0.55;
        box-shadow: none;
    }

    .agenda-subtext {
        gap: 0.55rem;
        min-width: 0;
    }

    .agenda-bullet {
        width: 0.55rem;
        height: 0.55rem;
        flex: 0 0 0.55rem;
        border-radius: 50%;
        background: #91a3af;
    }

    .agenda-subitem-current .agenda-bullet {
        background: #198754;
    }

    .agenda-empty {
        color: #657987;
        font-weight: 650;
        font-style: italic;
    }

    .secretaria-badge {
        padding: 0.34rem 0.66rem;
        font-size: 0.72rem;
        font-weight: 850;
        letter-spacing: 0.04em;
    }

    .badge-current {
        border: 1px solid #8fd0a9;
        color: #12633d;
        background: #dff3e7;
    }

    .badge-info {
        border: 1px solid #b8d9f3;
        color: #0b5f97;
        background: #e7f3fb;
    }

    .point-detail-title {
        margin: 0.7rem 0 0.35rem;
        color: #17324d;
        font-size: 1.55rem;
        font-weight: 850;
    }

    .point-detail-subtitle {
        margin-bottom: 0.75rem;
        color: #198754;
        font-weight: 760;
    }

    .point-detail-text {
        margin-bottom: 1rem;
        color: #526979;
        line-height: 1.55;
    }

    .point-detail-meta {
        display: grid;
        gap: 0.5rem;
        color: #415867;
        font-weight: 650;
    }

    .point-detail-meta strong {
        color: #17324d;
    }

    .secretaria-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
    }

    .secretaria-control-btn {
        justify-content: center;
        gap: 0.5rem;
        min-height: 48px;
        padding: 0.7rem 1rem;
        border-radius: 12px;
        font-weight: 760;
        box-shadow: 0 8px 16px rgba(15, 38, 56, 0.06);
    }

    .secretaria-control-btn.btn-outline-success {
        border-color: #198754;
        color: #12633d;
        background: #ffffff;
    }

    .secretaria-control-btn.btn-success {
        border-color: #198754;
        background: #198754;
    }

    .secretaria-control-btn.btn-danger {
        border-color: #dc3545;
        background: #dc3545;
    }

    .pending-state {
        min-height: 150px;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid #e4ebf0;
        border-radius: 14px;
        background: #fbfdfe;
    }

    .pending-state-icon {
        justify-content: center;
        width: 3rem;
        height: 3rem;
        flex: 0 0 3rem;
        border-radius: 50%;
        color: #198754;
        background: #e5f5eb;
        font-size: 1.15rem;
    }

    .pending-state-title {
        margin-bottom: 0.25rem;
        color: #17324d;
        font-size: 1.08rem;
        font-weight: 850;
    }

    .pending-state-text {
        margin: 0;
        color: #657987;
        line-height: 1.45;
        font-weight: 620;
    }

    @media (max-width: 1199.98px) {
        .secretaria-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .secretaria-hero,
        .secretaria-card-body {
            padding: 1rem;
        }

        .secretaria-hero {
            display: block;
        }

        .secretaria-title-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .secretaria-start-btn {
            margin-top: 0.2rem;
        }

        .secretaria-status-badge {
            margin-top: 0;
        }

        .agenda-sublist {
            padding-left: 1rem;
        }
    }
</style>

<div class="secretaria-pleno-view">
    <?php if ($crudError): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($crudError, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if (!$crudError && !$sesion): ?>
        <div class="alert alert-warning" role="alert">
            No existe una sesión plenaria vigente para mostrar.
        </div>
    <?php elseif ($sesion): ?>
        <div class="secretaria-hero">
            <div class="w-100">
                <div class="secretaria-live-label">
                    <span class="secretaria-live-dot"></span>
                    <span>SESIÓN EN VIVO</span>
                </div>
                <div class="secretaria-title-row">
                    <div class="secretaria-title-status">
                        <h1 class="secretaria-title">Sesión del Día</h1>
                        <span class="secretaria-status-badge" id="plenoSessionStatusBadge">
                            <?php echo htmlspecialchars($formatearEstado($sesion['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                    <?php if ($estadoSesion === 'programada'): ?>
                        <button type="button" class="secretaria-start-btn" id="plenoStartSessionBtn" data-id-sesion="<?php echo (int)($sesion['id_sesion'] ?? 0); ?>">
                            <i class="fas fa-play"></i>
                            Iniciar Pleno
                        </button>
                    <?php endif; ?>
                </div>
                <div class="alert alert-success mt-3 mb-0 d-none" id="plenoStartSessionMessage" role="alert">
                    Pleno iniciado correctamente.
                </div>
                <div class="secretaria-session-name">
                    <?php echo htmlspecialchars($tituloSesion, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="secretaria-meta">
                    <span><i class="fas fa-calendar-alt"></i><?php echo htmlspecialchars($formatearFecha($sesion['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fas fa-clock"></i><?php echo htmlspecialchars($formatearHora($sesion['hora'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars((string)($sesion['lugar'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <?php if ($observaciones !== ''): ?>
                    <div class="secretaria-observations">
                        <strong>Observaciones:</strong>
                        <?php echo nl2br(htmlspecialchars($observaciones, ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="secretaria-grid">
            <div class="secretaria-stack">
                <section class="secretaria-card">
                    <div class="secretaria-card-body">
                        <div class="secretaria-card-header">
                            <h2 class="secretaria-card-title">
                                <i class="fas fa-list-check"></i>
                                ORDEN DEL DÍA
                            </h2>
                        </div>

                        <div class="agenda-list">
                            <article class="agenda-item<?php echo $puntoActual && $puntoActual['numero'] === '1' ? ' agenda-item-current' : ''; ?>" data-agenda-number="1">
                                <div class="agenda-main">
                                    <div class="agenda-label">
                                        <span class="agenda-number">1</span>
                                        <span>Aprobación de Acta: <?php echo htmlspecialchars($aprobacionActas !== '' ? $aprobacionActas : 'Sin acta registrada', ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <?php if ($puntoActual && $puntoActual['numero'] === '1'): ?>
                                        <span class="secretaria-badge badge-current" data-current-badge="true">PUNTO ACTUAL</span>
                                    <?php endif; ?>
                                </div>
                            </article>

                            <article class="agenda-item<?php echo $puntoActual && $puntoActual['numero'] === '2' ? ' agenda-item-current' : ''; ?>" data-agenda-number="2">
                                <div class="agenda-main">
                                    <div class="agenda-label">
                                        <span class="agenda-number">2</span>
                                        <span>Cuenta Presidente del Consejo Regional</span>
                                    </div>
                                    <?php if ($puntoActual && $puntoActual['numero'] === '2'): ?>
                                        <span class="secretaria-badge badge-current" data-current-badge="true">PUNTO ACTUAL</span>
                                    <?php endif; ?>
                                </div>
                            </article>

                            <article class="agenda-item<?php echo $puntoActual && $puntoActual['numero'] === '3' ? ' agenda-item-current' : ''; ?>" data-agenda-number="3">
                                <div class="agenda-main">
                                    <div class="agenda-label">
                                        <span class="agenda-number">3</span>
                                        <span>Cuenta Comisiones</span>
                                    </div>
                                    <?php if ($puntoActual && $puntoActual['numero'] === '3'): ?>
                                        <span class="secretaria-badge badge-current" data-current-badge="true">PUNTO ACTUAL</span>
                                    <?php endif; ?>
                                </div>
                                <div class="agenda-sublist">
                                    <?php if (empty($temasComision)): ?>
                                        <div class="agenda-subitem agenda-empty">
                                            No hay temas de comisión cargados para esta sesión.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($temasComision as $indiceTema => $tema): ?>
                                            <?php
                                            $puntoRender = $resolverPunto($tema);
                                            $numeroPunto = '3.' . ((int)$indiceTema + 1);
                                            $esActual = $puntoActual && $puntoActual['numero'] === $numeroPunto;
                                            ?>
                                            <div class="agenda-subitem<?php echo $esActual ? ' agenda-subitem-current' : ''; ?>" data-agenda-number="<?php echo htmlspecialchars($numeroPunto, ENT_QUOTES, 'UTF-8'); ?>">
                                                <span class="agenda-subtext">
                                                    <span class="agenda-bullet"></span>
                                                    <span><?php echo htmlspecialchars($numeroPunto . ' ' . $puntoRender['titulo'] . ($puntoRender['tema'] !== '' ? ' - Tema: ' . $puntoRender['tema'] : ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                                </span>
                                                <?php if ($esActual): ?>
                                                    <span class="secretaria-badge badge-current" data-current-badge="true">PUNTO ACTUAL</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </article>

                            <article class="agenda-item<?php echo $puntoActual && $puntoActual['numero'] === '4' ? ' agenda-item-current' : ''; ?>" data-agenda-number="4">
                                <div class="agenda-main">
                                    <div class="agenda-label">
                                        <span class="agenda-number">4</span>
                                        <span>Varios</span>
                                    </div>
                                    <?php if ($puntoActual && $puntoActual['numero'] === '4'): ?>
                                        <span class="secretaria-badge badge-current" data-current-badge="true">PUNTO ACTUAL</span>
                                    <?php endif; ?>
                                </div>
                                <div class="agenda-sublist">
                                    <?php if (empty($puntosVarios)): ?>
                                        <div class="agenda-subitem agenda-empty">
                                            No hay puntos varios cargados para esta sesión.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($puntosVarios as $indiceVario => $puntoVario): ?>
                                            <?php
                                            $puntoRender = $resolverPunto($puntoVario);
                                            $numeroPunto = '4.' . ((int)$indiceVario + 1);
                                            $esActual = $puntoActual && $puntoActual['numero'] === $numeroPunto;
                                            ?>
                                            <div class="agenda-subitem<?php echo $esActual ? ' agenda-subitem-current' : ''; ?>" data-agenda-number="<?php echo htmlspecialchars($numeroPunto, ENT_QUOTES, 'UTF-8'); ?>">
                                                <span class="agenda-subtext">
                                                    <span class="agenda-bullet"></span>
                                                    <span><?php echo htmlspecialchars($numeroPunto . ' ' . $puntoRender['titulo'] . ($puntoRender['tema'] !== '' ? ' - Tema: ' . $puntoRender['tema'] : ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                                </span>
                                                <?php if ($esActual): ?>
                                                    <span class="secretaria-badge badge-current" data-current-badge="true">PUNTO ACTUAL</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="secretaria-card">
                    <div class="secretaria-card-body">
                        <div class="secretaria-card-header">
                            <h2 class="secretaria-card-title">
                                <i class="fas fa-file-lines"></i>
                                DETALLE DEL PUNTO ACTUAL
                            </h2>
                        </div>

                        <?php if ($detallePuntoActual): ?>
                            <span class="secretaria-badge badge-info" id="plenoCurrentPointNumber">PUNTO <?php echo htmlspecialchars((string)$puntoActual['numero'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <h2 class="point-detail-title" id="plenoCurrentPointTitle"><?php echo htmlspecialchars($detallePuntoActual['titulo'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <div class="point-detail-subtitle" id="plenoCurrentPointTopic" <?php echo $detallePuntoActual['tema'] !== '' ? '' : 'hidden'; ?>>Tema: <?php echo htmlspecialchars($detallePuntoActual['tema'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <p class="point-detail-text" id="plenoCurrentPointDescription" <?php echo $detallePuntoActual['descripcion'] !== '' ? '' : 'hidden'; ?>>
                                <?php echo nl2br(htmlspecialchars($detallePuntoActual['descripcion'], ENT_QUOTES, 'UTF-8')); ?>
                            </p>
                            <div class="point-detail-meta">
                                <div><strong>Tipo de punto:</strong> <span id="plenoCurrentPointType"><?php echo htmlspecialchars($detallePuntoActual['tipo'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                                <div><strong>Origen:</strong> <span id="plenoCurrentPointOrigin"><?php echo htmlspecialchars($detallePuntoActual['origen'], ENT_QUOTES, 'UTF-8'); ?></span></div>
                            </div>
                        <?php else: ?>
                            <div class="pending-state">
                                <span class="pending-state-icon"><i class="fas fa-list"></i></span>
                                <div>
                                    <div class="pending-state-title">Sin punto actual</div>
                                    <p class="pending-state-text">La sesión no tiene puntos disponibles para mostrar.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="secretaria-card">
                    <div class="secretaria-card-body">
                        <div class="secretaria-card-header">
                            <h2 class="secretaria-card-title">
                                <i class="fas fa-sliders"></i>
                                CONTROLES DE LA SECRETARÍA
                            </h2>
                        </div>

                        <div class="secretaria-controls">
                            <button type="button" class="btn btn-outline-success secretaria-control-btn pleno-session-action-btn" id="plenoPrevPointBtn" <?php echo $estadoSesion === 'finalizada' ? 'disabled' : ''; ?>>
                                <i class="fas fa-arrow-left"></i>
                                Punto anterior
                            </button>
                            <button type="button" class="btn btn-outline-success secretaria-control-btn pleno-session-action-btn" id="plenoNextPointBtn" <?php echo $estadoSesion === 'finalizada' ? 'disabled' : ''; ?>>
                                Siguiente punto
                                <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="button" class="btn btn-success secretaria-control-btn pleno-session-action-btn" id="plenoStartVoteBtn" <?php echo $estadoSesion !== 'en_curso' ? 'disabled' : ''; ?>>
                                <i class="fas fa-play"></i>
                                Iniciar votación
                            </button>
                            <button type="button" class="btn btn-danger secretaria-control-btn pleno-session-action-btn" id="plenoCloseVoteBtn" <?php echo $estadoSesion !== 'en_curso' ? 'disabled' : ''; ?>>
                                <i class="fas fa-stop"></i>
                                Cerrar votación
                            </button>
                            <button type="button" class="btn btn-outline-secondary secretaria-control-btn pleno-session-action-btn" id="plenoFinishSessionBtn" data-id-sesion="<?php echo (int)($sesion['id_sesion'] ?? 0); ?>" <?php echo $estadoSesion !== 'en_curso' ? 'disabled' : ''; ?>>
                                <i class="fas fa-flag-checkered"></i>
                                Finalizar pleno
                            </button>
                        </div>
                        <div class="alert alert-info mt-3 mb-0" id="plenoVoteControlMessage" role="status">
                            No hay votación activa.
                        </div>
                    </div>
                </section>
            </div>

            <aside class="secretaria-stack">
                <section class="secretaria-card">
                    <div class="secretaria-card-body">
                        <div class="secretaria-card-header">
                            <h2 class="secretaria-card-title">
                                <i class="fas fa-users"></i>
                                ASISTENCIA
                            </h2>
                        </div>
                        <div class="pending-state">
                            <span class="pending-state-icon"><i class="fas fa-user-clock"></i></span>
                            <div>
                                <div class="pending-state-title">Asistencia pendiente</div>
                                <p class="pending-state-text">Aún no se han registrado asistentes para esta sesión.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="secretaria-card">
                    <div class="secretaria-card-body">
                        <div class="secretaria-card-header">
                            <h2 class="secretaria-card-title">
                                <i class="fas fa-chart-simple"></i>
                                VOTACIÓN EN CURSO
                            </h2>
                        </div>
                        <div class="pending-state">
                            <span class="pending-state-icon"><i class="fas fa-square-poll-vertical"></i></span>
                            <div>
                                <div class="pending-state-title">No hay votación activa</div>
                                <p class="pending-state-text">Cuando la Secretaría inicie una votación, aquí se mostrarán los resultados en tiempo real.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="secretaria-card">
                    <div class="secretaria-card-body">
                        <div class="secretaria-card-header">
                            <h2 class="secretaria-card-title">
                                <i class="fas fa-clock-rotate-left"></i>
                                ÚLTIMOS VOTOS RECIBIDOS
                            </h2>
                        </div>
                        <div class="pending-state">
                            <span class="pending-state-icon"><i class="fas fa-inbox"></i></span>
                            <div>
                                <div class="pending-state-title">Sin votos registrados</div>
                                <p class="pending-state-text">Los votos emitidos aparecerán aquí una vez iniciada la votación.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    <?php endif; ?>
</div>

<?php if ($sesion): ?>
    <script>
        (function () {
            "use strict";

            var iniciarBtn = document.getElementById("plenoStartSessionBtn");
            var estadoBadge = document.getElementById("plenoSessionStatusBadge");
            var mensajeInicio = document.getElementById("plenoStartSessionMessage");
            var iniciarPlenoUrl = "ajax/iniciar_pleno.php";

            if (!iniciarBtn) {
                return;
            }

            function mostrarMensaje(texto, tipo) {
                if (!mensajeInicio) {
                    return;
                }

                mensajeInicio.textContent = texto;
                mensajeInicio.classList.remove("d-none", "alert-success", "alert-danger");
                mensajeInicio.classList.add(tipo === "error" ? "alert-danger" : "alert-success");
            }

            iniciarBtn.addEventListener("click", function () {
                var idSesion = parseInt(iniciarBtn.getAttribute("data-id-sesion") || "0", 10);
                var confirmado = window.confirm("¿Desea iniciar el pleno? Desde este momento los usuarios podrán visualizar el pleno en vivo.");

                if (!confirmado || !idSesion) {
                    return;
                }

                iniciarBtn.disabled = true;

                fetch(iniciarPlenoUrl, {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id_sesion: idSesion
                    })
                }).then(function (response) {
                    return response.json().then(function (payload) {
                        if (!response.ok || !payload || payload.success !== true) {
                            throw new Error(payload && payload.mensaje ? payload.mensaje : "No fue posible iniciar el pleno.");
                        }

                        return payload;
                    });
                }).then(function (payload) {
                    if (estadoBadge) {
                        estadoBadge.textContent = "EN CURSO";
                    }

                    iniciarBtn.classList.add("d-none");
                    document.querySelectorAll(".pleno-session-action-btn").forEach(function (boton) {
                        boton.disabled = false;
                    });
                    mostrarMensaje(payload.mensaje || "Pleno iniciado correctamente.", "success");
                }).catch(function (error) {
                    iniciarBtn.disabled = false;
                    mostrarMensaje(error.message || "No fue posible iniciar el pleno.", "error");
                });
            });
        }());
    </script>
<?php endif; ?>

<?php if ($sesion && !empty($puntosOrdenDia) && $estadoSesion !== 'finalizada'): ?>
    <script>
        (function () {
            "use strict";

            var puntosOrdenDia = <?php echo json_encode($puntosOrdenDia, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            var idSesion = <?php echo (int)($sesion['id_sesion'] ?? 0); ?>;
            var indiceActual = <?php echo (int)$indicePuntoActual; ?>;
            var sesionEnCurso = <?php echo $estadoSesion === 'en_curso' ? 'true' : 'false'; ?>;
            var actualizarPuntoUrl = "ajax/actualizar_punto_actual.php";
            var iniciarVotacionUrl = "ajax/iniciar_votacion.php";
            var cerrarVotacionUrl = "ajax/cerrar_votacion.php";

            function limpiarListeners(id) {
                var original = document.getElementById(id);
                if (!original) {
                    return null;
                }
                var limpio = original.cloneNode(true);
                original.parentNode.replaceChild(limpio, original);
                return limpio;
            }

            var btnAnterior = limpiarListeners("plenoPrevPointBtn");
            var btnSiguiente = limpiarListeners("plenoNextPointBtn");
            var btnIniciarVotacion = limpiarListeners("plenoStartVoteBtn");
            var btnCerrarVotacion = limpiarListeners("plenoCloseVoteBtn");
            var mensajeVotacion = document.getElementById("plenoVoteControlMessage");
            var detalleNumero = document.getElementById("plenoCurrentPointNumber");
            var detalleTitulo = document.getElementById("plenoCurrentPointTitle");
            var detalleTema = document.getElementById("plenoCurrentPointTopic");
            var detalleDescripcion = document.getElementById("plenoCurrentPointDescription");
            var detalleTipo = document.getElementById("plenoCurrentPointType");
            var detalleOrigen = document.getElementById("plenoCurrentPointOrigin");

            function buscarElementoPunto(numero) {
                var encontrado = null;
                document.querySelectorAll("[data-agenda-number]").forEach(function (elemento) {
                    if (!encontrado && elemento.getAttribute("data-agenda-number") === numero) {
                        encontrado = elemento;
                    }
                });
                return encontrado;
            }

            function crearBadgeActual() {
                var badge = document.createElement("span");
                badge.className = "secretaria-badge badge-current";
                badge.setAttribute("data-current-badge", "true");
                badge.textContent = "PUNTO ACTUAL";
                return badge;
            }

            function limpiarPuntoActual() {
                document.querySelectorAll(".agenda-item-current, .agenda-subitem-current").forEach(function (elemento) {
                    elemento.classList.remove("agenda-item-current", "agenda-subitem-current");
                });
                document.querySelectorAll("[data-current-badge='true']").forEach(function (badge) {
                    badge.remove();
                });
            }

            function obtenerPuntoActual() {
                return puntosOrdenDia[indiceActual] || null;
            }

            function puntoPermiteVotacion(punto) {
                var numero = punto ? String(punto.numero || "") : "";
                return numero === "1" || /^3\.\d+$/.test(numero);
            }

            function mostrarMensajeVotacion(texto, tipo) {
                if (!mensajeVotacion) {
                    return;
                }
                mensajeVotacion.textContent = texto;
                mensajeVotacion.classList.remove("alert-info", "alert-success", "alert-warning", "alert-danger");
                mensajeVotacion.classList.add(tipo || "alert-info");
            }

            function actualizarDetalle(punto) {
                if (detalleNumero) {
                    detalleNumero.textContent = "PUNTO " + (punto.numero || "");
                }
                if (detalleTitulo) {
                    detalleTitulo.textContent = punto.titulo || "Sin titulo";
                }
                if (detalleTema) {
                    var tema = punto.tema || "";
                    detalleTema.hidden = tema === "";
                    detalleTema.textContent = tema !== "" ? "Tema: " + tema : "";
                }
                if (detalleDescripcion) {
                    var descripcion = punto.descripcion || "";
                    detalleDescripcion.hidden = descripcion === "";
                    detalleDescripcion.textContent = descripcion;
                }
                if (detalleTipo) {
                    detalleTipo.textContent = punto.tipo || "Sin tipo";
                }
                if (detalleOrigen) {
                    detalleOrigen.textContent = punto.origen || "Sin origen";
                }
            }

            function actualizarControles() {
                var punto = obtenerPuntoActual();
                var permiteVotacion = puntoPermiteVotacion(punto);
                var estadoVotacion = punto && punto.estado_votacion ? punto.estado_votacion : "pendiente";

                if (btnAnterior) {
                    btnAnterior.disabled = indiceActual <= 0;
                }
                if (btnSiguiente) {
                    btnSiguiente.disabled = indiceActual >= puntosOrdenDia.length - 1;
                }
                if (!btnIniciarVotacion || !btnCerrarVotacion) {
                    return;
                }
                if (!sesionEnCurso) {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = true;
                    mostrarMensajeVotacion("Debe iniciar el pleno antes de abrir una votación.", "alert-info");
                    return;
                }
                if (!permiteVotacion) {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = true;
                    mostrarMensajeVotacion("Este punto no requiere votación.", "alert-info");
                    return;
                }
                if (estadoVotacion === "votacion_en_curso") {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = false;
                    mostrarMensajeVotacion("Votación en curso.", "alert-success");
                    return;
                }
                if (estadoVotacion === "votacion_cerrada") {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = true;
                    mostrarMensajeVotacion("Votación cerrada.", "alert-warning");
                    return;
                }
                btnIniciarVotacion.disabled = false;
                btnCerrarVotacion.disabled = true;
                mostrarMensajeVotacion("No hay votación activa.", "alert-info");
            }

            function actualizarVista() {
                var punto = obtenerPuntoActual();
                var elemento;
                if (!punto) {
                    return;
                }
                limpiarPuntoActual();
                elemento = buscarElementoPunto(String(punto.numero || ""));
                if (elemento) {
                    if (elemento.classList.contains("agenda-item")) {
                        elemento.classList.add("agenda-item-current");
                        (elemento.querySelector(".agenda-main") || elemento).appendChild(crearBadgeActual());
                    } else {
                        elemento.classList.add("agenda-subitem-current");
                        elemento.appendChild(crearBadgeActual());
                    }
                }
                actualizarDetalle(punto);
                actualizarControles();
            }

            function guardarPuntoActual(punto) {
                if (!punto || !idSesion) {
                    return Promise.resolve();
                }
                return fetch(actualizarPuntoUrl, {
                    method: "POST",
                    headers: { "Accept": "application/json", "Content-Type": "application/json" },
                    body: JSON.stringify({ id_sesion: idSesion, punto_actual: String(punto.numero || "") })
                });
            }

            function navegarPunto(direccion) {
                var nuevoIndice = indiceActual + direccion;
                if (nuevoIndice < 0 || nuevoIndice >= puntosOrdenDia.length) {
                    return;
                }
                indiceActual = nuevoIndice;
                actualizarVista();
                guardarPuntoActual(obtenerPuntoActual()).catch(function (error) {
                    if (window.console) {
                        window.console.error(error);
                    }
                });
            }

            function ejecutarAccionVotacion(url, mensajeError) {
                return fetch(url, {
                    method: "POST",
                    headers: { "Accept": "application/json", "Content-Type": "application/json" },
                    body: JSON.stringify({ id_sesion: idSesion })
                }).then(function (response) {
                    return response.json().then(function (payload) {
                        if (!response.ok || !payload || payload.success !== true) {
                            throw new Error(payload && payload.mensaje ? payload.mensaje : mensajeError);
                        }
                        return payload;
                    });
                });
            }

            function obtenerVotacionActiva() {
                var punto = obtenerPuntoActual();
                return punto && punto.estado_votacion === "votacion_en_curso" ? punto : null;
            }

            function iniciarVotacion() {
                var punto = obtenerPuntoActual();
                if (!puntoPermiteVotacion(punto)) {
                    mostrarMensajeVotacion("Este punto no requiere votación.", "alert-info");
                    return;
                }
                ejecutarAccionVotacion(iniciarVotacionUrl, "No fue posible iniciar la votación.").then(function () {
                    punto.estado_votacion = "votacion_en_curso";
                    actualizarControles();
                }).catch(function (error) {
                    mostrarMensajeVotacion(error.message || "No fue posible iniciar la votación.", "alert-danger");
                    actualizarControles();
                });
            }

            function cerrarVotacion() {
                var punto = obtenerVotacionActiva();
                if (!punto) {
                    mostrarMensajeVotacion("No hay votación activa.", "alert-warning");
                    return;
                }
                ejecutarAccionVotacion(cerrarVotacionUrl, "No fue posible cerrar la votación.").then(function () {
                    punto.estado_votacion = "votacion_cerrada";
                    actualizarControles();
                }).catch(function (error) {
                    mostrarMensajeVotacion(error.message || "No fue posible cerrar la votación.", "alert-danger");
                    actualizarControles();
                });
            }

            if (btnAnterior) {
                btnAnterior.addEventListener("click", function () { navegarPunto(-1); });
            }
            if (btnSiguiente) {
                btnSiguiente.addEventListener("click", function () { navegarPunto(1); });
            }
            if (btnIniciarVotacion) {
                btnIniciarVotacion.addEventListener("click", iniciarVotacion);
            }
            if (btnCerrarVotacion) {
                btnCerrarVotacion.addEventListener("click", cerrarVotacion);
            }
            document.addEventListener("pleno:estado-en-curso", function () {
                sesionEnCurso = true;
                actualizarControles();
            });
            actualizarVista();
        }());
    </script>
<?php endif; ?>

<?php if ($sesion && !empty($puntosOrdenDia) && $estadoSesion !== 'finalizada'): ?>
    <script>
        (function () {
            "use strict";

            var puntosOrdenDia = <?php echo json_encode($puntosOrdenDia, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            var idSesion = <?php echo (int)($sesion['id_sesion'] ?? 0); ?>;
            var indiceActual = <?php echo (int)$indicePuntoActual; ?>;
            var sesionEnCurso = <?php echo $estadoSesion === 'en_curso' ? 'true' : 'false'; ?>;
            var actualizarPuntoUrl = "ajax/actualizar_punto_actual.php";
            var iniciarVotacionUrl = "ajax/iniciar_votacion.php";
            var cerrarVotacionUrl = "ajax/cerrar_votacion.php";

            function limpiarListeners(id) {
                var original = document.getElementById(id);
                if (!original) {
                    return null;
                }
                var limpio = original.cloneNode(true);
                original.parentNode.replaceChild(limpio, original);
                return limpio;
            }

            var btnAnterior = limpiarListeners("plenoPrevPointBtn");
            var btnSiguiente = limpiarListeners("plenoNextPointBtn");
            var btnIniciarVotacion = limpiarListeners("plenoStartVoteBtn");
            var btnCerrarVotacion = limpiarListeners("plenoCloseVoteBtn");
            var mensajeVotacion = document.getElementById("plenoVoteControlMessage");
            var detalleNumero = document.getElementById("plenoCurrentPointNumber");
            var detalleTitulo = document.getElementById("plenoCurrentPointTitle");
            var detalleTema = document.getElementById("plenoCurrentPointTopic");
            var detalleDescripcion = document.getElementById("plenoCurrentPointDescription");
            var detalleTipo = document.getElementById("plenoCurrentPointType");
            var detalleOrigen = document.getElementById("plenoCurrentPointOrigin");

            function buscarElementoPunto(numero) {
                var encontrado = null;
                document.querySelectorAll("[data-agenda-number]").forEach(function (elemento) {
                    if (!encontrado && elemento.getAttribute("data-agenda-number") === numero) {
                        encontrado = elemento;
                    }
                });
                return encontrado;
            }

            function crearBadgeActual() {
                var badge = document.createElement("span");
                badge.className = "secretaria-badge badge-current";
                badge.setAttribute("data-current-badge", "true");
                badge.textContent = "PUNTO ACTUAL";
                return badge;
            }

            function limpiarPuntoActual() {
                document.querySelectorAll(".agenda-item-current, .agenda-subitem-current").forEach(function (elemento) {
                    elemento.classList.remove("agenda-item-current", "agenda-subitem-current");
                });
                document.querySelectorAll("[data-current-badge='true']").forEach(function (badge) {
                    badge.remove();
                });
            }

            function marcarPuntoActual(punto) {
                var elemento = buscarElementoPunto(String(punto.numero || ""));
                if (!elemento) {
                    return;
                }

                if (elemento.classList.contains("agenda-item")) {
                    var main = elemento.querySelector(".agenda-main");
                    elemento.classList.add("agenda-item-current");
                    if (main) {
                        main.appendChild(crearBadgeActual());
                    }
                    return;
                }

                elemento.classList.add("agenda-subitem-current");
                elemento.appendChild(crearBadgeActual());
            }

            function obtenerPuntoActual() {
                return puntosOrdenDia[indiceActual] || null;
            }

            function puntoPermiteVotacion(punto) {
                var numero = punto ? String(punto.numero || "") : "";
                return numero === "1" || /^3\.\d+$/.test(numero);
            }

            function mostrarMensajeVotacion(texto, tipo) {
                if (!mensajeVotacion) {
                    return;
                }
                mensajeVotacion.textContent = texto;
                mensajeVotacion.classList.remove("alert-info", "alert-success", "alert-warning", "alert-danger");
                mensajeVotacion.classList.add(tipo || "alert-info");
            }

            function actualizarDetalle(punto) {
                if (detalleNumero) {
                    detalleNumero.textContent = "PUNTO " + (punto.numero || "");
                }
                if (detalleTitulo) {
                    detalleTitulo.textContent = punto.titulo || "Sin titulo";
                }
                if (detalleTema) {
                    var tema = punto.tema || "";
                    detalleTema.hidden = tema === "";
                    detalleTema.textContent = tema !== "" ? "Tema: " + tema : "";
                }
                if (detalleDescripcion) {
                    var descripcion = punto.descripcion || "";
                    detalleDescripcion.hidden = descripcion === "";
                    detalleDescripcion.textContent = descripcion;
                }
                if (detalleTipo) {
                    detalleTipo.textContent = punto.tipo || "Sin tipo";
                }
                if (detalleOrigen) {
                    detalleOrigen.textContent = punto.origen || "Sin origen";
                }
            }

            function actualizarControlesNavegacion() {
                if (btnAnterior) {
                    btnAnterior.disabled = indiceActual <= 0;
                }
                if (btnSiguiente) {
                    btnSiguiente.disabled = indiceActual >= puntosOrdenDia.length - 1;
                }
            }

            function obtenerVotacionActiva() {
                var punto = obtenerPuntoActual();
                return punto && punto.estado_votacion === "votacion_en_curso" ? punto : null;
            }

            function actualizarControlesVotacion() {
                var punto = obtenerPuntoActual();
                var permiteVotacion = puntoPermiteVotacion(punto);
                var estadoVotacion = punto && punto.estado_votacion ? punto.estado_votacion : "pendiente";

                if (!btnIniciarVotacion || !btnCerrarVotacion) {
                    return;
                }

                if (!sesionEnCurso) {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = true;
                    mostrarMensajeVotacion("Debe iniciar el pleno antes de abrir una votación.", "alert-info");
                    return;
                }

                if (!permiteVotacion) {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = true;
                    mostrarMensajeVotacion("Este punto no requiere votación.", "alert-info");
                    return;
                }

                if (estadoVotacion === "votacion_en_curso") {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = false;
                    mostrarMensajeVotacion("Votación en curso.", "alert-success");
                    return;
                }

                if (estadoVotacion === "votacion_cerrada") {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = true;
                    mostrarMensajeVotacion("Votación cerrada.", "alert-warning");
                    return;
                }

                btnIniciarVotacion.disabled = false;
                btnCerrarVotacion.disabled = true;
                mostrarMensajeVotacion("No hay votación activa.", "alert-info");
            }

            function actualizarVista() {
                var punto = obtenerPuntoActual();
                if (!punto) {
                    return;
                }
                limpiarPuntoActual();
                marcarPuntoActual(punto);
                actualizarDetalle(punto);
                actualizarControlesNavegacion();
                actualizarControlesVotacion();
            }

            function guardarPuntoActual(punto) {
                if (!punto || !idSesion) {
                    return Promise.resolve();
                }
                return fetch(actualizarPuntoUrl, {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id_sesion: idSesion,
                        punto_actual: String(punto.numero || "")
                    })
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error("No fue posible guardar el punto actual.");
                    }
                    return response.json();
                });
            }

            function navegarPunto(direccion) {
                var nuevoIndice = indiceActual + direccion;
                if (nuevoIndice < 0 || nuevoIndice >= puntosOrdenDia.length) {
                    return;
                }
                indiceActual = nuevoIndice;
                actualizarVista();
                guardarPuntoActual(obtenerPuntoActual()).catch(function (error) {
                    if (window.console && typeof window.console.error === "function") {
                        window.console.error(error);
                    }
                });
            }

            function ejecutarAccionVotacion(url, mensajeError) {
                return fetch(url, {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ id_sesion: idSesion })
                }).then(function (response) {
                    return response.json().then(function (payload) {
                        if (!response.ok || !payload || payload.success !== true) {
                            throw new Error(payload && payload.mensaje ? payload.mensaje : mensajeError);
                        }
                        return payload;
                    });
                });
            }

            function iniciarVotacion() {
                var punto = obtenerPuntoActual();
                if (!puntoPermiteVotacion(punto)) {
                    mostrarMensajeVotacion("Este punto no requiere votación.", "alert-info");
                    return;
                }
                if (btnIniciarVotacion) {
                    btnIniciarVotacion.disabled = true;
                }
                ejecutarAccionVotacion(iniciarVotacionUrl, "No fue posible iniciar la votación.").then(function () {
                    punto.estado_votacion = "votacion_en_curso";
                    actualizarControlesVotacion();
                }).catch(function (error) {
                    mostrarMensajeVotacion(error.message || "No fue posible iniciar la votación.", "alert-danger");
                    actualizarControlesVotacion();
                });
            }

            function cerrarVotacion() {
                var punto = obtenerVotacionActiva();
                if (!punto) {
                    mostrarMensajeVotacion("No hay votación activa.", "alert-warning");
                    return;
                }
                if (btnCerrarVotacion) {
                    btnCerrarVotacion.disabled = true;
                }
                ejecutarAccionVotacion(cerrarVotacionUrl, "No fue posible cerrar la votación.").then(function () {
                    punto.estado_votacion = "votacion_cerrada";
                    actualizarControlesVotacion();
                }).catch(function (error) {
                    mostrarMensajeVotacion(error.message || "No fue posible cerrar la votación.", "alert-danger");
                    actualizarControlesVotacion();
                });
            }

            if (!Array.isArray(puntosOrdenDia) || puntosOrdenDia.length === 0) {
                return;
            }
            if (btnAnterior) {
                btnAnterior.addEventListener("click", function () { navegarPunto(-1); });
            }
            if (btnSiguiente) {
                btnSiguiente.addEventListener("click", function () { navegarPunto(1); });
            }
            if (btnIniciarVotacion) {
                btnIniciarVotacion.addEventListener("click", iniciarVotacion);
            }
            if (btnCerrarVotacion) {
                btnCerrarVotacion.addEventListener("click", cerrarVotacion);
            }
            document.addEventListener("pleno:estado-en-curso", function () {
                sesionEnCurso = true;
                actualizarControlesVotacion();
            });

            actualizarVista();
        }());
    </script>
<?php endif; ?>

<?php if ($sesion): ?>
    <script>
        (function () {
            "use strict";

            var finalizarBtn = document.getElementById("plenoFinishSessionBtn");
            var estadoBadge = document.getElementById("plenoSessionStatusBadge");
            var mensajeEstado = document.getElementById("plenoStartSessionMessage");
            var finalizarPlenoUrl = "ajax/finalizar_pleno.php";

            if (!finalizarBtn) {
                return;
            }

            function mostrarMensaje(texto, tipo) {
                if (!mensajeEstado) {
                    return;
                }

                mensajeEstado.textContent = texto;
                mensajeEstado.classList.remove("d-none", "alert-success", "alert-danger");
                mensajeEstado.classList.add(tipo === "error" ? "alert-danger" : "alert-success");
            }

            function bloquearAcciones() {
                document.querySelectorAll(".pleno-session-action-btn").forEach(function (boton) {
                    boton.disabled = true;
                });
            }

            finalizarBtn.addEventListener("click", function () {
                var idSesion = parseInt(finalizarBtn.getAttribute("data-id-sesion") || "0", 10);
                var confirmado = window.confirm("¿Desea finalizar el pleno? Una vez finalizado, no se podrán iniciar nuevas votaciones ni modificar el punto actual.");

                if (!confirmado || !idSesion) {
                    return;
                }

                finalizarBtn.disabled = true;

                fetch(finalizarPlenoUrl, {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id_sesion: idSesion
                    })
                }).then(function (response) {
                    return response.json().then(function (payload) {
                        if (!response.ok || !payload || payload.success !== true) {
                            throw new Error(payload && payload.mensaje ? payload.mensaje : "No fue posible finalizar el pleno.");
                        }

                        return payload;
                    });
                }).then(function (payload) {
                    if (estadoBadge) {
                        estadoBadge.textContent = "FINALIZADA";
                    }

                    bloquearAcciones();
                    mostrarMensaje(payload.mensaje || "Pleno finalizado correctamente.", "success");
                }).catch(function (error) {
                    finalizarBtn.disabled = false;
                    mostrarMensaje(error.message || "No fue posible finalizar el pleno.", "error");
                });
            });
        }());
    </script>
<?php endif; ?>

<?php if ($sesion && !empty($puntosOrdenDia) && $estadoSesion !== 'finalizada'): ?>
    <script>
        (function () {
            "use strict";

            var puntosOrdenDia = <?php echo json_encode($puntosOrdenDia, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            var idSesion = <?php echo (int)($sesion['id_sesion'] ?? 0); ?>;
            var actualizarPuntoUrl = "ajax/actualizar_punto_actual.php";
            var indiceActual = <?php echo (int)$indicePuntoActual; ?>;
            var btnAnterior = document.getElementById("plenoPrevPointBtn");
            var btnSiguiente = document.getElementById("plenoNextPointBtn");
            var detalleNumero = document.getElementById("plenoCurrentPointNumber");
            var detalleTitulo = document.getElementById("plenoCurrentPointTitle");
            var detalleTema = document.getElementById("plenoCurrentPointTopic");
            var detalleDescripcion = document.getElementById("plenoCurrentPointDescription");
            var detalleTipo = document.getElementById("plenoCurrentPointType");
            var detalleOrigen = document.getElementById("plenoCurrentPointOrigin");

            function buscarElementoPunto(numero) {
                var elementos = document.querySelectorAll("[data-agenda-number]");
                var encontrado = null;

                elementos.forEach(function (elemento) {
                    if (!encontrado && elemento.getAttribute("data-agenda-number") === numero) {
                        encontrado = elemento;
                    }
                });

                return encontrado;
            }

            function crearBadgeActual() {
                var badge = document.createElement("span");
                badge.className = "secretaria-badge badge-current";
                badge.setAttribute("data-current-badge", "true");
                badge.textContent = "PUNTO ACTUAL";
                return badge;
            }

            function limpiarPuntoActual() {
                document.querySelectorAll(".agenda-item-current").forEach(function (elemento) {
                    elemento.classList.remove("agenda-item-current");
                });
                document.querySelectorAll(".agenda-subitem-current").forEach(function (elemento) {
                    elemento.classList.remove("agenda-subitem-current");
                });
                document.querySelectorAll("[data-current-badge='true']").forEach(function (badge) {
                    badge.remove();
                });
            }

            function marcarPuntoActual(punto) {
                var elemento = buscarElementoPunto(String(punto.numero || ""));

                if (!elemento) {
                    return;
                }

                if (elemento.classList.contains("agenda-item")) {
                    var main = elemento.querySelector(".agenda-main");
                    elemento.classList.add("agenda-item-current");

                    if (main) {
                        main.appendChild(crearBadgeActual());
                    }
                    return;
                }

                elemento.classList.add("agenda-subitem-current");
                elemento.appendChild(crearBadgeActual());
            }

            function actualizarDetalle(punto) {
                if (detalleNumero) {
                    detalleNumero.textContent = "PUNTO " + (punto.numero || "");
                }
                if (detalleTitulo) {
                    detalleTitulo.textContent = punto.titulo || "Sin título";
                }
                if (detalleTema) {
                    var tema = punto.tema || "";
                    detalleTema.hidden = tema === "";
                    detalleTema.textContent = tema !== "" ? "Tema: " + tema : "";
                }
                if (detalleDescripcion) {
                    var descripcion = punto.descripcion || "";
                    detalleDescripcion.hidden = descripcion === "";
                    detalleDescripcion.textContent = descripcion;
                }
                if (detalleTipo) {
                    detalleTipo.textContent = punto.tipo || "Sin tipo";
                }
                if (detalleOrigen) {
                    detalleOrigen.textContent = punto.origen || "Sin origen";
                }
            }

            function actualizarBotones() {
                if (btnAnterior) {
                    btnAnterior.disabled = indiceActual <= 0;
                }
                if (btnSiguiente) {
                    btnSiguiente.disabled = indiceActual >= puntosOrdenDia.length - 1;
                }
            }

            function actualizarVista() {
                var punto = puntosOrdenDia[indiceActual];

                if (!punto) {
                    return;
                }

                limpiarPuntoActual();
                marcarPuntoActual(punto);
                actualizarDetalle(punto);
                actualizarBotones();
            }

            function guardarPuntoActual(punto) {
                if (!punto || !idSesion) {
                    return;
                }

                fetch(actualizarPuntoUrl, {
                    method: "POST",
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id_sesion: idSesion,
                        punto_actual: String(punto.numero || "")
                    })
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error("No fue posible guardar el punto actual.");
                    }

                    return response.json();
                }).then(function (payload) {
                    if (!payload || payload.ok !== true) {
                        throw new Error("No fue posible guardar el punto actual.");
                    }
                }).catch(function (error) {
                    if (window.console && typeof window.console.error === "function") {
                        window.console.error(error);
                    }
                });
            }

            if (!Array.isArray(puntosOrdenDia) || puntosOrdenDia.length === 0) {
                return;
            }

            if (btnAnterior) {
                btnAnterior.addEventListener("click", function () {
                    if (indiceActual <= 0) {
                        return;
                    }
                    indiceActual -= 1;
                    actualizarVista();
                    guardarPuntoActual(puntosOrdenDia[indiceActual]);
                });
            }

            if (btnSiguiente) {
                btnSiguiente.addEventListener("click", function () {
                    if (indiceActual >= puntosOrdenDia.length - 1) {
                        return;
                    }
                    indiceActual += 1;
                    actualizarVista();
                    guardarPuntoActual(puntosOrdenDia[indiceActual]);
                });
            }

            actualizarVista();
        }());
    </script>
<?php endif; ?>

<?php if ($sesion && !empty($puntosOrdenDia) && $estadoSesion !== 'finalizada'): ?>
    <script>
        (function () {
            "use strict";

            var puntosOrdenDia = <?php echo json_encode($puntosOrdenDia, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            var idSesion = <?php echo (int)($sesion['id_sesion'] ?? 0); ?>;
            var indiceActual = <?php echo (int)$indicePuntoActual; ?>;
            var sesionEnCurso = <?php echo $estadoSesion === 'en_curso' ? 'true' : 'false'; ?>;
            var actualizarPuntoUrl = "ajax/actualizar_punto_actual.php";
            var iniciarVotacionUrl = "ajax/iniciar_votacion.php";
            var cerrarVotacionUrl = "ajax/cerrar_votacion.php";

            function limpiarListeners(id) {
                var original = document.getElementById(id);
                if (!original) {
                    return null;
                }
                var limpio = original.cloneNode(true);
                original.parentNode.replaceChild(limpio, original);
                return limpio;
            }

            var btnAnterior = limpiarListeners("plenoPrevPointBtn");
            var btnSiguiente = limpiarListeners("plenoNextPointBtn");
            var btnIniciarVotacion = limpiarListeners("plenoStartVoteBtn");
            var btnCerrarVotacion = limpiarListeners("plenoCloseVoteBtn");
            var mensajeVotacion = document.getElementById("plenoVoteControlMessage");
            var detalleNumero = document.getElementById("plenoCurrentPointNumber");
            var detalleTitulo = document.getElementById("plenoCurrentPointTitle");
            var detalleTema = document.getElementById("plenoCurrentPointTopic");
            var detalleDescripcion = document.getElementById("plenoCurrentPointDescription");
            var detalleTipo = document.getElementById("plenoCurrentPointType");
            var detalleOrigen = document.getElementById("plenoCurrentPointOrigin");

            function obtenerPuntoActual() {
                return puntosOrdenDia[indiceActual] || null;
            }

            function puntoPermiteVotacion(punto) {
                var numero = punto ? String(punto.numero || "") : "";
                return numero === "1" || /^3\.\d+$/.test(numero);
            }

            function mostrarMensajeVotacion(texto, tipo) {
                if (!mensajeVotacion) {
                    return;
                }
                mensajeVotacion.textContent = texto;
                mensajeVotacion.classList.remove("alert-info", "alert-success", "alert-warning", "alert-danger");
                mensajeVotacion.classList.add(tipo || "alert-info");
            }

            function buscarElementoPunto(numero) {
                var encontrado = null;
                document.querySelectorAll("[data-agenda-number]").forEach(function (elemento) {
                    if (!encontrado && elemento.getAttribute("data-agenda-number") === numero) {
                        encontrado = elemento;
                    }
                });
                return encontrado;
            }

            function crearBadgeActual() {
                var badge = document.createElement("span");
                badge.className = "secretaria-badge badge-current";
                badge.setAttribute("data-current-badge", "true");
                badge.textContent = "PUNTO ACTUAL";
                return badge;
            }

            function actualizarDetalle(punto) {
                if (detalleNumero) {
                    detalleNumero.textContent = "PUNTO " + (punto.numero || "");
                }
                if (detalleTitulo) {
                    detalleTitulo.textContent = punto.titulo || "Sin titulo";
                }
                if (detalleTema) {
                    var tema = punto.tema || "";
                    detalleTema.hidden = tema === "";
                    detalleTema.textContent = tema !== "" ? "Tema: " + tema : "";
                }
                if (detalleDescripcion) {
                    var descripcion = punto.descripcion || "";
                    detalleDescripcion.hidden = descripcion === "";
                    detalleDescripcion.textContent = descripcion;
                }
                if (detalleTipo) {
                    detalleTipo.textContent = punto.tipo || "Sin tipo";
                }
                if (detalleOrigen) {
                    detalleOrigen.textContent = punto.origen || "Sin origen";
                }
            }

            function actualizarVista() {
                var punto = obtenerPuntoActual();
                var elemento;
                if (!punto) {
                    return;
                }
                document.querySelectorAll(".agenda-item-current, .agenda-subitem-current").forEach(function (nodo) {
                    nodo.classList.remove("agenda-item-current", "agenda-subitem-current");
                });
                document.querySelectorAll("[data-current-badge='true']").forEach(function (badge) {
                    badge.remove();
                });
                elemento = buscarElementoPunto(String(punto.numero || ""));
                if (elemento) {
                    if (elemento.classList.contains("agenda-item")) {
                        elemento.classList.add("agenda-item-current");
                        (elemento.querySelector(".agenda-main") || elemento).appendChild(crearBadgeActual());
                    } else {
                        elemento.classList.add("agenda-subitem-current");
                        elemento.appendChild(crearBadgeActual());
                    }
                }
                actualizarDetalle(punto);
                actualizarControles();
            }

            function actualizarControles() {
                var punto = obtenerPuntoActual();
                var permiteVotacion = puntoPermiteVotacion(punto);
                var estadoVotacion = punto && punto.estado_votacion ? punto.estado_votacion : "pendiente";
                if (btnAnterior) {
                    btnAnterior.disabled = indiceActual <= 0;
                }
                if (btnSiguiente) {
                    btnSiguiente.disabled = indiceActual >= puntosOrdenDia.length - 1;
                }
                if (!btnIniciarVotacion || !btnCerrarVotacion) {
                    return;
                }
                if (!sesionEnCurso) {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = true;
                    mostrarMensajeVotacion("Debe iniciar el pleno antes de abrir una votación.", "alert-info");
                    return;
                }
                if (!permiteVotacion) {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = true;
                    mostrarMensajeVotacion("Este punto no requiere votación.", "alert-info");
                    return;
                }
                if (estadoVotacion === "votacion_en_curso") {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = false;
                    mostrarMensajeVotacion("Votación en curso.", "alert-success");
                    return;
                }
                if (estadoVotacion === "votacion_cerrada") {
                    btnIniciarVotacion.disabled = true;
                    btnCerrarVotacion.disabled = true;
                    mostrarMensajeVotacion("Votación cerrada.", "alert-warning");
                    return;
                }
                btnIniciarVotacion.disabled = false;
                btnCerrarVotacion.disabled = true;
                mostrarMensajeVotacion("No hay votación activa.", "alert-info");
            }

            function guardarPuntoActual(punto) {
                if (!punto || !idSesion) {
                    return Promise.resolve();
                }
                return fetch(actualizarPuntoUrl, {
                    method: "POST",
                    headers: { "Accept": "application/json", "Content-Type": "application/json" },
                    body: JSON.stringify({ id_sesion: idSesion, punto_actual: String(punto.numero || "") })
                });
            }

            function navegarPunto(direccion) {
                var nuevoIndice = indiceActual + direccion;
                if (nuevoIndice < 0 || nuevoIndice >= puntosOrdenDia.length) {
                    return;
                }
                indiceActual = nuevoIndice;
                actualizarVista();
                guardarPuntoActual(obtenerPuntoActual()).catch(function (error) {
                    if (window.console) {
                        window.console.error(error);
                    }
                });
            }

            function ejecutarAccionVotacion(url, mensajeError) {
                return fetch(url, {
                    method: "POST",
                    headers: { "Accept": "application/json", "Content-Type": "application/json" },
                    body: JSON.stringify({ id_sesion: idSesion })
                }).then(function (response) {
                    return response.json().then(function (payload) {
                        if (!response.ok || !payload || payload.success !== true) {
                            throw new Error(payload && payload.mensaje ? payload.mensaje : mensajeError);
                        }
                        return payload;
                    });
                });
            }

            function obtenerVotacionActiva() {
                var punto = obtenerPuntoActual();
                return punto && punto.estado_votacion === "votacion_en_curso" ? punto : null;
            }

            function iniciarVotacion() {
                var punto = obtenerPuntoActual();
                if (!puntoPermiteVotacion(punto)) {
                    mostrarMensajeVotacion("Este punto no requiere votación.", "alert-info");
                    return;
                }
                ejecutarAccionVotacion(iniciarVotacionUrl, "No fue posible iniciar la votación.").then(function () {
                    punto.estado_votacion = "votacion_en_curso";
                    actualizarControles();
                }).catch(function (error) {
                    mostrarMensajeVotacion(error.message || "No fue posible iniciar la votación.", "alert-danger");
                    actualizarControles();
                });
            }

            function cerrarVotacion() {
                var punto = obtenerVotacionActiva();
                if (!punto) {
                    mostrarMensajeVotacion("No hay votación activa.", "alert-warning");
                    return;
                }
                ejecutarAccionVotacion(cerrarVotacionUrl, "No fue posible cerrar la votación.").then(function () {
                    punto.estado_votacion = "votacion_cerrada";
                    actualizarControles();
                }).catch(function (error) {
                    mostrarMensajeVotacion(error.message || "No fue posible cerrar la votación.", "alert-danger");
                    actualizarControles();
                });
            }

            if (btnAnterior) {
                btnAnterior.addEventListener("click", function () { navegarPunto(-1); });
            }
            if (btnSiguiente) {
                btnSiguiente.addEventListener("click", function () { navegarPunto(1); });
            }
            if (btnIniciarVotacion) {
                btnIniciarVotacion.addEventListener("click", iniciarVotacion);
            }
            if (btnCerrarVotacion) {
                btnCerrarVotacion.addEventListener("click", cerrarVotacion);
            }
            document.addEventListener("pleno:estado-en-curso", function () {
                sesionEnCurso = true;
                actualizarControles();
            });
            actualizarVista();
        }());
    </script>
<?php endif; ?>
