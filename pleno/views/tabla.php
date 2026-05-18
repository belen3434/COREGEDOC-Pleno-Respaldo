<?php
// Vista dinámica de la tabla del día.
require __DIR__ . '/partials/sidebar_pleno.php';

$perfilActivo = $data['pleno_auth']['perfilNombre'] ?? 'No detectado';
$crudError = $data['pleno_crud_error'] ?? null;
$sesion = $data['pleno_sesion_actual'] ?? null;
$puntosSesion = $data['pleno_puntos_sesion'] ?? [];

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

    return substr($hora, 0, 5);
};

$formatearEstado = static function (?string $estado): string {
    $estado = trim((string)$estado);
    return $estado !== '' ? ucfirst(str_replace('_', ' ', $estado)) : '';
};

$formatearTipo = static function (?string $tipo): string {
    $tipo = trim((string)$tipo);
    return $tipo !== '' ? ucfirst($tipo) : '';
};

$resolverPunto = static function (array $punto): array {
    $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));

    if ($tipoPunto === 'TABLA') {
        $titulo = trim((string)($punto['titulo_punto'] ?? ''));
        $descripcion = trim((string)($punto['descripcion_punto'] ?? ''));
        $texto = $titulo !== '' ? $titulo : 'Punto de Tabla';

        if ($descripcion !== '') {
            $texto .= ' - ' . $descripcion;
        }

        return [
            'badge' => 'Tabla',
            'badge_class' => 'pleno-point-badge-tabla',
            'texto' => $texto,
        ];
    }

    if ($tipoPunto === 'IMPREVISTA') {
        $nombre = trim((string)($punto['nombre_imprevista'] ?? ''));
        $observacion = trim((string)($punto['observacion_imprevista'] ?? ''));
        $texto = $nombre !== '' ? $nombre : 'Comisión Imprevista';

        if ($observacion !== '') {
            $texto .= ' - ' . $observacion;
        }

        return [
            'badge' => 'Imprevista',
            'badge_class' => 'pleno-point-badge-imprevista',
            'texto' => $texto,
        ];
    }

    $comision = trim((string)($punto['nombreComision'] ?? ''));
    $tema = trim((string)($punto['nombreTema'] ?? ''));
    $texto = $comision !== '' ? $comision : 'Comisión';

    if ($tema !== '') {
        $texto .= ' - Tema: ' . $tema;
    }

    return [
        'badge' => 'Comisión',
        'badge_class' => 'pleno-point-badge-permanente',
        'texto' => $texto,
    ];
};
?>
<div class="container-fluid mt-4">
    <?php if ($crudError): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($crudError, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-info py-2" role="alert">
        <strong>Perfil activo:</strong> <?php echo htmlspecialchars($perfilActivo, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-table me-2 text-primary"></i>Tabla del Día
        </h2>
        <a href="index.php" class="btn btn-pleno-back">
            <i class="fas fa-arrow-left me-2"></i>Atrás
        </a>
    </div>

    <?php if (!$crudError && !$sesion): ?>
        <div class="alert alert-warning" role="alert">
            No existe una sesión plenaria programada para hoy.
        </div>
    <?php elseif ($sesion): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-muted small text-uppercase">Número de sesión</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($sesion['numero_sesion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small text-uppercase">Tipo de pleno</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($formatearTipo($sesion['tipo_pleno'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small text-uppercase">Fecha</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($formatearFecha($sesion['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small text-uppercase">Hora</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($formatearHora($sesion['hora'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small text-uppercase">Estado</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($formatearEstado($sesion['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <?php if (trim((string)($sesion['observaciones'] ?? '')) !== ''): ?>
                        <div class="col-12">
                            <div class="text-muted small text-uppercase">Observaciones</div>
                            <div class="fw-semibold"><?php echo nl2br(htmlspecialchars((string)$sesion['observaciones'], ENT_QUOTES, 'UTF-8')); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="orden-dia-card">
            <div class="orden-dia-titulo">Orden del día</div>

            <?php if (empty($puntosSesion)): ?>
                <div class="alert alert-light border mb-0" role="alert">
                    La sesión plenaria de hoy aún no tiene puntos cargados.
                </div>
            <?php else: ?>
                <div
                    class="orden-dia-lista"
                    id="ordenDiaLista"
                    data-sesion-id="<?php echo (int)($sesion['id_sesion'] ?? 0); ?>"
                    data-update-url="<?php echo htmlspecialchars('index.php?action=actualizar_orden_tabla&id_sesion=' . (int)($sesion['id_sesion'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
                >
                    <?php foreach ($puntosSesion as $indice => $punto): ?>
                        <?php $puntoRender = $resolverPunto($punto); ?>
                        <div
                            class="orden-dia-item"
                            draggable="true"
                            data-id="<?php echo (int)($punto['id'] ?? 0); ?>"
                        >
                            <span class="orden-dia-handle">⠿</span>
                            <span class="orden-dia-numero"><?php echo (int)$indice + 1; ?>.</span>
                            <span class="orden-dia-texto">
                                <span class="pleno-point-badge <?php echo htmlspecialchars($puntoRender['badge_class'], ENT_QUOTES, 'UTF-8'); ?> me-2">
                                    <?php echo htmlspecialchars($puntoRender['badge'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <?php echo htmlspecialchars($puntoRender['texto'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="ordenDiaSyncStatus" class="small text-muted mt-3" aria-live="polite"></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
