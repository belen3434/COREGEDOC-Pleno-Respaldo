<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$sesion = $data['pleno_sesion_actual'] ?? null;
$asistenciaResumen = $data['pleno_asistencia_resumen'] ?? null;
$resultados = $data['pleno_resumen_resultados'] ?? [];
$participantes = is_array($asistenciaResumen) ? ($asistenciaResumen['participantes'] ?? []) : [];

$h = static function ($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};

$formatearFecha = static function (?string $fecha): string {
    if (!$fecha) {
        return 'Sin fecha';
    }

    $timestamp = strtotime($fecha);
    return $timestamp ? date('d/m/Y', $timestamp) : $fecha;
};

$formatearHora = static function (?string $hora): string {
    if (!$hora) {
        return 'Sin hora';
    }

    return substr($hora, 0, 5) . ' hrs.';
};

$formatearFechaHora = static function (?string $fecha): string {
    if (!$fecha) {
        return 'Sin registro';
    }

    $timestamp = strtotime($fecha);
    return $timestamp ? date('d/m/Y H:i', $timestamp) . ' hrs.' : $fecha;
};

$formatearEstado = static function (?string $estado): string {
    $estado = trim((string)$estado);
    return $estado !== '' ? strtoupper(str_replace('_', ' ', $estado)) : 'SIN ESTADO';
};

$formatearTipo = static function (?string $tipo): string {
    $tipo = trim((string)$tipo);
    return $tipo !== '' ? ucfirst(strtolower($tipo)) : 'Sin tipo';
};

$claseResultado = static function (string $resultado): string {
    if ($resultado === 'Aprobado') {
        return 'pleno-result-approved';
    }

    if ($resultado === 'Rechazado') {
        return 'pleno-result-rejected';
    }

    if ($resultado === 'Empate' || $resultado === 'Sin votos') {
        return 'pleno-result-warning';
    }

    if ($resultado === 'Exposición' || $resultado === 'Votación en curso') {
        return 'pleno-result-info';
    }

    return 'pleno-result-neutral';
};

$claseBadgeVoto = static function (?string $voto): string {
    $voto = strtoupper(trim((string)$voto));
    if ($voto === 'SI') {
        return 'pleno-status-badge pleno-vote-badge-si';
    }

    if ($voto === 'NO') {
        return 'pleno-status-badge pleno-vote-badge-no';
    }

    if ($voto === 'ABSTENCION') {
        return 'pleno-status-badge pleno-vote-badge-abstencion';
    }

    return 'pleno-status-badge pleno-result-neutral';
};

$presentes = is_array($asistenciaResumen) ? (int)($asistenciaResumen['presentes'] ?? 0) : 0;
$ausentes = is_array($asistenciaResumen) ? (int)($asistenciaResumen['ausentes'] ?? 0) : 0;
$totalHabilitados = is_array($asistenciaResumen) ? (int)($asistenciaResumen['total'] ?? 0) : 0;
$estadoSesionResumen = strtolower(trim((string)($sesion['estado'] ?? '')));
$porcentajeParticipacion = $totalHabilitados > 0 ? round(($presentes / $totalHabilitados) * 100, 1) : 0;
$porcentajeAusentismo = $totalHabilitados > 0 ? round(($ausentes / $totalHabilitados) * 100, 1) : 0;
$totalPuntosTratados = count($resultados);
$totalPuntosVotados = 0;
$totalPuntosInformativos = 0;
$totalVotacionesRealizadas = 0;
$totalVotosEmitidos = 0;

foreach ($resultados as $resultadoResumen) {
    $esInformativoResumen = !empty($resultadoResumen['es_informativo']) || (string)($resultadoResumen['resultado'] ?? '') === 'Exposición';
    $tuvoVotacionResumen = !empty($resultadoResumen['tuvo_votacion'])
        || in_array((string)($resultadoResumen['estado_votacion'] ?? ''), ['votacion_en_curso', 'votacion_cerrada'], true);

    if ($esInformativoResumen) {
        $totalPuntosInformativos++;
    }

    if ($tuvoVotacionResumen) {
        $totalPuntosVotados++;
    }

    if ($tuvoVotacionResumen) {
        $totalVotacionesRealizadas++;
    }

    $totalVotosEmitidos += (int)($resultadoResumen['total'] ?? 0);
}

$textoResumenEjecutivo = 'Se trataron ' . $totalPuntosTratados . ' puntos durante la sesión. '
    . $totalPuntosVotados . ' puntos fueron sometidos a votación y '
    . $totalPuntosInformativos . ' fueron informativos. Participaron '
    . $presentes . ' de ' . $totalHabilitados . ' consejeros habilitados.';
?>
<style>
    .pleno-resumen-page {
        color: #1f3240;
    }

    .pleno-resumen-heading {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .pleno-resumen-heading-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        color: #ffffff;
        background: #0f8f4c;
        box-shadow: 0 10px 20px rgba(15, 143, 76, 0.22);
    }

    .pleno-resumen-title {
        margin: 0;
        color: #172f3f;
        font-size: 2rem;
        font-weight: 850;
    }

    .pleno-resumen-card {
        border: 1px solid #e2ebf0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 26px rgba(15, 38, 56, 0.08);
    }

    .pleno-resumen-card + .pleno-resumen-card,
    .pleno-resumen-grid,
    .pleno-resumen-lower-grid {
        margin-top: 1.25rem;
    }

    .pleno-resumen-card-body {
        padding: 1.35rem;
    }

    .pleno-resumen-card-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin: 0 0 1.15rem;
        color: #203949;
        font-size: 1.18rem;
        font-weight: 850;
    }

    .pleno-resumen-card-title i {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        color: #0f8f4c;
        background: #e8f7ee;
    }

    .pleno-resumen-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.12fr) minmax(320px, 0.88fr);
        gap: 1.25rem;
        align-items: stretch;
    }

    .pleno-session-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .pleno-session-summary-item {
        min-height: 78px;
        padding: 0.9rem;
        border: 1px solid #e8f0f4;
        border-radius: 13px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    }

    .pleno-session-label {
        margin-bottom: 0.28rem;
        color: #657987;
        font-size: 0.76rem;
        font-weight: 850;
        text-transform: uppercase;
    }

    .pleno-session-value {
        color: #203949;
        font-size: 1rem;
        font-weight: 850;
    }

    .pleno-badge-finalizado,
    .pleno-badge-generado,
    .pleno-result-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 0.25rem 0.68rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 850;
        white-space: nowrap;
    }

    .pleno-badge-finalizado {
        background: #e8f7ee;
        color: #0f8f4c;
    }

    .pleno-badge-generado {
        background: #eaf3ff;
        color: #0d6efd;
        letter-spacing: 0.04em;
    }

    .pleno-attendance-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .pleno-attendance-tile {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        min-height: 108px;
        padding: 1rem;
        border-radius: 14px;
        border: 1px solid transparent;
    }

    .pleno-attendance-green {
        background: #eef9f2;
        border-color: #d8efdf;
    }

    .pleno-attendance-blue {
        background: #eef6ff;
        border-color: #d7e9fb;
    }

    .pleno-attendance-orange {
        background: #fff6e8;
        border-color: #f6dfb9;
    }

    .pleno-attendance-purple {
        background: #f2f0ff;
        border-color: #ded9fb;
    }

    .pleno-attendance-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        border-radius: 50%;
        color: #ffffff;
        font-size: 1rem;
    }

    .pleno-attendance-green .pleno-attendance-icon {
        background: #0f8f4c;
    }

    .pleno-attendance-blue .pleno-attendance-icon {
        background: #0d6efd;
    }

    .pleno-attendance-orange .pleno-attendance-icon {
        background: #f28c18;
    }

    .pleno-attendance-purple .pleno-attendance-icon {
        background: #6055d8;
    }

    .pleno-attendance-number {
        color: #182f3f;
        font-size: 1.9rem;
        font-weight: 900;
        line-height: 1;
    }

    .pleno-attendance-text {
        margin-top: 0.22rem;
        color: #647887;
        font-size: 0.84rem;
        font-weight: 780;
    }

    .pleno-results-table-wrap {
        overflow-x: auto;
    }

    .pleno-results-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .pleno-results-table th {
        padding: 0.85rem 0.95rem;
        background: #f1f5f8;
        color: #5d7180;
        font-size: 0.76rem;
        font-weight: 850;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .pleno-results-table th:first-child {
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }

    .pleno-results-table th:last-child {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    .pleno-results-table td {
        padding: 0.9rem 0.95rem;
        border-bottom: 1px solid #edf2f5;
        color: #273f4f;
        font-weight: 650;
        vertical-align: middle;
    }

    .pleno-results-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .pleno-result-approved {
        background: #e8f7ee;
        color: #0f8f4c;
    }

    .pleno-result-rejected {
        background: #fdecef;
        color: #d72638;
    }

    .pleno-result-neutral {
        background: #eef2f6;
        color: #5d7180;
    }

    .pleno-result-warning {
        background: #fff6e8;
        color: #b86b11;
    }

    .pleno-result-info {
        background: #eaf3ff;
        color: #0d6efd;
    }

    .pleno-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 26px;
        padding: 0.22rem 0.62rem;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 850;
        white-space: nowrap;
    }

    .pleno-status-present {
        background: #e8f7ee;
        color: #0f8f4c;
    }

    .pleno-status-absent {
        background: #fdecef;
        color: #d72638;
    }

    .pleno-vote-badge-si {
        background: #e8f7ee;
        color: #0f8f4c;
    }

    .pleno-vote-badge-no {
        background: #fdecef;
        color: #d72638;
    }

    .pleno-vote-badge-abstencion {
        background: #fff6e8;
        color: #b86b11;
    }

    .pleno-vote-favor,
    .pleno-vote-contra,
    .pleno-vote-abstencion {
        font-weight: 900;
    }

    .pleno-vote-favor {
        color: #0f8f4c;
    }

    .pleno-vote-contra {
        color: #d72638;
    }

    .pleno-vote-abstencion {
        color: #f28c18;
    }

    .pleno-resumen-lower-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(340px, 0.86fr);
        gap: 1.25rem;
        align-items: stretch;
    }

    .pleno-agreements-list {
        display: grid;
        gap: 0.85rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .pleno-agreement-card {
        display: flex;
        gap: 0.85rem;
        padding: 0.95rem;
        border: 1px solid #e5eff3;
        border-radius: 13px;
        background: #fbfdff;
    }

    .pleno-agreement-check {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 36px;
        border-radius: 50%;
        color: #ffffff;
        background: #0f8f4c;
        box-shadow: 0 8px 16px rgba(15, 143, 76, 0.18);
    }

    .pleno-agreement-number {
        margin-bottom: 0.15rem;
        color: #203949;
        font-weight: 850;
    }

    .pleno-agreement-text {
        color: #5f7280;
        font-weight: 650;
        line-height: 1.4;
    }

    .pleno-view-all-btn,
    .pleno-certificate-actions .btn {
        min-height: 40px;
        border-radius: 10px;
        font-weight: 750;
    }

    .pleno-view-all-btn {
        margin-top: 1rem;
        border-color: #0f8f4c;
        color: #0f8f4c;
    }

    .pleno-view-all-btn:hover,
    .pleno-view-all-btn:focus {
        border-color: #0b743d;
        color: #0b743d;
        background: #eef9f2;
    }

    .pleno-certificate-box {
        display: flex;
        gap: 0.9rem;
        padding: 1rem;
        border: 1px solid #e5eff3;
        border-radius: 14px;
        background: #fbfdff;
    }

    .pleno-pdf-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        border-radius: 13px;
        color: #d72638;
        background: #fdecef;
        font-size: 1.35rem;
    }

    .pleno-certificate-name {
        color: #203949;
        font-weight: 850;
        word-break: break-word;
    }

    .pleno-certificate-date {
        margin: 0.2rem 0 0.45rem;
        color: #657987;
        font-size: 0.88rem;
        font-weight: 650;
    }

    .pleno-certificate-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .pleno-certificate-actions .btn-success {
        grid-column: 1 / -1;
        background: #0f8f4c;
        border-color: #0f8f4c;
    }

    .pleno-certificate-actions .btn-outline-success {
        color: #0f8f4c;
        border-color: #0f8f4c;
    }

    .pleno-observation-text {
        margin: 0 0 1rem;
        color: #273f4f;
        font-size: 1.02rem;
        font-weight: 650;
        line-height: 1.55;
    }

    .pleno-observation-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.5rem;
        padding-top: 0.95rem;
        border-top: 1px solid #edf2f5;
        color: #657987;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .pleno-observation-meta strong {
        color: #203949;
    }

    @media (max-width: 1199.98px) {
        .pleno-session-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .pleno-resumen-grid,
        .pleno-resumen-lower-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .pleno-resumen-card-body {
            padding: 1rem;
        }

        .pleno-session-summary-grid,
        .pleno-attendance-grid,
        .pleno-certificate-actions {
            grid-template-columns: 1fr;
        }

        .pleno-results-table {
            min-width: 780px;
        }
    }
</style>

<div class="container-fluid mt-4 pleno-resumen-page">
    <div class="pleno-resumen-heading">
        <span class="pleno-resumen-heading-icon"><i class="fas fa-chart-pie"></i></span>
        <h1 class="pleno-resumen-title">Resumen</h1>
    </div>

    <?php if (!$sesion): ?>
        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-info-circle"></i>
                    Sin sesión disponible
                </h2>
                <p class="pleno-observation-text">No existe una sesión plenaria vigente para mostrar resumen.</p>
            </div>
        </section>
    <?php else: ?>
    <section class="pleno-resumen-card">
        <div class="pleno-resumen-card-body">
            <h2 class="pleno-resumen-card-title">
                <i class="fas fa-chart-line"></i>
                Resumen Ejecutivo
            </h2>
            <div class="pleno-session-summary-grid">
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Tipo de sesión</div>
                    <div class="pleno-session-value"><?php echo $h($formatearTipo($sesion['tipo_pleno'] ?? null)); ?></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Número de sesión</div>
                    <div class="pleno-session-value"><?php echo $h($sesion['numero_sesion'] ?? 'Sin número'); ?></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Fecha</div>
                    <div class="pleno-session-value"><?php echo $h($formatearFecha($sesion['fecha'] ?? null)); ?></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Estado</div>
                    <div class="pleno-session-value"><span class="pleno-badge-finalizado"><?php echo $h($formatearEstado($sesion['estado'] ?? null)); ?></span></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Habilitados</div>
                    <div class="pleno-session-value"><?php echo (int)$totalHabilitados; ?></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Presentes</div>
                    <div class="pleno-session-value"><?php echo (int)$presentes; ?></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Ausentes</div>
                    <div class="pleno-session-value"><?php echo (int)$ausentes; ?></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Puntos tratados</div>
                    <div class="pleno-session-value"><?php echo (int)$totalPuntosTratados; ?></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Puntos votados</div>
                    <div class="pleno-session-value"><?php echo (int)$totalPuntosVotados; ?></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Puntos informativos</div>
                    <div class="pleno-session-value"><?php echo (int)$totalPuntosInformativos; ?></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Votaciones realizadas</div>
                    <div class="pleno-session-value"><?php echo (int)$totalVotacionesRealizadas; ?></div>
                </div>
                <div class="pleno-session-summary-item">
                    <div class="pleno-session-label">Votos emitidos</div>
                    <div class="pleno-session-value"><?php echo (int)$totalVotosEmitidos; ?></div>
                </div>
            </div>
            <p class="pleno-observation-text mt-3"><?php echo $h($textoResumenEjecutivo); ?></p>
        </div>
    </section>

    <div class="pleno-resumen-grid">
        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-calendar-check"></i>
                    Resumen de la Sesión
                </h2>
                <div class="pleno-session-summary-grid">
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Número de sesión</div>
                        <div class="pleno-session-value"><?php echo $h($sesion['numero_sesion'] ?? 'Sin número'); ?></div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Tipo de pleno</div>
                        <div class="pleno-session-value"><?php echo $h($formatearTipo($sesion['tipo_pleno'] ?? null)); ?></div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Fecha</div>
                        <div class="pleno-session-value"><?php echo $h($formatearFecha($sesion['fecha'] ?? null)); ?></div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Hora inicio</div>
                        <div class="pleno-session-value"><?php echo $h($formatearHora($sesion['hora'] ?? null)); ?></div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Hora término</div>
                        <div class="pleno-session-value">
                            <?php if ($estadoSesionResumen === 'finalizada'): ?>
                                <?php echo $h($formatearFechaHora($sesion['fecha_actualizacion'] ?? null)); ?>
                            <?php elseif ($estadoSesionResumen === 'en_curso'): ?>
                                En curso
                            <?php else: ?>
                                Sin cierre
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Estado</div>
                        <div class="pleno-session-value"><span class="pleno-badge-finalizado"><?php echo $h($formatearEstado($sesion['estado'] ?? null)); ?></span></div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Lugar</div>
                        <div class="pleno-session-value"><?php echo $h($sesion['lugar'] ?? 'Sin lugar'); ?></div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Observaciones</div>
                        <div class="pleno-session-value"><?php echo $h(trim((string)($sesion['observaciones'] ?? '')) ?: 'Sin observaciones'); ?></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-users"></i>
                    Asistencia Final
                </h2>
                <div class="pleno-attendance-grid">
                    <div class="pleno-attendance-tile pleno-attendance-green">
                        <span class="pleno-attendance-icon"><i class="fas fa-users"></i></span>
                        <div>
                            <div class="pleno-attendance-number"><?php echo (int)$presentes; ?></div>
                            <div class="pleno-attendance-text">Presentes</div>
                        </div>
                    </div>
                    <div class="pleno-attendance-tile pleno-attendance-orange">
                        <span class="pleno-attendance-icon"><i class="fas fa-user-minus"></i></span>
                        <div>
                            <div class="pleno-attendance-number"><?php echo (int)$ausentes; ?></div>
                            <div class="pleno-attendance-text">Ausentes</div>
                        </div>
                    </div>
                    <div class="pleno-attendance-tile pleno-attendance-purple">
                        <span class="pleno-attendance-icon"><i class="fas fa-clipboard-check"></i></span>
                        <div>
                            <div class="pleno-attendance-number"><?php echo (int)$totalHabilitados; ?></div>
                            <div class="pleno-attendance-text">Total habilitados</div>
                        </div>
                    </div>
                    <div class="pleno-attendance-tile pleno-attendance-blue">
                        <span class="pleno-attendance-icon"><i class="fas fa-percent"></i></span>
                        <div>
                            <div class="pleno-attendance-number"><?php echo $h(number_format($porcentajeParticipacion, 1, ',', '.')); ?>%</div>
                            <div class="pleno-attendance-text">Participación</div>
                        </div>
                    </div>
                    <div class="pleno-attendance-tile pleno-attendance-orange">
                        <span class="pleno-attendance-icon"><i class="fas fa-chart-pie"></i></span>
                        <div>
                            <div class="pleno-attendance-number"><?php echo $h(number_format($porcentajeAusentismo, 1, ',', '.')); ?>%</div>
                            <div class="pleno-attendance-text">Ausentismo</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="pleno-resumen-card">
        <div class="pleno-resumen-card-body">
            <h2 class="pleno-resumen-card-title">
                <i class="fas fa-user-check"></i>
                Lista de Asistentes
            </h2>
            <?php if (empty($participantes)): ?>
                <p class="pleno-observation-text">No hay asistentes registrados para esta sesión.</p>
            <?php else: ?>
                <div class="pleno-results-table-wrap">
                    <table class="pleno-results-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Hora registro</th>
                                <th>Origen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participantes as $participante): ?>
                                <?php $estadoAsistencia = strtoupper(trim((string)($participante['estadoAsistencia'] ?? 'AUSENTE'))) ?: 'AUSENTE'; ?>
                                <tr>
                                    <td><?php echo $h($participante['nombreCompleto'] ?? 'Sin nombre'); ?></td>
                                    <td>
                                        <span class="pleno-status-badge <?php echo $estadoAsistencia === 'PRESENTE' ? 'pleno-status-present' : 'pleno-status-absent'; ?>">
                                            <?php echo $h($estadoAsistencia === 'PRESENTE' ? 'Presente' : 'Ausente'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $h($formatearFechaHora($participante['fechaRegistroAsistencia'] ?? null)); ?></td>
                                    <td><?php echo $h(trim((string)($participante['origenAsistencia'] ?? '')) ?: 'Sin origen'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="pleno-resumen-card">
        <div class="pleno-resumen-card-body">
            <h2 class="pleno-resumen-card-title">
                <i class="fas fa-poll"></i>
                Resultados por Punto Tratado
            </h2>
            <?php if (empty($resultados)): ?>
                <p class="pleno-observation-text">No hay puntos tratados registrados para esta sesión.</p>
            <?php else: ?>
                <div class="pleno-results-table-wrap">
                    <table class="pleno-results-table">
                        <thead>
                            <tr>
                                <th>Punto</th>
                                <th>Resultado</th>
                                <th>Sí</th>
                                <th>No</th>
                                <th>Abstención</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $indice => $resultado): ?>
                                <tr>
                                    <td><?php echo $h($resultado['numero'] . '. ' . $resultado['titulo']); ?></td>
                                    <td>
                                        <span class="pleno-result-badge <?php echo $h($claseResultado((string)$resultado['resultado'])); ?>">
                                            <?php echo $h($resultado['resultado']); ?>
                                        </span>
                                    </td>
                                    <td class="pleno-vote-favor"><?php echo (int)$resultado['si']; ?></td>
                                    <td class="pleno-vote-contra"><?php echo (int)$resultado['no']; ?></td>
                                    <td class="pleno-vote-abstencion"><?php echo (int)$resultado['abstencion']; ?></td>
                                    <td><?php echo (int)$resultado['total']; ?></td>
                                </tr>
                                <?php if (!empty($resultado['votos'])): ?>
                                    <tr>
                                        <td colspan="6">
                                            <details>
                                                <summary>Ver detalle de votos</summary>
                                                <div class="pleno-results-table-wrap mt-3">
                                                    <table class="pleno-results-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Consejero</th>
                                                                <th>Voto emitido</th>
                                                                <th>Hora</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($resultado['votos'] as $voto): ?>
                                                                <tr>
                                                                    <td><?php echo $h($voto['consejero'] ?? 'Sin nombre'); ?></td>
                                                                    <td>
                                                                        <span class="<?php echo $h($claseBadgeVoto($voto['opcionVoto'] ?? '')); ?>">
                                                                            <?php echo $h($voto['opcionVoto'] ?? 'Sin voto'); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td><?php echo $h($formatearFechaHora($voto['hora'] ?? null)); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </details>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="pleno-resumen-lower-grid">
        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-check-double"></i>
                    Acuerdos Adoptados
                </h2>
                <p class="pleno-observation-text">No hay acuerdos registrados para esta sesión.</p>
            </div>
        </section>

        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-file-signature"></i>
                    Certificado de Acuerdos
                </h2>
                <div class="pleno-certificate-box">
                    <span class="pleno-pdf-icon"><i class="fas fa-file-pdf"></i></span>
                    <div>
                        <div class="pleno-certificate-name">No hay certificado generado.</div>
                        <div class="pleno-certificate-date">El certificado se mostrará cuando sea generado.</div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="pleno-resumen-card">
        <div class="pleno-resumen-card-body">
            <h2 class="pleno-resumen-card-title">
                <i class="fas fa-comment-dots"></i>
                Observaciones Finales
            </h2>
            <p class="pleno-observation-text"><?php echo $h(trim((string)($sesion['observaciones'] ?? '')) ?: 'Sin observaciones registradas para esta sesión.'); ?></p>
            <div class="pleno-observation-meta">
                <span>Estado: <strong><?php echo $h($formatearEstado($sesion['estado'] ?? null)); ?></strong></span>
                <span>Última actualización: <strong><?php echo $h($formatearFechaHora($sesion['fecha_actualizacion'] ?? $sesion['fecha_creacion'] ?? null)); ?></strong></span>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>
