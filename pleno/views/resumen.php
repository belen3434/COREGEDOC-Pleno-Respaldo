<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$sesion = $data['pleno_sesion_actual'] ?? null;
$asistenciaResumen = $data['pleno_asistencia_resumen'] ?? null;
$resultados = $data['pleno_resumen_resultados'] ?? [];
$acuerdos = $data['pleno_acuerdos'] ?? [];
$puntosAcuerdo = $data['pleno_puntos_acuerdo'] ?? [];
$certificadoAcuerdos = $data['pleno_certificado_acuerdos'] ?? [
    'total_acuerdos' => 0,
    'existen_acuerdos' => false,
    'certificado' => null,
    'existe_certificado' => false,
];
$resumenEjecutivoSesion = $data['pleno_resumen_ejecutivo'] ?? [
    'resumen' => null,
    'existe_resumen' => false,
];
$puedeGestionar = (bool)($data['pleno_puede_gestionar'] ?? false);
$puedeGenerarCertificados = (bool)($data['pleno_puede_generar_certificados'] ?? false);
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

    if ($resultado === 'Sin votos') {
        return 'pleno-result-warning';
    }

    if ($resultado === 'Empate') {
        return 'pleno-result-neutral';
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
$totalAcuerdosCertificado = (int)($certificadoAcuerdos['total_acuerdos'] ?? 0);
$existenAcuerdosCertificado = !empty($certificadoAcuerdos['existen_acuerdos']);
$certificadoActual = is_array($certificadoAcuerdos['certificado'] ?? null) ? $certificadoAcuerdos['certificado'] : null;
$existeCertificadoAcuerdos = !empty($certificadoAcuerdos['existe_certificado']) && $certificadoActual !== null;
$idCertificadoActual = (int)($certificadoActual['id_certificado'] ?? ($certificadoActual['raw']['id_certificado'] ?? 0));
$resumenEjecutivoActual = is_array($resumenEjecutivoSesion['resumen'] ?? null) ? $resumenEjecutivoSesion['resumen'] : null;
$existeResumenEjecutivo = !empty($resumenEjecutivoSesion['existe_resumen']) && $resumenEjecutivoActual !== null;
$idResumenEjecutivoActual = (int)($resumenEjecutivoActual['id_resumen'] ?? ($resumenEjecutivoActual['raw']['id_resumen'] ?? 0));
$observacionesFinales = trim((string)($sesion['observaciones_finales'] ?? ''));
$observacionesFinalesFecha = $sesion['observaciones_finales_fecha'] ?? null;

foreach ($resultados as $resultadoResumen) {
    $esInformativoResumen = !empty($resultadoResumen['es_informativo']) || (string)($resultadoResumen['resultado'] ?? '') === 'Exposición';
    $estadoVotacionResumen = (string)($resultadoResumen['estado_votacion'] ?? '');
    $tuvoVotacionResumen = !empty($resultadoResumen['tuvo_votacion'])
        || in_array($estadoVotacionResumen, ['votacion_en_curso', 'votacion_cerrada'], true);
    $votacionCerradaResumen = $estadoVotacionResumen === 'votacion_cerrada';

    if ($esInformativoResumen) {
        $totalPuntosInformativos++;
    }

    if ($tuvoVotacionResumen) {
        $totalPuntosVotados++;
    }

    if ($votacionCerradaResumen) {
        $totalVotacionesRealizadas++;
    }

    if ($votacionCerradaResumen) {
        $totalVotosEmitidos += (int)($resultadoResumen['total'] ?? 0);
    }
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

    .pleno-agreement-content {
        flex: 1;
        min-width: 0;
    }

    .pleno-agreement-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem 0.85rem;
        margin-top: 0.55rem;
        color: #657987;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .pleno-agreement-point {
        margin: 0.1rem 0 0.45rem;
        color: #203949;
        font-weight: 800;
    }

    .pleno-agreement-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.75rem;
    }

    .pleno-agreement-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .pleno-agreement-header .pleno-resumen-card-title {
        margin-bottom: 0;
    }

    .pleno-agreement-form-label {
        margin-bottom: 0.35rem;
        color: #657987;
        font-size: 0.76rem;
        font-weight: 850;
        text-transform: uppercase;
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

    .pleno-final-observation-form {
        display: grid;
        gap: 0.9rem;
    }

    .pleno-final-observation-textarea {
        min-height: 150px;
        border: 1px solid #d9e4df;
        border-radius: 10px;
        color: #1f3240;
        background: #ffffff;
        resize: vertical;
    }

    .pleno-final-observation-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .pleno-executive-kpi-grid {
        display: grid;
        gap: 0.9rem;
    }

    .pleno-executive-kpi-row-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .pleno-executive-kpi-row-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-top: 0.95rem;
    }

    .pleno-kpi-card {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        min-height: 96px;
        padding: 1rem;
        border: 1px solid #e4edf2;
        border-radius: 14px;
        background: #f9fcfd;
    }

    .pleno-kpi-card-large {
        min-height: 118px;
    }

    .pleno-kpi-green {
        background: #eef9f2;
        border-color: #d8efdf;
    }

    .pleno-kpi-orange {
        background: #fff6e8;
        border-color: #f6dfb9;
    }

    .pleno-kpi-purple {
        background: #f2f0ff;
        border-color: #ded9fb;
    }

    .pleno-kpi-blue {
        background: #eef6ff;
        border-color: #d7e9fb;
    }

    .pleno-kpi-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        border-radius: 50%;
        color: #ffffff;
        background: #0f8f4c;
        font-size: 1rem;
    }

    .pleno-kpi-orange .pleno-kpi-icon {
        background: #f28c18;
    }

    .pleno-kpi-purple .pleno-kpi-icon {
        background: #6055d8;
    }

    .pleno-kpi-blue .pleno-kpi-icon {
        background: #0d6efd;
    }

    .pleno-kpi-label {
        margin-bottom: 0.22rem;
        color: #657987;
        font-size: 0.76rem;
        font-weight: 850;
        text-transform: uppercase;
    }

    .pleno-kpi-value {
        color: #182f3f;
        font-size: 1.45rem;
        font-weight: 900;
        line-height: 1.08;
    }

    .pleno-kpi-card-large .pleno-kpi-value {
        font-size: 2rem;
    }

    .pleno-dashboard-accordion {
        display: grid;
        gap: 0.85rem;
        margin-top: 1.25rem;
    }

    .pleno-accordion-item {
        overflow: hidden;
        border: 1px solid #e2ebf0;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 8px 22px rgba(15, 38, 56, 0.06);
    }

    .pleno-accordion-button {
        width: 100%;
        min-height: 58px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.95rem 1.15rem;
        border: 0;
        background: #ffffff;
        color: #203949;
        font-weight: 850;
        text-align: left;
    }

    .pleno-accordion-button i {
        color: #0f8f4c;
    }

    .pleno-accordion-body {
        padding: 1.15rem;
        border-top: 1px solid #edf2f5;
    }

    .pleno-accordion-count {
        color: #657987;
        font-size: 0.86rem;
        font-weight: 750;
    }

    .pleno-cert-layout .pleno-certificate-actions,
    #plenoCertificadoAcuerdosCard .pleno-certificate-actions,
    #plenoResumenEjecutivoCard .pleno-certificate-actions {
        grid-template-columns: 1fr;
    }

    @media (max-width: 1199.98px) {
        .pleno-session-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .pleno-executive-kpi-row-4,
        .pleno-executive-kpi-row-3 {
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
        .pleno-executive-kpi-row-4,
        .pleno-executive-kpi-row-3,
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
        <h1 class="pleno-resumen-title">Resumen de Sesi&oacute;n</h1>
    </div>

    <?php if (!$sesion): ?>
        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-info-circle"></i>
                    Sin sesi&oacute;n disponible
                </h2>
                <p class="pleno-observation-text">No existe una sesi&oacute;n plenaria vigente para mostrar resumen.</p>
            </div>
        </section>
    <?php else: ?>
    <section class="pleno-resumen-card">
        <div class="pleno-resumen-card-body">
            <h2 class="pleno-resumen-card-title">
                <i class="fas fa-chart-line"></i>
                Resumen Ejecutivo
            </h2>
            <div class="pleno-executive-kpi-grid pleno-executive-kpi-row-4">
                <div class="pleno-kpi-card pleno-kpi-blue">
                    <span class="pleno-kpi-icon"><i class="fas fa-hashtag"></i></span>
                    <div>
                        <div class="pleno-kpi-label">N&uacute;mero de sesi&oacute;n</div>
                        <div class="pleno-kpi-value"><?php echo $h($sesion['numero_sesion'] ?? 'PL-XXXX'); ?></div>
                    </div>
                </div>
                <div class="pleno-kpi-card pleno-kpi-green">
                    <span class="pleno-kpi-icon"><i class="fas fa-check-circle"></i></span>
                    <div>
                        <div class="pleno-kpi-label">Estado</div>
                        <div class="pleno-kpi-value"><span class="pleno-badge-finalizado"><?php echo $h($formatearEstado($sesion['estado'] ?? null)); ?></span></div>
                    </div>
                </div>
                <div class="pleno-kpi-card pleno-kpi-blue">
                    <span class="pleno-kpi-icon"><i class="fas fa-calendar-day"></i></span>
                    <div>
                        <div class="pleno-kpi-label">Fecha</div>
                        <div class="pleno-kpi-value"><?php echo $h($formatearFecha($sesion['fecha'] ?? null)); ?></div>
                    </div>
                </div>
                <div class="pleno-kpi-card pleno-kpi-green">
                    <span class="pleno-kpi-icon"><i class="fas fa-percent"></i></span>
                    <div>
                        <div class="pleno-kpi-label">Participaci&oacute;n</div>
                        <div class="pleno-kpi-value"><?php echo $h(number_format($porcentajeParticipacion, 1, ',', '.')); ?>%</div>
                    </div>
                </div>
            </div>
            <div class="pleno-executive-kpi-grid pleno-executive-kpi-row-3">
                <div class="pleno-kpi-card pleno-kpi-card-large pleno-kpi-green">
                    <span class="pleno-kpi-icon"><i class="fas fa-users"></i></span>
                    <div>
                        <div class="pleno-kpi-label">Presentes</div>
                        <div class="pleno-kpi-value"><?php echo (int)$presentes; ?></div>
                    </div>
                </div>
                <div class="pleno-kpi-card pleno-kpi-card-large pleno-kpi-orange">
                    <span class="pleno-kpi-icon"><i class="fas fa-user-minus"></i></span>
                    <div>
                        <div class="pleno-kpi-label">Ausentes</div>
                        <div class="pleno-kpi-value"><?php echo (int)$ausentes; ?></div>
                    </div>
                </div>
                <div class="pleno-kpi-card pleno-kpi-card-large pleno-kpi-purple">
                    <span class="pleno-kpi-icon"><i class="fas fa-user-friends"></i></span>
                    <div>
                        <div class="pleno-kpi-label">Total habilitados</div>
                        <div class="pleno-kpi-value"><?php echo (int)$totalHabilitados; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="pleno-dashboard-accordion" id="plenoResumenTopAccordion">
        <section class="pleno-accordion-item">
            <button class="pleno-accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#plenoInfoSesionCollapse" aria-expanded="false" aria-controls="plenoInfoSesionCollapse">
                <span><i class="fas fa-calendar-check me-2"></i>Informaci&oacute;n de la sesi&oacute;n</span>
                <span class="pleno-accordion-count">Detalle general</span>
            </button>
            <div id="plenoInfoSesionCollapse" class="collapse" data-bs-parent="#plenoResumenTopAccordion">
                <div class="pleno-accordion-body">
                    <div class="pleno-session-summary-grid">
                        <div class="pleno-session-summary-item">
                            <div class="pleno-session-label">N&uacute;mero sesi&oacute;n</div>
                            <div class="pleno-session-value"><?php echo $h($sesion['numero_sesion'] ?? 'Sin n&uacute;mero'); ?></div>
                        </div>
                        <div class="pleno-session-summary-item">
                            <div class="pleno-session-label">Tipo pleno</div>
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
                            <div class="pleno-session-label">Hora t&eacute;rmino</div>
                            <div class="pleno-session-value">
                                <?php if ($estadoSesionResumen === 'finalizada'): ?>
                                    <?php echo $h($formatearFechaHora($sesion['fecha_actualizacion'] ?? null)); ?>
                                <?php elseif ($estadoSesionResumen === 'en_curso'): ?>
                                    -
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
            </div>
        </section>
        <section class="pleno-accordion-item">
            <button class="pleno-accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#plenoResultadosCollapse" aria-expanded="false" aria-controls="plenoResultadosCollapse">
                <span><i class="fas fa-poll me-2"></i>Resultados de la sesi&oacute;n</span>
                <span class="pleno-accordion-count"><?php echo (int)$totalPuntosTratados; ?> puntos</span>
            </button>
            <div id="plenoResultadosCollapse" class="collapse" data-bs-parent="#plenoResumenTopAccordion">
                <div class="pleno-accordion-body">
                    <?php if (empty($resultados)): ?>
                        <p class="pleno-observation-text">No hay puntos tratados registrados para esta sesi&oacute;n.</p>
                    <?php else: ?>
                        <div class="pleno-results-table-wrap">
                            <table class="pleno-results-table">
                                <thead>
                                    <tr>
                                        <th>Punto</th>
                                        <th>Resultado</th>
                                        <th>S&iacute;</th>
                                        <th>No</th>
                                        <th>Abstenci&oacute;n</th>
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
            </div>
        </section>
    </div>
    <?php if ($puedeGestionar): ?>
        <div class="modal fade" id="plenoAcuerdoModal" tabindex="-1" aria-labelledby="plenoAcuerdoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form id="plenoAcuerdoForm">
                        <div class="modal-header">
                            <h2 class="modal-title h5" id="plenoAcuerdoModalLabel">Registrar Acuerdo</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_sesion" value="<?php echo (int)($sesion['id_sesion'] ?? 0); ?>">
                            <div class="mb-3">
                                <label class="pleno-agreement-form-label" for="plenoAcuerdoPunto">Punto asociado</label>
                                <select class="form-select" id="plenoAcuerdoPunto" name="punto_numero" required>
                                    <option value="">Seleccione un punto</option>
                                    <?php foreach ($puntosAcuerdo as $puntoAcuerdo): ?>
                                        <option value="<?php echo $h($puntoAcuerdo['punto_numero'] ?? ''); ?>">
                                            Punto <?php echo $h($puntoAcuerdo['punto_numero'] ?? ''); ?> - <?php echo $h($puntoAcuerdo['titulo_punto'] ?? 'Sin titulo'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="pleno-agreement-form-label" for="plenoAcuerdoTexto">Texto del acuerdo</label>
                                <textarea class="form-control" id="plenoAcuerdoTexto" name="texto_acuerdo" rows="5" required></textarea>
                            </div>
                            <div class="alert alert-danger d-none mt-3 mb-0" id="plenoAcuerdoError" role="alert"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Guardar acuerdo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="plenoEditarAcuerdoModal" tabindex="-1" aria-labelledby="plenoEditarAcuerdoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form id="plenoEditarAcuerdoForm">
                        <div class="modal-header">
                            <h2 class="modal-title h5" id="plenoEditarAcuerdoModalLabel">Editar Acuerdo</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_acuerdo" id="plenoEditarAcuerdoId">
                            <div>
                                <label class="pleno-agreement-form-label" for="plenoEditarAcuerdoTexto">Texto del acuerdo</label>
                                <textarea class="form-control" id="plenoEditarAcuerdoTexto" name="texto_acuerdo" rows="5" required></textarea>
                            </div>
                            <div class="alert alert-danger d-none mt-3 mb-0" id="plenoEditarAcuerdoError" role="alert"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Actualizar acuerdo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="pleno-resumen-lower-grid">
        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <div class="pleno-agreement-header">
                    <h2 class="pleno-resumen-card-title">
                        <i class="fas fa-check-double"></i>
                        Acuerdos Adoptados
                    </h2>
                    <?php if ($puedeGestionar): ?>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#plenoAcuerdoModal">
                            <i class="fas fa-plus me-1"></i>
                            Registrar Acuerdo
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (empty($acuerdos)): ?>
                    <p class="pleno-observation-text" id="plenoAcuerdosEmpty">No hay acuerdos registrados para esta sesi&oacute;n.</p>
                <?php else: ?>
                    <ul class="pleno-agreements-list" id="plenoAcuerdosList">
                        <?php foreach ($acuerdos as $indiceAcuerdo => $acuerdo): ?>
                            <li class="pleno-agreement-card">
                                <span class="pleno-agreement-check"><i class="fas fa-check"></i></span>
                                <div class="pleno-agreement-content">
                                    <div class="pleno-agreement-number">Acuerdo N&deg; <?php echo (int)$indiceAcuerdo + 1; ?></div>
                                    <div class="pleno-agreement-point">
                                        Punto <?php echo $h($acuerdo['punto_numero'] ?? 'Sin punto'); ?> -
                                        <?php echo $h($acuerdo['titulo_punto'] ?? 'Sin t&iacute;tulo'); ?>
                                    </div>
                                    <div class="pleno-agreement-text"><?php echo nl2br($h($acuerdo['texto_acuerdo'] ?? '')); ?></div>
                                    <div class="pleno-agreement-meta">
                                        <span>Fecha creaci&oacute;n: <?php echo $h($formatearFechaHora($acuerdo['fecha_creacion'] ?? null)); ?></span>
                                        <span>Estado: <?php echo $h(ucfirst((string)($acuerdo['estado'] ?? 'vigente'))); ?></span>
                                    </div>
                                    <?php if ($puedeGestionar): ?>
                                        <div class="pleno-agreement-actions">
                                            <button
                                                type="button"
                                                class="btn btn-outline-success btn-sm pleno-edit-agreement-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#plenoEditarAcuerdoModal"
                                                data-id-acuerdo="<?php echo (int)($acuerdo['id_acuerdo'] ?? 0); ?>"
                                                data-texto="<?php echo $h($acuerdo['texto_acuerdo'] ?? ''); ?>"
                                            >
                                                <i class="fas fa-pen me-1"></i>
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-sm pleno-delete-agreement-btn"
                                                data-id-acuerdo="<?php echo (int)($acuerdo['id_acuerdo'] ?? 0); ?>"
                                            >
                                                <i class="fas fa-trash me-1"></i>
                                                Eliminar
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>

        <section class="pleno-resumen-card" id="plenoCertificadoAcuerdosCard" data-id-sesion="<?php echo (int)($sesion['id_sesion'] ?? 0); ?>">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-file-signature"></i>
                    Certificado de Acuerdos
                </h2>
                <div class="alert d-none mb-3 pleno-certificate-feedback" role="alert"></div>
                <?php if (!$existenAcuerdosCertificado): ?>
                    <div class="pleno-certificate-box">
                        <span class="pleno-pdf-icon"><i class="fas fa-file-pdf"></i></span>
                        <div>
                            <div class="pleno-certificate-name">No hay acuerdos registrados para generar certificado.</div>
                            <div class="pleno-certificate-date">Total acuerdos: 0</div>
                        </div>
                    </div>
                    <?php if ($puedeGenerarCertificados): ?>
                        <div class="pleno-certificate-actions">
                            <button type="button" class="btn btn-success" disabled>
                                <i class="fas fa-file-export me-1"></i>
                                Generar Certificado
                            </button>
                        </div>
                    <?php endif; ?>
                <?php elseif (!$existeCertificadoAcuerdos): ?>
                    <div class="pleno-certificate-box">
                        <span class="pleno-pdf-icon"><i class="fas fa-file-pdf"></i></span>
                        <div>
                            <div class="pleno-certificate-name">No hay certificado generado.</div>
                            <div class="pleno-certificate-date">Total acuerdos: <?php echo (int)$totalAcuerdosCertificado; ?></div>
                        </div>
                    </div>
                    <?php if ($puedeGenerarCertificados): ?>
                        <div class="pleno-certificate-actions">
                            <button type="button" class="btn btn-success pleno-certificate-generate-btn" data-certificate-mode="first">
                                <i class="fas fa-file-export me-1"></i>
                                Generar Certificado
                            </button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="pleno-certificate-box">
                        <span class="pleno-pdf-icon"><i class="fas fa-file-pdf"></i></span>
                        <div>
                            <div class="pleno-certificate-name">Versi&oacute;n <?php echo $h($certificadoActual['version'] ?? '1'); ?></div>
                            <div class="pleno-certificate-date">Fecha generaci&oacute;n: <?php echo $h($formatearFechaHora($certificadoActual['fecha_generacion'] ?? null)); ?></div>
                            <div class="pleno-agreement-meta">
                                <span>Usuario generador: <?php echo $h($certificadoActual['usuario_generador'] ?? 'Sin registro'); ?></span>
                                <span>Total acuerdos: <?php echo (int)($certificadoActual['total_acuerdos'] ?? $totalAcuerdosCertificado); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="pleno-certificate-actions">
                        <a
                            class="btn btn-outline-success"
                            href="certificados/ver.php?id_certificado=<?php echo (int)$idCertificadoActual; ?>"
                            target="_blank"
                            rel="noopener"
                        >
                            <i class="fas fa-eye me-1"></i>
                            Ver
                        </a>
                        <a
                            class="btn btn-outline-success"
                            href="certificados/descargar.php?id_certificado=<?php echo (int)$idCertificadoActual; ?>"
                        >
                            <i class="fas fa-download me-1"></i>
                            Descargar
                        </a>
                        <?php if ($puedeGenerarCertificados): ?>
                            <button type="button" class="btn btn-success pleno-certificate-generate-btn" data-certificate-mode="version">
                                <i class="fas fa-plus me-1"></i>
                                Generar nueva versi&oacute;n
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="pleno-resumen-card" id="plenoResumenEjecutivoCard" data-id-sesion="<?php echo (int)($sesion['id_sesion'] ?? 0); ?>">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-file-alt"></i>
                    Resumen Ejecutivo de la Sesi&oacute;n
                </h2>
                <div class="alert d-none mb-3 pleno-summary-feedback" role="alert"></div>
                <?php if (!$existeResumenEjecutivo): ?>
                    <div class="pleno-certificate-box">
                        <span class="pleno-pdf-icon"><i class="fas fa-file-pdf"></i></span>
                        <div>
                            <div class="pleno-certificate-name">No hay resumen ejecutivo generado.</div>
                            <div class="pleno-certificate-date">Se consolidar&aacute; asistencia, orden del d&iacute;a, votaciones y acuerdos.</div>
                        </div>
                    </div>
                    <?php if ($puedeGenerarCertificados): ?>
                        <div class="pleno-certificate-actions">
                            <button type="button" class="btn btn-success pleno-summary-generate-btn" data-summary-mode="first">
                                <i class="fas fa-file-export me-1"></i>
                                Generar resumen
                            </button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="pleno-certificate-box">
                        <span class="pleno-pdf-icon"><i class="fas fa-file-pdf"></i></span>
                        <div>
                            <div class="pleno-certificate-name">Versi&oacute;n <?php echo $h($resumenEjecutivoActual['version'] ?? '1'); ?></div>
                            <div class="pleno-certificate-date">Fecha generaci&oacute;n: <?php echo $h($formatearFechaHora($resumenEjecutivoActual['fecha_generacion'] ?? null)); ?></div>
                            <div class="pleno-agreement-meta">
                                <span>Usuario generador: <?php echo $h($resumenEjecutivoActual['usuario_generador'] ?? 'Sin registro'); ?></span>
                                <span>Acuerdos: <?php echo (int)($resumenEjecutivoActual['total_acuerdos'] ?? 0); ?></span>
                                <span>Votaciones: <?php echo (int)($resumenEjecutivoActual['total_votaciones'] ?? 0); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="pleno-certificate-actions">
                        <a
                            class="btn btn-outline-success"
                            href="resumenes/ver.php?id_resumen=<?php echo (int)$idResumenEjecutivoActual; ?>"
                            target="_blank"
                            rel="noopener"
                        >
                            <i class="fas fa-eye me-1"></i>
                            Ver
                        </a>
                        <a
                            class="btn btn-outline-success"
                            href="resumenes/descargar.php?id_resumen=<?php echo (int)$idResumenEjecutivoActual; ?>"
                        >
                            <i class="fas fa-download me-1"></i>
                            Descargar
                        </a>
                        <?php if ($puedeGenerarCertificados): ?>
                            <button type="button" class="btn btn-success pleno-summary-generate-btn" data-summary-mode="version">
                                <i class="fas fa-plus me-1"></i>
                                Generar nueva versi&oacute;n
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <div class="pleno-dashboard-accordion" id="plenoResumenBottomAccordion">
        <section class="pleno-accordion-item">
            <button class="pleno-accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#plenoAsistentesCollapse" aria-expanded="false" aria-controls="plenoAsistentesCollapse">
                <span><i class="fas fa-user-check me-2"></i>Lista de asistentes (<?php echo count($participantes); ?>)</span>
                <span class="pleno-accordion-count"><?php echo (int)$presentes; ?> presentes</span>
            </button>
            <div id="plenoAsistentesCollapse" class="collapse" data-bs-parent="#plenoResumenBottomAccordion">
                <div class="pleno-accordion-body">
                    <?php if (empty($participantes)): ?>
                        <p class="pleno-observation-text">No hay asistentes registrados para esta sesi&oacute;n.</p>
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
            </div>
        </section>
        <section class="pleno-accordion-item">
            <button class="pleno-accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#plenoObservacionesCollapse" aria-expanded="false" aria-controls="plenoObservacionesCollapse">
                <span><i class="fas fa-comment-dots me-2"></i>Observaciones finales</span>
                <span class="pleno-accordion-count">Detalle</span>
            </button>
            <div id="plenoObservacionesCollapse" class="collapse" data-bs-parent="#plenoResumenBottomAccordion">
                <div class="pleno-accordion-body">
                    <?php if ($puedeGestionar): ?>
                        <form class="pleno-final-observation-form" id="plenoObservacionesFinalesForm">
                            <input type="hidden" name="id_sesion" value="<?php echo (int)($sesion['id_sesion'] ?? 0); ?>">
                            <textarea
                                class="form-control pleno-final-observation-textarea"
                                id="plenoObservacionesFinalesTexto"
                                name="observaciones_finales"
                                rows="6"
                                placeholder="Escriba las observaciones finales de la sesi&oacute;n"
                            ><?php echo $h($observacionesFinales); ?></textarea>
                            <div class="pleno-final-observation-actions">
                                <div class="alert d-none mb-0 flex-grow-1" id="plenoObservacionesFinalesFeedback" role="alert"></div>
                                <button type="submit" class="btn btn-success" id="plenoObservacionesFinalesGuardar">
                                    <i class="fas fa-save me-1"></i>
                                    Guardar observaciones
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <p class="pleno-observation-text" id="plenoObservacionesFinalesVista"><?php echo $h($observacionesFinales !== '' ? $observacionesFinales : 'Sin observaciones finales registradas para esta sesi&oacute;n.'); ?></p>
                    <?php endif; ?>
                    <div class="pleno-observation-meta">
                        <span>Estado: <strong><?php echo $h($formatearEstado($sesion['estado'] ?? null)); ?></strong></span>
                        <span>&Uacute;ltima actualizaci&oacute;n: <strong id="plenoObservacionesFinalesFecha"><?php echo $h($formatearFechaHora($observacionesFinalesFecha)); ?></strong></span>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        (function () {
            "use strict";

            var certificadoCard = document.getElementById("plenoCertificadoAcuerdosCard");
            var puedeGenerarCertificados = <?php echo $puedeGenerarCertificados ? 'true' : 'false'; ?>;

            function htmlEscape(valor) {
                return String(valor === null || valor === undefined ? "" : valor)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            function formatearFechaHora(valor) {
                var fecha = String(valor || "").trim();
                var partes = fecha.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
                if (!partes) {
                    return fecha || "Sin registro";
                }

                return partes[3] + "/" + partes[2] + "/" + partes[1] + " " + partes[4] + ":" + partes[5] + " hrs.";
            }

            function obtenerFeedbackCertificado() {
                if (!certificadoCard) {
                    return null;
                }

                var feedback = certificadoCard.querySelector(".pleno-certificate-feedback");
                if (feedback) {
                    return feedback;
                }

                var titulo = certificadoCard.querySelector(".pleno-resumen-card-title");
                if (!titulo) {
                    return null;
                }

                feedback = document.createElement("div");
                feedback.className = "alert d-none mb-3 pleno-certificate-feedback";
                feedback.setAttribute("role", "alert");
                titulo.insertAdjacentElement("afterend", feedback);

                return feedback;
            }

            function mostrarFeedbackCertificado(tipo, mensaje) {
                var feedback = obtenerFeedbackCertificado();
                if (!feedback) {
                    return;
                }

                feedback.className = "alert mb-3 pleno-certificate-feedback alert-" + tipo;
                feedback.textContent = mensaje;
            }

            function limpiarFeedbackCertificado() {
                var feedback = obtenerFeedbackCertificado();
                if (!feedback) {
                    return;
                }

                feedback.className = "alert d-none mb-3 pleno-certificate-feedback";
                feedback.textContent = "";
            }

            function ponerBotonGenerando(boton) {
                boton.setAttribute("data-original-html", boton.innerHTML);
                boton.disabled = true;
                boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Generando...';
            }

            function restaurarBotonGenerar(boton) {
                var original = boton.getAttribute("data-original-html");
                boton.disabled = false;
                if (original) {
                    boton.innerHTML = original;
                    boton.removeAttribute("data-original-html");
                }
            }

            function mostrarMensajeTemporal() {
                window.alert("Funcionalidad disponible en la siguiente etapa.");
            }

            function enlazarBotonesCertificado() {
                document.querySelectorAll(".pleno-certificate-placeholder-btn").forEach(function (boton) {
                    boton.addEventListener("click", mostrarMensajeTemporal);
                });

                document.querySelectorAll(".pleno-certificate-generate-btn").forEach(function (boton) {
                    boton.addEventListener("click", generarCertificado);
                });
            }

            function renderCertificado(certificado) {
                if (!certificadoCard || !certificado) {
                    return;
                }

                var body = certificadoCard.querySelector(".pleno-resumen-card-body");
                if (!body) {
                    return;
                }

                var idCertificado = parseInt(certificado.id_certificado || "0", 10);
                var verHref = "certificados/ver.php?id_certificado=" + encodeURIComponent(idCertificado);
                var descargarHref = "certificados/descargar.php?id_certificado=" + encodeURIComponent(idCertificado);
                var botonGenerar = puedeGenerarCertificados
                    ? '<button type="button" class="btn btn-success pleno-certificate-generate-btn" data-certificate-mode="version"><i class="fas fa-plus me-1"></i>Generar nueva versi&oacute;n</button>'
                    : '';

                body.innerHTML = ''
                    + '<h2 class="pleno-resumen-card-title">'
                    + '<i class="fas fa-file-signature"></i>'
                    + 'Certificado de Acuerdos'
                    + '</h2>'
                    + '<div class="alert d-none mb-3 pleno-certificate-feedback" role="alert"></div>'
                    + '<div class="pleno-certificate-box">'
                    + '<span class="pleno-pdf-icon"><i class="fas fa-file-pdf"></i></span>'
                    + '<div>'
                    + '<div class="pleno-certificate-name">Versi&oacute;n ' + htmlEscape(certificado.version || "1") + '</div>'
                    + '<div class="pleno-certificate-date">Fecha generaci&oacute;n: ' + htmlEscape(formatearFechaHora(certificado.fecha_generacion)) + '</div>'
                    + '<div class="pleno-agreement-meta">'
                    + '<span>Usuario generador: ' + htmlEscape(certificado.usuario_generador || "Sin registro") + '</span>'
                    + '<span>Total acuerdos: ' + htmlEscape(certificado.total_acuerdos || "0") + '</span>'
                    + '</div>'
                    + '</div>'
                    + '</div>'
                    + '<div class="pleno-certificate-actions">'
                    + '<a class="btn btn-outline-success" href="' + htmlEscape(verHref) + '" target="_blank" rel="noopener"><i class="fas fa-eye me-1"></i>Ver</a>'
                    + '<a class="btn btn-outline-success" href="' + htmlEscape(descargarHref) + '"><i class="fas fa-download me-1"></i>Descargar</a>'
                    + botonGenerar
                    + '</div>';

                enlazarBotonesCertificado();
            }

            function generarCertificado() {
                if (!certificadoCard) {
                    return;
                }

                if (!puedeGenerarCertificados) {
                    mostrarFeedbackCertificado("danger", "No tiene permisos para generar certificados.");
                    return;
                }

                var boton = this;
                var modo = boton.getAttribute("data-certificate-mode") || "version";
                var mensajeConfirmacion = modo === "first"
                    ? "¿Desea generar el certificado de acuerdos para esta sesión?"
                    : "¿Desea generar una nueva versión del certificado de acuerdos? La versión vigente actual será marcada como reemplazada.";

                if (!window.confirm(mensajeConfirmacion)) {
                    return;
                }

                var idSesion = parseInt(certificadoCard.getAttribute("data-id-sesion") || "0", 10);
                limpiarFeedbackCertificado();
                ponerBotonGenerando(boton);

                fetch("ajax/generar_certificado_acuerdos.php", {
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
                            var errorBackend = new Error(payload && payload.mensaje ? payload.mensaje : "No fue posible generar el certificado.");
                            errorBackend.esRespuestaBackend = true;
                            throw errorBackend;
                        }

                        return payload;
                    });
                }).then(function (payload) {
                    renderCertificado(payload.certificado);
                    mostrarFeedbackCertificado("success", "Certificado generado correctamente.");
                }).catch(function (error) {
                    restaurarBotonGenerar(boton);
                    mostrarFeedbackCertificado(
                        "danger",
                        error && error.esRespuestaBackend ? error.message : "No se pudo generar el certificado. Intente nuevamente."
                    );
                });
            }

            enlazarBotonesCertificado();
        }());

        (function () {
            "use strict";

            var resumenCard = document.getElementById("plenoResumenEjecutivoCard");
            var puedeGenerarResumen = <?php echo $puedeGenerarCertificados ? 'true' : 'false'; ?>;

            function htmlEscape(valor) {
                return String(valor === null || valor === undefined ? "" : valor)
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            function formatearFechaHora(valor) {
                var fecha = String(valor || "").trim();
                var partes = fecha.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
                if (!partes) {
                    return fecha || "Sin registro";
                }

                return partes[3] + "/" + partes[2] + "/" + partes[1] + " " + partes[4] + ":" + partes[5] + " hrs.";
            }

            function obtenerFeedbackResumen() {
                if (!resumenCard) {
                    return null;
                }

                var feedback = resumenCard.querySelector(".pleno-summary-feedback");
                if (feedback) {
                    return feedback;
                }

                var titulo = resumenCard.querySelector(".pleno-resumen-card-title");
                if (!titulo) {
                    return null;
                }

                feedback = document.createElement("div");
                feedback.className = "alert d-none mb-3 pleno-summary-feedback";
                feedback.setAttribute("role", "alert");
                titulo.insertAdjacentElement("afterend", feedback);

                return feedback;
            }

            function mostrarFeedbackResumen(tipo, mensaje) {
                var feedback = obtenerFeedbackResumen();
                if (!feedback) {
                    return;
                }

                feedback.className = "alert mb-3 pleno-summary-feedback alert-" + tipo;
                feedback.textContent = mensaje;
            }

            function limpiarFeedbackResumen() {
                var feedback = obtenerFeedbackResumen();
                if (!feedback) {
                    return;
                }

                feedback.className = "alert d-none mb-3 pleno-summary-feedback";
                feedback.textContent = "";
            }

            function ponerBotonGenerando(boton) {
                boton.setAttribute("data-original-html", boton.innerHTML);
                boton.disabled = true;
                boton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Generando...';
            }

            function restaurarBotonGenerar(boton) {
                var original = boton.getAttribute("data-original-html");
                boton.disabled = false;
                if (original) {
                    boton.innerHTML = original;
                    boton.removeAttribute("data-original-html");
                }
            }

            function enlazarBotonesResumen() {
                document.querySelectorAll(".pleno-summary-generate-btn").forEach(function (boton) {
                    boton.addEventListener("click", generarResumenEjecutivo);
                });
            }

            function renderResumen(resumen) {
                if (!resumenCard || !resumen) {
                    return;
                }

                var body = resumenCard.querySelector(".pleno-resumen-card-body");
                if (!body) {
                    return;
                }

                var idResumen = parseInt(resumen.id_resumen || "0", 10);
                var verHref = "resumenes/ver.php?id_resumen=" + encodeURIComponent(idResumen);
                var descargarHref = "resumenes/descargar.php?id_resumen=" + encodeURIComponent(idResumen);
                var botonGenerar = puedeGenerarResumen
                    ? '<button type="button" class="btn btn-success pleno-summary-generate-btn" data-summary-mode="version"><i class="fas fa-plus me-1"></i>Generar nueva versi&oacute;n</button>'
                    : '';

                body.innerHTML = ''
                    + '<h2 class="pleno-resumen-card-title">'
                    + '<i class="fas fa-file-alt"></i>'
                    + 'Resumen Ejecutivo de la Sesi&oacute;n'
                    + '</h2>'
                    + '<div class="alert d-none mb-3 pleno-summary-feedback" role="alert"></div>'
                    + '<div class="pleno-certificate-box">'
                    + '<span class="pleno-pdf-icon"><i class="fas fa-file-pdf"></i></span>'
                    + '<div>'
                    + '<div class="pleno-certificate-name">Versi&oacute;n ' + htmlEscape(resumen.version || "1") + '</div>'
                    + '<div class="pleno-certificate-date">Fecha generaci&oacute;n: ' + htmlEscape(formatearFechaHora(resumen.fecha_generacion)) + '</div>'
                    + '<div class="pleno-agreement-meta">'
                    + '<span>Usuario generador: ' + htmlEscape(resumen.usuario_generador || "Sin registro") + '</span>'
                    + '<span>Acuerdos: ' + htmlEscape(resumen.total_acuerdos || "0") + '</span>'
                    + '<span>Votaciones: ' + htmlEscape(resumen.total_votaciones || "0") + '</span>'
                    + '</div>'
                    + '</div>'
                    + '</div>'
                    + '<div class="pleno-certificate-actions">'
                    + '<a class="btn btn-outline-success" href="' + htmlEscape(verHref) + '" target="_blank" rel="noopener"><i class="fas fa-eye me-1"></i>Ver</a>'
                    + '<a class="btn btn-outline-success" href="' + htmlEscape(descargarHref) + '"><i class="fas fa-download me-1"></i>Descargar</a>'
                    + botonGenerar
                    + '</div>';

                enlazarBotonesResumen();
            }

            function generarResumenEjecutivo() {
                if (!resumenCard) {
                    return;
                }

                if (!puedeGenerarResumen) {
                    mostrarFeedbackResumen("danger", "No tiene permisos para generar resumenes ejecutivos.");
                    return;
                }

                var boton = this;
                var modo = boton.getAttribute("data-summary-mode") || "version";
                var mensajeConfirmacion = modo === "first"
                    ? "Desea generar el resumen ejecutivo de esta sesion?"
                    : "Desea generar una nueva version del resumen ejecutivo de esta sesion?";

                if (!window.confirm(mensajeConfirmacion)) {
                    return;
                }

                var idSesion = parseInt(resumenCard.getAttribute("data-id-sesion") || "0", 10);
                limpiarFeedbackResumen();
                ponerBotonGenerando(boton);

                fetch("ajax/generar_resumen_ejecutivo.php", {
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
                            var errorBackend = new Error(payload && payload.mensaje ? payload.mensaje : "No fue posible generar el resumen ejecutivo.");
                            errorBackend.esRespuestaBackend = true;
                            throw errorBackend;
                        }

                        return payload;
                    });
                }).then(function (payload) {
                    renderResumen(payload.resumen);
                    mostrarFeedbackResumen("success", "Resumen ejecutivo generado correctamente.");
                }).catch(function (error) {
                    restaurarBotonGenerar(boton);
                    mostrarFeedbackResumen(
                        "danger",
                        error && error.esRespuestaBackend ? error.message : "No se pudo generar el resumen ejecutivo. Intente nuevamente."
                    );
                });
            }

            enlazarBotonesResumen();
        }());
    </script>
    <?php if ($puedeGestionar): ?>
        <script>
            (function () {
                "use strict";

                function mostrarError(elemento, mensaje) {
                    if (!elemento) {
                        return;
                    }
                    elemento.textContent = mensaje || "No fue posible completar la acción.";
                    elemento.classList.remove("d-none");
                }

                function limpiarError(elemento) {
                    if (!elemento) {
                        return;
                    }
                    elemento.textContent = "";
                    elemento.classList.add("d-none");
                }

                function enviarJson(url, data) {
                    return fetch(url, {
                        method: "POST",
                        headers: {
                            "Accept": "application/json",
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(data)
                    }).then(function (response) {
                        return response.json().then(function (payload) {
                            if (!response.ok || !payload || payload.success !== true) {
                                throw new Error(payload && payload.mensaje ? payload.mensaje : "No fue posible completar la acción.");
                            }
                            return payload;
                        });
                    });
                }

                function recargarResumen() {
                    window.location.reload();
                }

                function formatearFechaHoraResumen(valor) {
                    var fecha = String(valor || "").trim();
                    var partes = fecha.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
                    if (!partes) {
                        return fecha || "Sin registro";
                    }

                    return partes[3] + "/" + partes[2] + "/" + partes[1] + " " + partes[4] + ":" + partes[5] + " hrs.";
                }

                function mostrarFeedbackObservaciones(tipo, mensaje) {
                    var feedback = document.getElementById("plenoObservacionesFinalesFeedback");
                    if (!feedback) {
                        return;
                    }

                    feedback.className = "alert mb-0 flex-grow-1 alert-" + tipo;
                    feedback.textContent = mensaje;
                }

                var crearForm = document.getElementById("plenoAcuerdoForm");
                var crearError = document.getElementById("plenoAcuerdoError");
                var editarForm = document.getElementById("plenoEditarAcuerdoForm");
                var editarError = document.getElementById("plenoEditarAcuerdoError");
                var editarId = document.getElementById("plenoEditarAcuerdoId");
                var editarTexto = document.getElementById("plenoEditarAcuerdoTexto");
                var observacionesForm = document.getElementById("plenoObservacionesFinalesForm");
                var observacionesGuardar = document.getElementById("plenoObservacionesFinalesGuardar");
                var observacionesFecha = document.getElementById("plenoObservacionesFinalesFecha");

                if (observacionesForm) {
                    observacionesForm.addEventListener("submit", function (event) {
                        event.preventDefault();

                        if (observacionesGuardar) {
                            observacionesGuardar.disabled = true;
                        }

                        enviarJson("ajax/guardar_observaciones_finales.php", {
                            id_sesion: parseInt(observacionesForm.elements.id_sesion.value || "0", 10),
                            observaciones_finales: observacionesForm.elements.observaciones_finales.value
                        }).then(function (payload) {
                            mostrarFeedbackObservaciones("success", payload.mensaje || "Observaciones finales guardadas correctamente.");
                            if (observacionesFecha) {
                                observacionesFecha.textContent = formatearFechaHoraResumen(payload.observaciones_finales_fecha);
                            }
                        }).catch(function (error) {
                            mostrarFeedbackObservaciones("danger", error.message || "No fue posible guardar las observaciones finales.");
                        }).finally(function () {
                            if (observacionesGuardar) {
                                observacionesGuardar.disabled = false;
                            }
                        });
                    });
                }

                if (crearForm) {
                    crearForm.addEventListener("submit", function (event) {
                        event.preventDefault();
                        limpiarError(crearError);

                        enviarJson("ajax/guardar_acuerdo.php", {
                            id_sesion: parseInt(crearForm.elements.id_sesion.value || "0", 10),
                            punto_numero: crearForm.elements.punto_numero.value,
                            texto_acuerdo: crearForm.elements.texto_acuerdo.value
                        }).then(recargarResumen).catch(function (error) {
                            mostrarError(crearError, error.message);
                        });
                    });
                }

                document.querySelectorAll(".pleno-edit-agreement-btn").forEach(function (boton) {
                    boton.addEventListener("click", function () {
                        limpiarError(editarError);
                        if (editarId) {
                            editarId.value = boton.getAttribute("data-id-acuerdo") || "";
                        }
                        if (editarTexto) {
                            editarTexto.value = boton.getAttribute("data-texto") || "";
                        }
                    });
                });

                if (editarForm) {
                    editarForm.addEventListener("submit", function (event) {
                        event.preventDefault();
                        limpiarError(editarError);

                        enviarJson("ajax/actualizar_acuerdo.php", {
                            id_acuerdo: parseInt(editarForm.elements.id_acuerdo.value || "0", 10),
                            texto_acuerdo: editarForm.elements.texto_acuerdo.value
                        }).then(recargarResumen).catch(function (error) {
                            mostrarError(editarError, error.message);
                        });
                    });
                }

                document.querySelectorAll(".pleno-delete-agreement-btn").forEach(function (boton) {
                    boton.addEventListener("click", function () {
                        var idAcuerdo = parseInt(boton.getAttribute("data-id-acuerdo") || "0", 10);
                        if (!idAcuerdo || !window.confirm("¿Desea eliminar este acuerdo?")) {
                            return;
                        }

                        boton.disabled = true;
                        enviarJson("ajax/eliminar_acuerdo.php", {
                            id_acuerdo: idAcuerdo
                        }).then(recargarResumen).catch(function (error) {
                            boton.disabled = false;
                            window.alert(error.message || "No fue posible eliminar el acuerdo.");
                        });
                    });
                });
            }());
        </script>
    <?php endif; ?>
    <?php endif; ?>
</div>
