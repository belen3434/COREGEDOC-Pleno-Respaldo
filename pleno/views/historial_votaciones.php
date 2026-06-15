<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$historial = $data['pleno_historial_votaciones'] ?? [];
$filtros = $data['pleno_historial_filtros'] ?? [
    'numero_sesion' => '',
    'fecha' => '',
    'tipo_pleno' => 'todas',
    'resultado' => 'todas',
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

$claseResultado = static function (string $resultado): string {
    if ($resultado === 'Aprobado') {
        return 'historial-result-approved';
    }

    if ($resultado === 'Rechazado') {
        return 'historial-result-rejected';
    }

    if ($resultado === 'Sin votos') {
        return 'historial-result-empty';
    }

    return 'historial-result-tie';
};

$claseVoto = static function (?string $voto): string {
    $voto = strtoupper(trim((string)$voto));
    if ($voto === 'SI') {
        return 'historial-vote-yes';
    }

    if ($voto === 'NO') {
        return 'historial-vote-no';
    }

    if ($voto === 'ABSTENCION') {
        return 'historial-vote-abstain';
    }

    return 'historial-result-tie';
};

$opcionesResultado = [
    'todas' => 'Todas',
    'aprobadas' => 'Aprobadas',
    'rechazadas' => 'Rechazadas',
    'empatadas' => 'Empatadas',
    'sin_votos' => 'Sin votos',
];

$opcionesTipoPleno = [
    'todas' => 'Todas',
    'ordinario' => 'Ordinario',
    'extraordinario' => 'Extraordinario',
];
?>
<style>
    .historial-votaciones-page {
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
        grid-template-columns: minmax(180px, 1fr) minmax(160px, 210px) minmax(170px, 230px) minmax(170px, 230px) auto;
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

    .historial-result-approved,
    .historial-vote-yes {
        background: #e8f7ee;
        color: #0f8f4c;
    }

    .historial-result-rejected,
    .historial-vote-no {
        background: #fdecef;
        color: #d72638;
    }

    .historial-result-tie {
        background: #eef2f6;
        color: #5d7180;
    }

    .historial-result-empty,
    .historial-vote-abstain {
        background: #fff6e8;
        color: #b86b11;
    }

    .historial-detail {
        padding: 1rem;
        border: 1px solid #e5eff3;
        border-radius: 13px;
        background: #fbfdff;
    }

    .historial-detail-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .historial-detail-item {
        padding: 0.8rem;
        border: 1px solid #e8f0f4;
        border-radius: 12px;
        background: #ffffff;
    }

    .historial-detail-item span {
        display: block;
        color: #657987;
        font-size: 0.75rem;
        font-weight: 850;
        text-transform: uppercase;
    }

    .historial-detail-item strong {
        color: #203949;
    }

    @media (max-width: 991.98px) {
        .historial-filters,
        .historial-detail-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .historial-card-body {
            padding: 1rem;
        }

        .historial-table {
            min-width: 820px;
        }
    }
</style>

<div class="container-fluid mt-4 historial-votaciones-page">
    <div class="historial-heading">
        <span class="historial-heading-icon"><i class="fas fa-history"></i></span>
        <div>
            <h1 class="historial-title">Historial de Votaciones</h1>
            <p class="historial-subtitle">Consultar votaciones realizadas en sesiones plenarias anteriores.</p>
        </div>
    </div>

    <section class="historial-card">
        <div class="historial-card-body">
            <form class="historial-filters" method="get" action="index.php">
                <input type="hidden" name="vista" value="historial_votaciones">
                <div>
                    <label class="historial-label" for="historialNumeroSesion">Número de sesión</label>
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
                    <label class="historial-label" for="historialTipoPleno">Tipo de pleno</label>
                    <select class="historial-select" id="historialTipoPleno" name="tipo_pleno">
                        <?php foreach ($opcionesTipoPleno as $valor => $label): ?>
                            <option value="<?php echo $h($valor); ?>" <?php echo ($filtros['tipo_pleno'] ?? 'todas') === $valor ? 'selected' : ''; ?>>
                                <?php echo $h($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="historial-label" for="historialResultado">Resultado</label>
                    <select class="historial-select" id="historialResultado" name="resultado">
                        <?php foreach ($opcionesResultado as $valor => $label): ?>
                            <option value="<?php echo $h($valor); ?>" <?php echo ($filtros['resultado'] ?? 'todas') === $valor ? 'selected' : ''; ?>>
                                <?php echo $h($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-search me-2"></i>Buscar
                </button>
            </form>
        </div>
    </section>

    <section class="historial-card">
        <div class="historial-card-body">
            <?php if (empty($historial)): ?>
                <p class="mb-0 text-muted">No se encontraron votaciones con los filtros seleccionados.</p>
            <?php else: ?>
                <div class="historial-table-wrap">
                    <table class="historial-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Sesión</th>
                                <th>Punto</th>
                                <th>Resultado</th>
                                <th>Sí</th>
                                <th>No</th>
                                <th>Abs</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $index => $votacion): ?>
                                <?php $detalleId = 'historialDetalle' . (int)$index; ?>
                                <tr>
                                    <td><?php echo $h($formatearFecha($votacion['fecha'] ?? null)); ?></td>
                                    <td>
                                        <strong><?php echo $h($votacion['numero_sesion'] ?? 'Sin número'); ?></strong><br>
                                        <span class="text-muted"><?php echo $h($votacion['tipo_pleno'] ?? 'Sin tipo'); ?></span>
                                    </td>
                                    <td><?php echo $h($votacion['punto_numero'] . '. ' . $votacion['punto_titulo']); ?></td>
                                    <td>
                                        <span class="historial-badge <?php echo $h($claseResultado((string)$votacion['resultado'])); ?>">
                                            <?php echo $h($votacion['resultado']); ?>
                                        </span>
                                    </td>
                                    <td class="text-success fw-bold"><?php echo (int)$votacion['si']; ?></td>
                                    <td class="text-danger fw-bold"><?php echo (int)$votacion['no']; ?></td>
                                    <td class="text-warning fw-bold"><?php echo (int)$votacion['abstencion']; ?></td>
                                    <td>
                                        <button class="btn btn-outline-success btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $h($detalleId); ?>" aria-expanded="false" aria-controls="<?php echo $h($detalleId); ?>">
                                            Ver Detalle
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="8">
                                        <div class="collapse" id="<?php echo $h($detalleId); ?>">
                                            <div class="historial-detail">
                                                <h2 class="h6 fw-bold mb-3">Información General</h2>
                                                <div class="historial-detail-grid">
                                                    <div class="historial-detail-item">
                                                        <span>Sesión</span>
                                                        <strong><?php echo $h($votacion['numero_sesion'] ?? 'Sin número'); ?></strong>
                                                    </div>
                                                    <div class="historial-detail-item">
                                                        <span>Punto</span>
                                                        <strong><?php echo $h($votacion['punto_numero'] . '. ' . $votacion['punto_titulo']); ?></strong>
                                                    </div>
                                                    <div class="historial-detail-item">
                                                        <span>Fecha</span>
                                                        <strong><?php echo $h($formatearFecha($votacion['fecha'] ?? null)); ?></strong>
                                                    </div>
                                                    <div class="historial-detail-item">
                                                        <span>Inicio votación</span>
                                                        <strong><?php echo $h($formatearFechaHora($votacion['fecha_inicio_votacion'] ?? null)); ?></strong>
                                                    </div>
                                                    <div class="historial-detail-item">
                                                        <span>Cierre votación</span>
                                                        <strong><?php echo $h($formatearFechaHora($votacion['fecha_cierre_votacion'] ?? null)); ?></strong>
                                                    </div>
                                                    <div class="historial-detail-item">
                                                        <span>Resultado</span>
                                                        <strong><?php echo $h($votacion['resultado']); ?></strong>
                                                    </div>
                                                </div>

                                                <h2 class="h6 fw-bold mb-3">Detalle de votos</h2>
                                                <?php if (empty($votacion['votos'])): ?>
                                                    <p class="mb-0 text-muted">No hay votos registrados para esta votación.</p>
                                                <?php else: ?>
                                                    <div class="historial-table-wrap">
                                                        <table class="historial-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Consejero</th>
                                                                    <th>Voto emitido</th>
                                                                    <th>Hora del voto</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($votacion['votos'] as $voto): ?>
                                                                    <tr>
                                                                        <td><?php echo $h($voto['consejero'] ?? 'Sin nombre'); ?></td>
                                                                        <td>
                                                                            <span class="historial-badge <?php echo $h($claseVoto($voto['opcionVoto'] ?? '')); ?>">
                                                                                <?php echo $h($voto['opcionVoto'] ?? 'Sin voto'); ?>
                                                                            </span>
                                                                        </td>
                                                                        <td><?php echo $h($formatearFechaHora($voto['hora'] ?? null)); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
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
