<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$certificados = $data['pleno_historial_certificados'] ?? [];
$filtros = $data['pleno_historial_certificados_filtros'] ?? [
    'numero_sesion' => '',
    'fecha' => '',
    'estado' => 'todos',
    'numero_certificado' => '',
];

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

$formatearFechaHora = static function (?string $fecha): string {
    if (!$fecha) {
        return 'Sin registro';
    }

    $timestamp = strtotime($fecha);
    return $timestamp ? date('d/m/Y H:i', $timestamp) . ' hrs.' : $fecha;
};

$valorFechaInput = static function (?string $fecha): string {
    $fecha = trim((string)$fecha);
    if ($fecha === '') {
        return '';
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) === 1) {
        return $fecha;
    }

    if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $fecha, $matches) === 1) {
        return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
    }

    return '';
};

$claseEstado = static function (?string $estado): string {
    $estado = strtolower(trim((string)$estado));
    if (in_array($estado, ['vigente', 'activo', 'generado'], true)) {
        return 'historial-result-approved';
    }

    if (in_array($estado, ['anulado', 'eliminado', 'inactivo'], true)) {
        return 'historial-result-rejected';
    }

    return 'historial-result-tie';
};

$opcionesEstado = [
    'todos' => 'Todos',
    'vigente' => 'Vigente',
    'reemplazado' => 'Reemplazado',
    'anulado' => 'Anulado',
];
?>
<style>
    .historial-certificados-page {
        color: #1f3240;
    }

    .historial-heading {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .historial-heading-icon {
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

    .historial-title {
        margin: 0;
        color: #172f3f;
        font-size: 2rem;
        font-weight: 850;
    }

    .historial-subtitle {
        margin: 0.15rem 0 0;
        color: #657987;
        font-weight: 650;
    }

    .historial-card {
        border: 1px solid #e2ebf0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 26px rgba(15, 38, 56, 0.08);
    }

    .historial-card + .historial-card {
        margin-top: 1.25rem;
    }

    .historial-card-body {
        padding: 1.35rem;
    }

    .historial-filters {
        display: grid;
        grid-template-columns: minmax(170px, 1fr) minmax(150px, 190px) minmax(150px, 190px) minmax(170px, 1fr) auto;
        gap: 0.85rem;
        align-items: end;
    }

    .historial-label {
        margin-bottom: 0.35rem;
        color: #657987;
        font-size: 0.76rem;
        font-weight: 850;
        text-transform: uppercase;
    }

    .historial-input,
    .historial-select {
        width: 100%;
        min-height: 42px;
        border: 1px solid #d8e4ea;
        border-radius: 10px;
        padding: 0.55rem 0.75rem;
        color: #203949;
        font-weight: 650;
        background: #ffffff;
    }

    .historial-table-wrap {
        overflow-x: auto;
    }

    .historial-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .historial-table th {
        padding: 0.85rem 0.95rem;
        background: #f1f5f8;
        color: #5d7180;
        font-size: 0.76rem;
        font-weight: 850;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .historial-table th:first-child {
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }

    .historial-table th:last-child {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    .historial-table td {
        padding: 0.9rem 0.95rem;
        border-bottom: 1px solid #edf2f5;
        color: #273f4f;
        font-weight: 650;
        vertical-align: middle;
        white-space: nowrap;
    }

    .historial-badge {
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

    .historial-result-approved {
        background: #e8f7ee;
        color: #0f8f4c;
    }

    .historial-result-rejected {
        background: #fdecef;
        color: #d72638;
    }

    .historial-result-tie {
        background: #eef2f6;
        color: #5d7180;
    }

    .historial-actions {
        display: inline-flex;
        gap: 0.45rem;
    }

    @media (max-width: 991.98px) {
        .historial-filters {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .historial-card-body {
            padding: 1rem;
        }

        .historial-table {
            min-width: 1120px;
        }
    }
</style>

<div class="container-fluid mt-4 historial-certificados-page">
    <div class="historial-heading">
        <span class="historial-heading-icon"><i class="fas fa-file-pdf"></i></span>
        <div>
            <h1 class="historial-title">Historial de Certificados</h1>
            <p class="historial-subtitle">Consultar certificados de acuerdos generados por sesi&oacute;n.</p>
        </div>
    </div>

    <section class="historial-card">
        <div class="historial-card-body">
            <form class="historial-filters" method="get" action="index.php">
                <input type="hidden" name="vista" value="historial_certificados">
                <div>
                    <label class="historial-label" for="historialNumeroSesion">N&uacute;mero de sesi&oacute;n</label>
                    <input
                        class="historial-input"
                        id="historialNumeroSesion"
                        name="numero_sesion"
                        type="text"
                        value="<?php echo $h($filtros['numero_sesion'] ?? ''); ?>"
                        placeholder="PL-2026-034"
                    >
                </div>
                <div>
                    <label class="historial-label" for="historialFecha">Fecha</label>
                    <input
                        class="historial-input"
                        id="historialFecha"
                        name="fecha"
                        type="date"
                        value="<?php echo $h($valorFechaInput($filtros['fecha'] ?? '')); ?>"
                    >
                </div>
                <div>
                    <label class="historial-label" for="historialEstado">Estado</label>
                    <select class="historial-select" id="historialEstado" name="estado">
                        <?php foreach ($opcionesEstado as $valor => $label): ?>
                            <option value="<?php echo $h($valor); ?>" <?php echo ($filtros['estado'] ?? 'todos') === $valor ? 'selected' : ''; ?>>
                                <?php echo $h($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="historial-label" for="historialNumeroCertificado">N&uacute;mero certificado</label>
                    <input
                        class="historial-input"
                        id="historialNumeroCertificado"
                        name="numero_certificado"
                        type="text"
                        value="<?php echo $h($filtros['numero_certificado'] ?? ''); ?>"
                        placeholder="CERT-PL-2026-034-V1"
                    >
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-search me-2"></i>Buscar
                </button>
            </form>
        </div>
    </section>

    <section class="historial-card">
        <div class="historial-card-body">
            <?php if (empty($certificados)): ?>
                <p class="mb-0 text-muted">No se encontraron certificados con los filtros seleccionados.</p>
            <?php else: ?>
                <div class="historial-table-wrap">
                    <table class="historial-table">
                        <thead>
                            <tr>
                                <th>N&uacute;mero certificado</th>
                                <th>Sesi&oacute;n</th>
                                <th>Tipo de pleno</th>
                                <th>Fecha de sesi&oacute;n</th>
                                <th>Versi&oacute;n</th>
                                <th>Fecha generaci&oacute;n</th>
                                <th>Total acuerdos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificados as $certificado): ?>
                                <?php
                                $idCertificado = (int)($certificado['id_certificado'] ?? 0);
                                $estado = (string)($certificado['estado'] ?? 'Sin estado');
                                ?>
                                <tr>
                                    <td><strong><?php echo $h($certificado['numero_certificado'] ?? 'Sin n&uacute;mero'); ?></strong></td>
                                    <td><?php echo $h($certificado['numero_sesion'] ?? 'Sin sesi&oacute;n'); ?></td>
                                    <td><?php echo $h($certificado['tipo_pleno'] ?? 'Sin tipo'); ?></td>
                                    <td><?php echo $h($formatearFecha($certificado['fecha_sesion'] ?? null)); ?></td>
                                    <td>V<?php echo (int)($certificado['version'] ?? 1); ?></td>
                                    <td><?php echo $h($formatearFechaHora($certificado['fecha_generacion'] ?? null)); ?></td>
                                    <td><?php echo (int)($certificado['total_acuerdos'] ?? 0); ?></td>
                                    <td>
                                        <span class="historial-badge <?php echo $h($claseEstado($estado)); ?>">
                                            <?php echo $h(ucfirst($estado)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="historial-actions">
                                            <a class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener" href="certificados/ver.php?id_certificado=<?php echo $idCertificado; ?>">
                                                <i class="fas fa-eye me-1"></i>Ver
                                            </a>
                                            <a class="btn btn-outline-success btn-sm" href="certificados/descargar.php?id_certificado=<?php echo $idCertificado; ?>">
                                                <i class="fas fa-download me-1"></i>Descargar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
