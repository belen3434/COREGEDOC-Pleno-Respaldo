<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$sesion = $data['pleno_sesion_actual'] ?? null;
$temasComision = $data['pleno_temas_comision_sesion'] ?? [];
$puntosVarios = $data['pleno_puntos_varios_sesion'] ?? [];
$totalConsejerosGobernador = (int)($data['pleno_total_consejeros_gobernador'] ?? 0);

$formatearFechaLarga = static function (?string $fecha): string {
    if (!$fecha) {
        return '';
    }

    $timestamp = strtotime($fecha);
    if (!$timestamp) {
        return $fecha;
    }

    $meses = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre',
    ];

    return date('j', $timestamp) . ' de ' . $meses[(int)date('n', $timestamp)] . ' de ' . date('Y', $timestamp);
};

$formatearFechaCorta = static function (?string $fecha): string {
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

$formatearTipoBadge = static function (?string $tipo): string {
    $tipo = trim((string)$tipo);
    $tipoNormalizado = function_exists('mb_strtolower')
        ? mb_strtolower($tipo, 'UTF-8')
        : strtolower($tipo);

    if ($tipoNormalizado === 'normal' || $tipoNormalizado === 'ordinario') {
        return 'ORDINARIO';
    }

    if ($tipoNormalizado === 'extraordinario') {
        return 'EXTRAORDINARIO';
    }

    return strtoupper($tipo);
};

$formatearEstado = static function (?string $estado): string {
    $estado = trim((string)$estado);
    return $estado !== '' ? strtoupper(str_replace('_', ' ', $estado)) : '';
};

$resolverTextoTema = static function (array $tema): string {
    $comision = trim((string)($tema['nombreComision'] ?? ''));
    $nombreTema = trim((string)($tema['nombreTema'] ?? ''));

    if ($comision !== '' && $nombreTema !== '') {
        return $comision . ' - Tema: ' . $nombreTema;
    }

    if ($comision !== '') {
        return $comision;
    }

    if ($nombreTema !== '') {
        return $nombreTema;
    }

    return 'Tema de comisión';
};

$resolverTextoVario = static function (array $punto): string {
    $titulo = trim((string)($punto['titulo_punto'] ?? ''));
    $descripcion = trim((string)($punto['descripcion_punto'] ?? ''));

    if ($titulo !== '' && $descripcion !== '') {
        return $titulo . ' - ' . $descripcion;
    }

    if ($titulo !== '') {
        return $titulo;
    }

    if ($descripcion !== '') {
        return $descripcion;
    }

    return 'Punto varios';
};

$cantidadTemasComision = count($temasComision);
$cantidadPuntosVarios = count($puntosVarios);
$nombreActa = trim((string)($sesion['aprobacion_actas'] ?? ''));
$tituloAprobacionActa = $nombreActa !== ''
    ? 'APROBACIÓN DE ACTA: ' . $nombreActa
    : 'APROBACIÓN DE ACTA';
$puntosEnTabla = 4 + $cantidadTemasComision + $cantidadPuntosVarios;
$duracionComisiones = $cantidadTemasComision * 15;
$duracionVarios = max(1, $cantidadPuntosVarios) * 10;
$duracionEstimada = 10 + 20 + $duracionComisiones + $duracionVarios;
?>
<style>
    .pleno-session-dashboard {
        color: #1f3240;
    }

    .pleno-session-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .pleno-session-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem 1.2rem;
        color: #657987;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .pleno-session-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .pleno-start-btn {
        min-width: 170px;
        min-height: 48px;
        border-radius: 12px;
        font-weight: 700;
    }

    .pleno-session-grid {
        display: grid;
        grid-template-columns: minmax(0, 7fr) minmax(280px, 3fr);
        gap: 1.25rem;
        align-items: start;
    }

    .pleno-agenda-list {
        display: flex;
        flex-direction: column;
        gap: 0.95rem;
    }

    .pleno-agenda-item,
    .pleno-subtema-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        border: 1px solid #e1e9ee;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(15, 38, 56, 0.05);
    }

    .pleno-agenda-item {
        padding: 1rem 1.1rem;
        border-radius: 12px;
    }

    .pleno-agenda-number {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        border-radius: 50%;
        color: #ffffff;
        background: #198754;
        font-size: 1.2rem;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(25, 135, 84, 0.24);
    }

    .pleno-agenda-content {
        min-width: 0;
        flex: 1;
    }

    .pleno-agenda-title {
        margin: 0 0 0.25rem;
        color: #203949;
        font-size: 1.03rem;
        font-weight: 750;
    }

    .pleno-agenda-description {
        margin: 0;
        color: #657987;
        font-size: 0.92rem;
    }

    .pleno-agenda-duration {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        flex-shrink: 0;
        color: #198754;
        font-size: 0.88rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .pleno-agenda-arrow {
        color: #8aa0ad;
        flex-shrink: 0;
    }

    .pleno-subtemas-list {
        display: grid;
        gap: 0.65rem;
        margin: 0.75rem 0 0.2rem;
        padding-left: 3.9rem;
    }

    .pleno-subtema-card {
        padding: 0.7rem 0.85rem;
        border-radius: 10px;
        color: #203949;
        font-weight: 650;
    }

    .pleno-side-stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .pleno-info-row {
        display: flex;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #edf2f5;
    }

    .pleno-info-row:last-child {
        border-bottom: 0;
    }

    .pleno-info-label {
        color: #657987;
        font-weight: 650;
    }

    .pleno-info-value {
        color: #203949;
        font-weight: 750;
        text-align: right;
    }

    .pleno-summary-box {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.9rem;
        border: 1px solid #e1e9ee;
        border-radius: 12px;
        background: #f8fafc;
    }

    .pleno-summary-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: #198754;
        background: rgba(25, 135, 84, 0.1);
        font-size: 1.1rem;
    }

    .pleno-summary-number {
        color: #203949;
        font-size: 1.55rem;
        font-weight: 850;
        line-height: 1;
    }

    .pleno-doc-button {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 48px;
        border-radius: 12px;
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .pleno-session-hero,
        .pleno-session-grid {
            display: block;
        }

        .pleno-start-btn {
            width: 100%;
            margin-top: 1rem;
        }

        .pleno-side-stack {
            margin-top: 1.25rem;
        }
    }

    @media (max-width: 575.98px) {
        .pleno-agenda-item {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .pleno-subtemas-list {
            padding-left: 0;
        }

        .pleno-agenda-duration {
            margin-left: 3.9rem;
        }
    }
</style>

<div class="container-fluid mt-4 pleno-session-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-sitemap me-2 text-success"></i>Comisiones
        </h2>
        <a href="index.php" class="btn btn-pleno-back">
            <i class="fas fa-arrow-left me-2"></i>Volver al inicio
        </a>
    </div>

    <?php if (!$sesion): ?>
        <div class="alert alert-warning" role="alert">
            No existe una sesión plenaria vigente para mostrar.
        </div>
    <?php else: ?>
        <div class="pleno-session-hero">
            <div>
                <div class="pleno-eyebrow text-success mb-2">SESIÓN PLENARIA</div>
                <h1 class="display-6 pleno-page-title mb-3">
                    Sesión <?php echo htmlspecialchars($formatearTipo($sesion['tipo_pleno'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> N° <?php echo htmlspecialchars((string)($sesion['numero_sesion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                </h1>
                <div class="pleno-session-meta">
                    <span><i class="fas fa-calendar-alt text-success"></i><?php echo htmlspecialchars($formatearFechaLarga($sesion['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fas fa-clock text-success"></i><?php echo htmlspecialchars($formatearHora($sesion['hora'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fas fa-map-marker-alt text-success"></i><?php echo htmlspecialchars((string)($sesion['lugar'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle"><?php echo htmlspecialchars($formatearTipoBadge($sesion['tipo_pleno'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
            <form action="index.php?action=iniciar_pleno&id_sesion=<?php echo (int)($sesion['id_sesion'] ?? 0); ?>" method="post">
                <button type="button" class="btn btn-success pleno-btn-success pleno-start-btn" data-next-action="iniciar_pleno">
                    <i class="fas fa-play me-2"></i>Iniciar Pleno
                </button>
            </form>
        </div>

        <div class="pleno-session-grid">
            <section class="pleno-panel">
                <div class="pleno-panel-body">
                    <h2 class="h4 pleno-section-title mb-4">Orden del Día</h2>

                    <div class="pleno-agenda-list">
                        <article class="pleno-agenda-item">
                            <span class="pleno-agenda-number">1</span>
                            <div class="pleno-agenda-content">
                                <h3 class="pleno-agenda-title"><?php echo htmlspecialchars($tituloAprobacionActa, ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="pleno-agenda-description">Revisión y aprobación formal del acta de la sesión anterior.</p>
                            </div>
                            <span class="pleno-agenda-duration"><i class="fas fa-stopwatch"></i>10 min</span>
                            <i class="fas fa-chevron-right pleno-agenda-arrow"></i>
                        </article>

                        <article class="pleno-agenda-item">
                            <span class="pleno-agenda-number">2</span>
                            <div class="pleno-agenda-content">
                                <h3 class="pleno-agenda-title">CUENTA PRESIDENTE DEL CONSEJO REGIONAL DE VALPARAÍSO</h3>
                                <p class="pleno-agenda-description">Cuenta institucional del Consejo Regional de Valparaíso.</p>
                            </div>
                            <span class="pleno-agenda-duration"><i class="fas fa-stopwatch"></i>20 min</span>
                            <i class="fas fa-chevron-right pleno-agenda-arrow"></i>
                        </article>

                        <article class="pleno-agenda-item">
                            <span class="pleno-agenda-number">3</span>
                            <div class="pleno-agenda-content">
                                <h3 class="pleno-agenda-title">CUENTA COMISIONES</h3>
                                <p class="pleno-agenda-description">Presentación de informes y acuerdos elevados por las comisiones.</p>
                            </div>
                            <span class="pleno-agenda-duration"><i class="fas fa-stopwatch"></i><?php echo (int)$duracionComisiones; ?> min</span>
                            <i class="fas fa-chevron-right pleno-agenda-arrow"></i>
                        </article>

                        <div class="pleno-subtemas-list">
                            <?php if (empty($temasComision)): ?>
                                <div class="pleno-subtema-card">No hay temas de comisión cargados para esta sesión.</div>
                            <?php else: ?>
                                <?php foreach ($temasComision as $indiceTema => $tema): ?>
                                    <div class="pleno-subtema-card">
                                        <?php echo '3.' . ((int)$indiceTema + 1) . ' ' . htmlspecialchars($resolverTextoTema($tema), ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <article class="pleno-agenda-item">
                            <span class="pleno-agenda-number">4</span>
                            <div class="pleno-agenda-content">
                                <h3 class="pleno-agenda-title">VARIOS</h3>
                                <p class="pleno-agenda-description">Temas extraordinarios o de última incorporación.</p>
                            </div>
                            <span class="pleno-agenda-duration"><i class="fas fa-stopwatch"></i><?php echo (int)$duracionVarios; ?> min</span>
                            <i class="fas fa-chevron-right pleno-agenda-arrow"></i>
                        </article>

                        <div class="pleno-subtemas-list">
                            <?php if (empty($puntosVarios)): ?>
                                <div class="pleno-subtema-card">No hay puntos varios cargados para esta sesión.</div>
                            <?php else: ?>
                                <?php foreach ($puntosVarios as $indiceVario => $puntoVario): ?>
                                    <div class="pleno-subtema-card">
                                        <?php echo '4.' . ((int)$indiceVario + 1) . ' ' . htmlspecialchars($resolverTextoVario($puntoVario), ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="pleno-side-stack">
                <section class="pleno-panel">
                    <div class="pleno-panel-body">
                        <h2 class="h5 pleno-section-title mb-3">Información de la Sesión</h2>
                        <div class="pleno-info-row">
                            <span class="pleno-info-label">Tipo de sesión:</span>
                            <span class="pleno-info-value"><?php echo htmlspecialchars($formatearTipo($sesion['tipo_pleno'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="pleno-info-row">
                            <span class="pleno-info-label">Número:</span>
                            <span class="pleno-info-value"><?php echo htmlspecialchars((string)($sesion['numero_sesion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="pleno-info-row">
                            <span class="pleno-info-label">Fecha:</span>
                            <span class="pleno-info-value"><?php echo htmlspecialchars($formatearFechaCorta($sesion['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="pleno-info-row">
                            <span class="pleno-info-label">Hora:</span>
                            <span class="pleno-info-value"><?php echo htmlspecialchars($formatearHora($sesion['hora'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="pleno-info-row">
                            <span class="pleno-info-label">Lugar:</span>
                            <span class="pleno-info-value"><?php echo htmlspecialchars((string)($sesion['lugar'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="pleno-info-row">
                            <span class="pleno-info-label">Estado:</span>
                            <span class="badge pleno-badge-warning"><?php echo htmlspecialchars($formatearEstado($sesion['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>
                </section>

                <section class="pleno-panel">
                    <div class="pleno-panel-body">
                        <h2 class="h5 pleno-section-title mb-3">Resumen del Pleno</h2>
                        <div class="d-flex flex-column gap-3">
                            <div class="pleno-summary-box">
                                <span class="pleno-summary-icon"><i class="fas fa-users"></i></span>
                                <div>
                                    <div class="pleno-summary-number"><?php echo (int)$totalConsejerosGobernador; ?></div>
                                    <div class="small text-muted fw-semibold">Consejeros y Gobernador</div>
                                </div>
                            </div>
                            <div class="pleno-summary-box">
                                <span class="pleno-summary-icon"><i class="fas fa-list-check"></i></span>
                                <div>
                                    <div class="pleno-summary-number"><?php echo (int)$puntosEnTabla; ?></div>
                                    <div class="small text-muted fw-semibold">Puntos en tabla</div>
                                </div>
                            </div>
                            <div class="pleno-summary-box">
                                <span class="pleno-summary-icon"><i class="fas fa-hourglass-half"></i></span>
                                <div>
                                    <div class="pleno-summary-number"><?php echo (int)$duracionEstimada; ?></div>
                                    <div class="small text-muted fw-semibold">Duración estimada total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pleno-panel">
                    <div class="pleno-panel-body">
                        <h2 class="h5 pleno-section-title mb-3">Documentos Asociados</h2>
                        <button type="button" class="btn btn-light border pleno-doc-button">
                            <span><i class="fas fa-folder-open me-2 text-success"></i>Ver documentos de la sesión</span>
                            <i class="fas fa-chevron-right text-success"></i>
                        </button>
                    </div>
                </section>
            </aside>
        </div>
    <?php endif; ?>
</div>
