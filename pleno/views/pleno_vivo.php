<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$sesion = $data['pleno_sesion_actual'] ?? null;
$temasComision = $data['pleno_temas_comision_sesion'] ?? [];
$puntosVarios = $data['pleno_puntos_varios_sesion'] ?? [];
$rolUsuario = (int)($data['usuario']['rol'] ?? $_SESSION['tipoUsuario_id'] ?? 0);
$puedeControlarPunto = in_array($rolUsuario, [6, 20], true);

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
    return $hora ? substr($hora, 0, 5) : 'Sin hora';
};

$formatearEstado = static function (?string $estado): string {
    $estado = trim((string)$estado);
    if ($estado === '') {
        return 'EN CURSO';
    }

    $estado = str_replace('_', ' ', $estado);
    return function_exists('mb_strtoupper') ? mb_strtoupper($estado, 'UTF-8') : strtoupper($estado);
};

$resolverPunto = static function (array $punto): array {
    $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));

    if ($tipoPunto === 'TABLA') {
        return [
            'titulo' => trim((string)($punto['titulo_punto'] ?? 'Punto de tabla')),
            'tema' => '',
            'descripcion' => trim((string)($punto['descripcion_punto'] ?? '')),
            'origen' => 'Punto de tabla',
        ];
    }

    if ($tipoPunto === 'IMPREVISTA') {
        return [
            'titulo' => trim((string)($punto['nombre_imprevista'] ?? 'Comisión imprevista')),
            'tema' => 'Comisión imprevista',
            'descripcion' => trim((string)($punto['observacion_imprevista'] ?? '')),
            'origen' => 'Comisión imprevista',
        ];
    }

    return [
        'titulo' => trim((string)($punto['nombreComision'] ?? 'Comisión')),
        'tema' => trim((string)($punto['nombreTema'] ?? '')),
        'descripcion' => trim((string)($punto['objetivo'] ?? $punto['descripcion_punto'] ?? '')),
        'origen' => 'Comisión',
    ];
};

$puntoActual = 1;
if ($sesion) {
    $puntoGuardado = trim((string)($sesion['punto_actual'] ?? '1'));
    $puntoBase = strtok($puntoGuardado !== '' ? $puntoGuardado : '1', '.');
    $puntoActual = in_array($puntoBase, ['1', '2', '3', '4'], true) ? (int)$puntoBase : 1;
}

$puntosNavegacion = [
    1 => 'Acta',
    2 => 'Cuenta Presidente',
    3 => 'Cuenta Comisiones',
    4 => 'Varios',
];

$titulos = [
    1 => ['Aprobación de Acta', 'Punto 1 de la Orden del Día'],
    2 => ['Cuenta Presidente del Consejo Regional', 'Punto 2 de la Orden del Día'],
    3 => ['Cuenta Comisiones', 'Punto 3 de la Orden del Día'],
    4 => ['Varios', 'Punto 4 de la Orden del Día'],
];

if ($sesion && !array_key_exists('cuenta_presidente', $sesion)) {
    try {
        $database = new \App\Config\Database();
        $conn = $database->getConnection();
        $stmtColumna = $conn->query("SHOW COLUMNS FROM sesiones_plenarias LIKE 'cuenta_presidente'");

        if ($stmtColumna && $stmtColumna->fetch()) {
            $stmtCuenta = $conn->prepare('SELECT cuenta_presidente FROM sesiones_plenarias WHERE id_sesion = :id LIMIT 1');
            $stmtCuenta->execute([':id' => (int)($sesion['id_sesion'] ?? 0)]);
            $filaCuenta = $stmtCuenta->fetch();

            if (is_array($filaCuenta)) {
                $sesion['cuenta_presidente'] = $filaCuenta['cuenta_presidente'] ?? '';
            }
        }
    } catch (\Throwable $e) {
        $sesion['cuenta_presidente'] = '';
    }
}

$cuentaPresidente = trim((string)($sesion['cuenta_presidente'] ?? $sesion['observaciones'] ?? ''));
$estadoSesion = strtolower(trim((string)($sesion['estado'] ?? 'programada')));
?>
<style>
    .pleno-live-session-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 1.25rem;
    }

    .pleno-live-session-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15, 38, 56, 0.08);
    }

    .pleno-live-session-body {
        padding: 1.35rem;
    }

    .pleno-live-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        margin: 1rem 0;
    }

    .pleno-live-meta-item {
        padding: 0.85rem;
        border-radius: 12px;
        background: #f6faf8;
        border: 1px solid #dceee4;
    }

    .pleno-live-meta-item span {
        display: block;
        color: #6b7c88;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .pleno-live-meta-item strong {
        color: #17324d;
        font-size: 0.95rem;
    }

    .pleno-live-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .pleno-live-subpoint {
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
    }

    .pleno-live-subpoint + .pleno-live-subpoint {
        margin-top: 0.85rem;
    }

    .pleno-live-subpoint-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.4rem;
        height: 2rem;
        margin-bottom: 0.65rem;
        border-radius: 999px;
        color: #ffffff;
        background: #198754;
        font-weight: 800;
    }

    .pleno-live-progress-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.7rem 0;
        color: #7b8790;
        font-weight: 700;
    }

    .pleno-live-progress-dot {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 999px;
        background: #e9ecef;
        color: #7b8790;
        font-size: 0.85rem;
        font-weight: 900;
    }

    .pleno-live-progress-item.is-current {
        color: #198754;
    }

    .pleno-live-progress-item.is-current .pleno-live-progress-dot,
    .pleno-live-progress-item.is-done .pleno-live-progress-dot {
        background: #198754;
        color: #ffffff;
    }

    .pleno-live-control-buttons {
        display: grid;
        gap: 0.75rem;
    }

    @media (max-width: 992px) {
        .pleno-live-session-grid,
        .pleno-live-meta,
        .pleno-live-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="pleno-live-view">
    <?php if (!$sesion): ?>
        <div class="pleno-live-session-card">
            <div class="pleno-live-session-body">
                <h1 class="pleno-live-title mb-2">Pleno en Vivo</h1>
                <p class="text-muted mb-0">No existe una sesión plenaria en curso.</p>
            </div>
        </div>
    <?php elseif ($estadoSesion === 'programada'): ?>
        <div class="pleno-live-session-card">
            <div class="pleno-live-session-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <div class="pleno-live-progress-dot mb-3">
                            <i class="fas fa-info" aria-hidden="true"></i>
                        </div>
                        <h1 class="pleno-live-title mb-2">Pleno aún no iniciado</h1>
                        <p class="text-muted mb-0">
                            La sesión plenaria se encuentra programada. El contenido del Pleno en Vivo estará disponible cuando la Secretaría inicie el pleno.
                        </p>
                    </div>
                    <span class="badge bg-secondary"><?php echo $h($formatearEstado($sesion['estado'] ?? 'programada')); ?></span>
                </div>
                <div class="pleno-live-meta">
                    <div class="pleno-live-meta-item"><span>Sesión</span><strong><?php echo $h($sesion['numero_sesion'] ?? 'Sin número'); ?></strong></div>
                    <div class="pleno-live-meta-item"><span>Fecha</span><strong><?php echo $h($formatearFecha($sesion['fecha'] ?? null)); ?></strong></div>
                    <div class="pleno-live-meta-item"><span>Hora</span><strong><?php echo $h($formatearHora($sesion['hora'] ?? null)); ?></strong></div>
                    <div class="pleno-live-meta-item"><span>Lugar</span><strong><?php echo $h($sesion['lugar'] ?? 'Sin lugar'); ?></strong></div>
                </div>
                <div class="alert alert-info mb-0">
                    <?php echo $puedeControlarPunto ? 'Debe iniciar el pleno desde la vista Sesión del Día.' : 'Espere a que la Secretaría inicie el pleno.'; ?>
                </div>
            </div>
        </div>
    <?php elseif ($estadoSesion === 'finalizada'): ?>
        <div class="pleno-live-session-card">
            <div class="pleno-live-session-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <div class="pleno-live-progress-dot mb-3">
                            <i class="fas fa-flag-checkered" aria-hidden="true"></i>
                        </div>
                        <h1 class="pleno-live-title mb-2">Pleno finalizado</h1>
                        <p class="text-muted mb-0">
                            La sesión plenaria ya fue finalizada. Puede revisar el resumen de la sesión en la pestaña Resumen.
                        </p>
                    </div>
                    <span class="badge bg-secondary"><?php echo $h($formatearEstado($sesion['estado'] ?? 'finalizada')); ?></span>
                </div>
                <div class="pleno-live-meta">
                    <div class="pleno-live-meta-item"><span>Sesión</span><strong><?php echo $h($sesion['numero_sesion'] ?? 'Sin número'); ?></strong></div>
                    <div class="pleno-live-meta-item"><span>Fecha</span><strong><?php echo $h($formatearFecha($sesion['fecha'] ?? null)); ?></strong></div>
                    <div class="pleno-live-meta-item"><span>Hora</span><strong><?php echo $h($formatearHora($sesion['hora'] ?? null)); ?></strong></div>
                    <div class="pleno-live-meta-item"><span>Lugar</span><strong><?php echo $h($sesion['lugar'] ?? 'Sin lugar'); ?></strong></div>
                </div>
                <a href="index.php?vista=resumen" class="btn btn-success mt-3">Ir a Resumen</a>
            </div>
        </div>
    <?php elseif ($estadoSesion === 'en_curso'): ?>
        <div class="pleno-live-header">
            <div>
                <p class="pleno-live-status mb-2"><span aria-hidden="true">&bull;</span> PLENO EN VIVO</p>
                <h1 class="pleno-live-title mb-2"><?php echo $h($titulos[$puntoActual][0]); ?></h1>
                <p class="pleno-live-subtitle mb-0"><?php echo $h($titulos[$puntoActual][1]); ?></p>
            </div>
            <span class="badge bg-success align-self-start"><?php echo $h($formatearEstado($sesion['estado'] ?? '')); ?></span>
        </div>

        <div class="pleno-live-session-grid">
            <main class="pleno-live-main">
                <section class="pleno-live-session-card">
                    <div class="pleno-live-session-body">
                        <div class="pleno-live-meta">
                            <div class="pleno-live-meta-item"><span>Sesión</span><strong><?php echo $h($sesion['numero_sesion'] ?? 'Sin número'); ?></strong></div>
                            <div class="pleno-live-meta-item"><span>Fecha</span><strong><?php echo $h($formatearFecha($sesion['fecha'] ?? null)); ?></strong></div>
                            <div class="pleno-live-meta-item"><span>Hora</span><strong><?php echo $h($formatearHora($sesion['hora'] ?? null)); ?></strong></div>
                            <div class="pleno-live-meta-item"><span>Lugar</span><strong><?php echo $h($sesion['lugar'] ?? 'Sin lugar'); ?></strong></div>
                        </div>

                        <?php if ($puntoActual === 1): ?>
                            <h2 class="h4 mb-3">Acta a aprobar</h2>
                            <p class="lead mb-3"><?php echo $h(trim((string)($sesion['aprobacion_actas'] ?? '')) ?: 'No hay acta registrada para aprobación.'); ?></p>
                            <div class="alert alert-light border mb-3">Documento del acta: no hay documento asociado.</div>
                            <div class="alert alert-info mb-0">Estado de votación: Esperando inicio de votación</div>
                            <div class="pleno-live-actions">
                                <button class="pleno-live-option pleno-live-option-favor" type="button"><span>A Favor</span></button>
                                <button class="pleno-live-option pleno-live-option-against" type="button"><span>En Contra</span></button>
                                <button class="pleno-live-option pleno-live-option-abstain" type="button"><span>Abstención</span></button>
                            </div>
                        <?php elseif ($puntoActual === 2): ?>
                            <h2 class="h4 mb-3">Cuenta cargada</h2>
                            <p class="mb-3"><?php echo nl2br($h($cuentaPresidente !== '' ? $cuentaPresidente : 'No hay cuenta del presidente registrada para esta sesión.')); ?></p>
                            <div class="alert alert-success mb-0">Este punto es informativo y no requiere votación.</div>
                        <?php elseif ($puntoActual === 3): ?>
                            <?php if (empty($temasComision)): ?>
                                <div class="alert alert-light border mb-0">No hay temas de comisión cargados para esta sesión.</div>
                            <?php else: ?>
                                <?php foreach ($temasComision as $indice => $tema): ?>
                                    <?php $punto = $resolverPunto($tema); ?>
                                    <article class="pleno-live-subpoint">
                                        <span class="pleno-live-subpoint-number">3.<?php echo (int)$indice + 1; ?></span>
                                        <h2 class="h5 mb-1"><?php echo $h($punto['titulo']); ?></h2>
                                        <?php if ($punto['tema'] !== ''): ?><p class="fw-semibold mb-2">Tema: <?php echo $h($punto['tema']); ?></p><?php endif; ?>
                                        <?php if ($punto['descripcion'] !== ''): ?><p class="text-muted mb-2"><?php echo $h($punto['descripcion']); ?></p><?php endif; ?>
                                        <span class="badge bg-secondary mb-3">Pendiente</span>
                                        <div class="pleno-live-actions">
                                            <button class="btn btn-success" type="button">Aprobar</button>
                                            <button class="btn btn-outline-danger" type="button">Rechazar</button>
                                            <button class="btn btn-outline-secondary" type="button">Abstención</button>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (empty($puntosVarios)): ?>
                                <div class="alert alert-light border mb-3">No hay puntos varios cargados para esta sesión.</div>
                            <?php else: ?>
                                <?php foreach ($puntosVarios as $indice => $vario): ?>
                                    <?php $punto = $resolverPunto($vario); ?>
                                    <article class="pleno-live-subpoint">
                                        <span class="pleno-live-subpoint-number">4.<?php echo (int)$indice + 1; ?></span>
                                        <h2 class="h5 mb-1"><?php echo $h($punto['titulo']); ?></h2>
                                        <?php if ($punto['tema'] !== ''): ?><p class="fw-semibold mb-2">Tema: <?php echo $h($punto['tema']); ?></p><?php endif; ?>
                                        <?php if ($punto['descripcion'] !== ''): ?><p class="text-muted mb-0"><?php echo $h($punto['descripcion']); ?></p><?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <div class="alert alert-success mt-3 mb-0">Este punto es informativo y no requiere votación.</div>
                        <?php endif; ?>
                    </div>
                </section>
            </main>

            <aside class="pleno-live-main">
                <section class="pleno-live-session-card">
                    <div class="pleno-live-session-body">
                        <h2 class="h5 mb-3">Control de la Sesión</h2>
                        <div class="pleno-live-control-buttons">
                            <button type="button" class="btn btn-outline-success" id="plenoLivePrevBtn" <?php echo !$puedeControlarPunto || $puntoActual <= 1 ? 'disabled' : ''; ?>>Punto anterior</button>
                            <button type="button" class="btn btn-outline-success" id="plenoLiveNextBtn" <?php echo !$puedeControlarPunto || $puntoActual >= 4 ? 'disabled' : ''; ?>>Siguiente punto</button>
                        </div>
                    </div>
                </section>

                <section class="pleno-live-session-card">
                    <div class="pleno-live-session-body">
                        <h2 class="h5 mb-3">Navegación del Pleno</h2>
                        <p class="mb-3"><strong>Punto actual:</strong> <?php echo (int)$puntoActual; ?> - <?php echo $h($puntosNavegacion[$puntoActual]); ?></p>
                        <?php foreach ($puntosNavegacion as $numero => $nombre): ?>
                            <?php
                            $estadoClase = $numero === $puntoActual ? ' is-current' : ($numero < $puntoActual ? ' is-done' : '');
                            $icono = $numero < $puntoActual ? '<i class="fas fa-check" aria-hidden="true"></i>' : (string)$numero;
                            ?>
                            <div class="pleno-live-progress-item<?php echo $estadoClase; ?>">
                                <span class="pleno-live-progress-dot"><?php echo $icono; ?></span>
                                <span><?php echo (int)$numero; ?> <?php echo $h($nombre); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </aside>
        </div>
    <?php else: ?>
        <div class="pleno-live-session-card">
            <div class="pleno-live-session-body">
                <h1 class="pleno-live-title mb-2">Pleno aún no iniciado</h1>
                <p class="text-muted mb-0">
                    La sesión plenaria no se encuentra en curso. El contenido del Pleno en Vivo estará disponible cuando la Secretaría inicie el pleno.
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($sesion && $puedeControlarPunto && $estadoSesion === 'en_curso'): ?>
    <script>
        (function () {
            "use strict";

            var idSesion = <?php echo (int)($sesion['id_sesion'] ?? 0); ?>;
            var puntoActual = <?php echo (int)$puntoActual; ?>;
            var actualizarPuntoUrl = "ajax/actualizar_punto_actual.php";

            function guardarPunto(punto) {
                if (!idSesion || punto < 1 || punto > 4) {
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
                        punto_actual: String(punto)
                    })
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error("No fue posible guardar el punto actual.");
                    }

                    return response.json();
                }).then(function (payload) {
                    if (payload && payload.ok === true) {
                        window.location.reload();
                    }
                }).catch(function (error) {
                    if (window.console && typeof window.console.error === "function") {
                        window.console.error(error);
                    }
                });
            }

            var prev = document.getElementById("plenoLivePrevBtn");
            var next = document.getElementById("plenoLiveNextBtn");

            if (prev) {
                prev.addEventListener("click", function () {
                    guardarPunto(puntoActual - 1);
                });
            }

            if (next) {
                next.addEventListener("click", function () {
                    guardarPunto(puntoActual + 1);
                });
            }
        })();
    </script>
<?php endif; ?>
