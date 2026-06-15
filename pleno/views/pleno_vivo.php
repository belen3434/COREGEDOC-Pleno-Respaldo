<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$sesion = $data['pleno_sesion_actual'] ?? null;
$temasComision = $data['pleno_temas_comision_sesion'] ?? [];
$puntosVarios = $data['pleno_puntos_varios_sesion'] ?? [];
$estadoVotacionActual = (string)($data['pleno_estado_votacion_actual'] ?? 'pendiente');
$rolUsuario = (int)($data['usuario']['rol'] ?? $_SESSION['tipoUsuario_id'] ?? 0);
$puedeControlarPunto = in_array($rolUsuario, [6, 20], true);
$asistenciaUsuarioPleno = $data['pleno_asistencia_usuario'] ?? null;
$asistenciaMarcadaPleno = is_array($asistenciaUsuarioPleno) && !empty($asistenciaUsuarioPleno['ya_marco']);
$esConsejeroRegional = $rolUsuario === 1;
$bloquearPlenoPorAsistencia = $esConsejeroRegional && !$asistenciaMarcadaPleno;

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
$puntoActualGuardado = '1';
if ($sesion) {
    $puntoGuardado = trim((string)($sesion['punto_actual'] ?? '1'));
    $puntoActualGuardado = $puntoGuardado !== '' ? $puntoGuardado : '1';
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

$observaciones = trim((string)($sesion['observaciones'] ?? ''));
$observacionesTexto = $observaciones !== '' ? $observaciones : 'Sin observaciones registradas para esta sesión.';
$cuentaPresidente = trim((string)($sesion['cuenta_presidente'] ?? ''));
$estadoSesion = strtolower(trim((string)($sesion['estado'] ?? 'programada')));
$estadoVotacionActual = in_array($estadoVotacionActual, ['sin_votacion', 'pendiente', 'votacion_en_curso', 'votacion_cerrada', 'no_vota'], true)
    ? $estadoVotacionActual
    : 'pendiente';
$mensajeEstadoVotacion = 'Estado de votación: Esperando inicio de votación';
$claseEstadoVotacion = 'alert-info';

if ($estadoVotacionActual === 'votacion_en_curso') {
    $mensajeEstadoVotacion = 'Estado de votación: Votación en curso';
    $claseEstadoVotacion = 'alert-success';
}

if ($estadoVotacionActual === 'votacion_cerrada') {
    $mensajeEstadoVotacion = 'Estado de votación: Votación cerrada';
    $claseEstadoVotacion = 'alert-warning';
}

if ($estadoVotacionActual === 'no_vota') {
    $mensajeEstadoVotacion = 'Este punto no requiere votación';
    $claseEstadoVotacion = 'alert-success';
}
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

    .pleno-live-observations {
        margin: 0 0 1rem;
        padding: 1rem;
        border: 1px solid #dceee4;
        border-radius: 12px;
        color: #526979;
        background: #f6faf8;
        line-height: 1.45;
        font-weight: 620;
    }

    .pleno-live-observations strong {
        display: block;
        margin-bottom: 0.35rem;
        color: #17324d;
        font-size: 0.82rem;
        font-weight: 850;
        text-transform: uppercase;
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

    .pleno-live-option:disabled,
    .pleno-live-actions .btn:disabled {
        cursor: not-allowed;
        opacity: 0.55;
        transform: none;
        box-shadow: none;
    }

    .pleno-live-attendance-gate {
        min-height: calc(100vh - 230px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem 0;
    }

    .pleno-live-attendance-gate .pleno-attendance-self-card {
        width: min(100%, 620px);
        margin-bottom: 0 !important;
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
                <div class="pleno-live-observations">
                    <strong>Observaciones</strong>
                    <?php echo nl2br($h($observacionesTexto)); ?>
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
                <div class="pleno-live-observations">
                    <strong>Observaciones</strong>
                    <?php echo nl2br($h($observacionesTexto)); ?>
                </div>
                <a href="index.php?vista=resumen" class="btn btn-success mt-3">Ir a Resumen</a>
            </div>
        </div>
    <?php elseif ($estadoSesion === 'en_curso'): ?>
        <?php if ($bloquearPlenoPorAsistencia): ?>
            <div class="pleno-live-attendance-gate" id="plenoLiveAttendanceGate">
                <?php require __DIR__ . '/partials/asistencia_autoregistro.php'; ?>
            </div>
        <?php elseif ($rolUsuario === 22): ?>
            <?php require __DIR__ . '/partials/asistencia_autoregistro.php'; ?>
        <?php endif; ?>

        <div id="plenoLiveContent" <?php echo $bloquearPlenoPorAsistencia ? 'hidden' : ''; ?>>
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
                        <div class="pleno-live-observations">
                            <strong>Observaciones</strong>
                            <?php echo nl2br($h($observacionesTexto)); ?>
                        </div>

                        <?php if ($puntoActual === 1): ?>
                            <h2 class="h4 mb-3">Acta a aprobar</h2>
                            <p class="lead mb-3"><?php echo $h(trim((string)($sesion['aprobacion_actas'] ?? '')) ?: 'No hay acta registrada para aprobación.'); ?></p>
                            <div class="alert alert-light border mb-3">Documento del acta: no hay documento asociado.</div>
                            <div class="alert <?php echo $h($claseEstadoVotacion); ?> mb-0"><?php echo $h($mensajeEstadoVotacion); ?></div>
                            <?php if ($estadoVotacionActual === 'votacion_en_curso'): ?>
                            <?php if (!$asistenciaMarcadaPleno && in_array($rolUsuario, [1, 22], true)): ?>
                                <div class="alert alert-warning mt-3 mb-0 pleno-vote-attendance-warning">Debe registrar su asistencia antes de votar.</div>
                            <?php endif; ?>
                            <div class="pleno-live-actions">
                                <button class="pleno-live-option pleno-live-option-favor" type="button" <?php echo (!$asistenciaMarcadaPleno && in_array($rolUsuario, [1, 22], true)) ? 'disabled' : ''; ?>><span>A Favor</span></button>
                                <button class="pleno-live-option pleno-live-option-against" type="button" <?php echo (!$asistenciaMarcadaPleno && in_array($rolUsuario, [1, 22], true)) ? 'disabled' : ''; ?>><span>En Contra</span></button>
                                <button class="pleno-live-option pleno-live-option-abstain" type="button" <?php echo (!$asistenciaMarcadaPleno && in_array($rolUsuario, [1, 22], true)) ? 'disabled' : ''; ?>><span>Abstención</span></button>
                            </div>
                            <?php elseif ($estadoVotacionActual === 'votacion_cerrada'): ?>
                                <div class="alert alert-warning mt-3 mb-0">La votación de este punto se encuentra cerrada.</div>
                            <?php else: ?>
                                <div class="alert alert-info mt-3 mb-0">No hay votación activa. La Secretaría aún no ha iniciado una votación para el punto actual.</div>
                            <?php endif; ?>
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
                                        <?php $subpuntoNumero = '3.' . ((int)$indice + 1); ?>
                                        <?php if ($puntoActualGuardado === $subpuntoNumero): ?>
                                            <div class="alert <?php echo $h($claseEstadoVotacion); ?> mb-3"><?php echo $h($mensajeEstadoVotacion); ?></div>
                                        <?php endif; ?>
                                        <?php if ($puntoActualGuardado === $subpuntoNumero && $estadoVotacionActual === 'votacion_en_curso'): ?>
                                        <?php if (!$asistenciaMarcadaPleno && in_array($rolUsuario, [1, 22], true)): ?>
                                            <div class="alert alert-warning mb-3 pleno-vote-attendance-warning">Debe registrar su asistencia antes de votar.</div>
                                        <?php endif; ?>
                                        <div class="pleno-live-actions">
                                            <button class="btn btn-success" type="button" <?php echo (!$asistenciaMarcadaPleno && in_array($rolUsuario, [1, 22], true)) ? 'disabled' : ''; ?>>Aprobar</button>
                                            <button class="btn btn-outline-danger" type="button" <?php echo (!$asistenciaMarcadaPleno && in_array($rolUsuario, [1, 22], true)) ? 'disabled' : ''; ?>>Rechazar</button>
                                            <button class="btn btn-outline-secondary" type="button" <?php echo (!$asistenciaMarcadaPleno && in_array($rolUsuario, [1, 22], true)) ? 'disabled' : ''; ?>>Abstención</button>
                                        </div>
                                        <?php elseif ($puntoActualGuardado === $subpuntoNumero && $estadoVotacionActual === 'votacion_cerrada'): ?>
                                            <div class="alert alert-warning mb-0">La votación de este punto se encuentra cerrada.</div>
                                        <?php elseif ($puntoActualGuardado === $subpuntoNumero): ?>
                                            <div class="alert alert-info mb-0">No hay votación activa. La Secretaría aún no ha iniciado una votación para el punto actual.</div>
                                        <?php endif; ?>
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

<?php if ($estadoSesion === 'en_curso' && in_array($rolUsuario, [1, 22], true)): ?>
    <script>
        function plenoMostrarContenidoEnVivo() {
            var gate = document.getElementById("plenoLiveAttendanceGate");
            var content = document.getElementById("plenoLiveContent");

            if (gate) {
                gate.hidden = true;
            }

            if (content) {
                content.hidden = false;
            }

            document.querySelectorAll(".pleno-live-actions button:disabled").forEach(function (button) {
                button.disabled = false;
            });

            document.querySelectorAll(".pleno-vote-attendance-warning").forEach(function (alert) {
                alert.hidden = true;
            });
        }

        document.addEventListener("pleno:asistencia-registrada", plenoMostrarContenidoEnVivo);
        document.addEventListener("pleno:asistencia-confirmada", plenoMostrarContenidoEnVivo);
    </script>
<?php endif; ?>
