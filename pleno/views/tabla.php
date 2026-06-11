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

$aprobacionActas = trim((string)($sesion['aprobacion_actas'] ?? ''));

$seccionesOrdenDia = [
    'cuenta_comisiones' => '3. CUENTA COMISIONES',
    'varios' => '4. VARIOS',
];

$puntosPorSeccion = array_fill_keys(array_keys($seccionesOrdenDia), []);

foreach ($puntosSesion as $punto) {
    $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));
    $seccionOrden = in_array($tipoPunto, ['COMISION', 'IMPREVISTA'], true)
        ? 'cuenta_comisiones'
        : ($tipoPunto === 'TABLA' ? 'varios' : trim((string)($punto['seccion_orden'] ?? '')));

    if (!isset($puntosPorSeccion[$seccionOrden])) {
        $seccionOrden = 'varios';
    }

    $puntosPorSeccion[$seccionOrden][] = $punto;
}

foreach ($puntosPorSeccion as &$puntosSeccion) {
    usort($puntosSeccion, static function (array $a, array $b): int {
        $ordenA = (int)($a['orden'] ?? 0);
        $ordenB = (int)($b['orden'] ?? 0);

        if ($ordenA === $ordenB) {
            return (int)($a['id'] ?? 0) <=> (int)($b['id'] ?? 0);
        }

        return $ordenA <=> $ordenB;
    });
}
unset($puntosSeccion);
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
            No existe una sesión plenaria registrada para hoy.
        </div>
    <?php elseif ($sesion): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md">
                        <div class="text-muted small text-uppercase">Número de sesión</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($sesion['numero_sesion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="col-md">
                        <div class="text-muted small text-uppercase">Tipo de pleno</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($formatearTipo($sesion['tipo_pleno'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="col-md">
                        <div class="text-muted small text-uppercase">Fecha</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($formatearFecha($sesion['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="col-md">
                        <div class="text-muted small text-uppercase">Hora</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars($formatearHora($sesion['hora'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div class="col-md">
                        <div class="text-muted small text-uppercase">Lugar</div>
                        <div class="fw-semibold"><?php echo htmlspecialchars((string)($sesion['lugar'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
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
            <?php endif; ?>
            <div
                class="orden-dia-lista"
                id="ordenDiaLista"
                data-sesion-id="<?php echo (int)($sesion['id_sesion'] ?? 0); ?>"
                data-update-url="<?php echo htmlspecialchars('index.php?action=actualizar_orden_tabla&id_sesion=' . (int)($sesion['id_sesion'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>"
            >
                <?php if ($aprobacionActas !== ''): ?>
                    <div class="orden-dia-seccion-titulo">1. <?php echo htmlspecialchars($aprobacionActas, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <div class="orden-dia-seccion-titulo">2. CUENTA PRESIDENTE DEL CONSEJO REGIONAL DE VALPARAÍSO</div>
                <?php foreach ($seccionesOrdenDia as $seccionKey => $seccionTitulo): ?>
                    <section class="orden-dia-seccion" data-seccion="<?php echo htmlspecialchars($seccionKey, ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="orden-dia-seccion-titulo"><?php echo htmlspecialchars($seccionTitulo, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="orden-dia-items" data-drop-zone="true" aria-label="<?php echo htmlspecialchars($seccionTitulo, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php foreach ($puntosPorSeccion[$seccionKey] as $indiceSeccion => $punto): ?>
                                <?php $puntoRender = $resolverPunto($punto); ?>
                                <div
                                    class="orden-dia-item"
                                    draggable="true"
                                    data-id="<?php echo (int)($punto['id'] ?? 0); ?>"
                                >
                                    <span class="orden-dia-handle">⠿</span>
                                    <span class="orden-dia-numero"><?php echo (int)$indiceSeccion + 1; ?>.</span>
                                    <span class="orden-dia-texto">
                                        <span class="pleno-point-badge <?php echo htmlspecialchars($puntoRender['badge_class'], ENT_QUOTES, 'UTF-8'); ?> me-2">
                                            <?php echo htmlspecialchars($puntoRender['badge'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <?php echo htmlspecialchars($puntoRender['texto'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
            <div id="ordenDiaSyncStatus" class="small text-muted mt-3" aria-live="polite"></div>
        </div>
    <?php endif; ?>
</div>
