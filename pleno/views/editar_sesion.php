<?php
// Vista para editar una sesión plenaria.
require __DIR__ . '/partials/sidebar_pleno.php';

$sesion = $data['pleno_sesion_actual'] ?? null;
$puedeGestionar = (bool)($data['pleno_puede_gestionar'] ?? false);
$flash = $data['pleno_flash'] ?? null;
$crudError = $data['pleno_crud_error'] ?? null;
$comisionesActivas = $data['pleno_comisiones_activas'] ?? [];
$puntosSesion = $data['pleno_puntos_sesion'] ?? [];
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-pen-to-square me-2 text-primary"></i>Editar sesión plenaria
        </h2>
        <a href="index.php?vista=sesiones" class="btn btn-pleno-back">
            <i class="fas fa-arrow-left me-2"></i>Atrás
        </a>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['tipo'], ENT_QUOTES, 'UTF-8'); ?>" role="alert">
            <?php echo htmlspecialchars($flash['mensaje'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($crudError): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($crudError, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if (!$puedeGestionar): ?>
        <div class="alert alert-warning" role="alert">
            No tiene permisos para realizar esta acción.
        </div>
    <?php elseif (!$sesion): ?>
        <div class="alert alert-danger" role="alert">
            La sesión solicitada no existe.
        </div>
    <?php else: ?>
        <div class="card shadow-sm pleno-session-card">
            <div class="card-body">
                <form
                    id="plenoEditarSesionForm"
                    action="index.php?action=actualizar_sesion&id=<?php echo (int)$sesion['id_sesion']; ?>"
                    method="post"
                >
                    <input type="hidden" id="plenoPuntosResumenJson" name="puntos_resumen_json" value="[]">
                    <input type="hidden" id="plenoPuntosEliminadosJson" name="puntos_eliminados_json" value="[]">
                    <input type="hidden" name="numero_sesion" value="<?php echo htmlspecialchars($sesion['numero_sesion'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold pleno-meta-label">Tipo de Pleno</label>
                            <div class="pleno-pill-toggle" role="group" aria-label="Tipo de pleno">
                                <input
                                    type="radio"
                                    class="btn-check"
                                    name="tipo_pleno"
                                    id="editarTipoPlenoNormal"
                                    value="normal"
                                    autocomplete="off"
                                    <?php echo $sesion['tipo_pleno'] === 'normal' ? 'checked' : ''; ?>
                                >
                                <label class="btn pleno-pill-toggle-btn" for="editarTipoPlenoNormal">
                                    <span class="pleno-pill-indicator" aria-hidden="true"></span>
                                    <span>Normal</span>
                                </label>

                                <input
                                    type="radio"
                                    class="btn-check"
                                    name="tipo_pleno"
                                    id="editarTipoPlenoExtraordinario"
                                    value="extraordinario"
                                    autocomplete="off"
                                    <?php echo $sesion['tipo_pleno'] === 'extraordinario' ? 'checked' : ''; ?>
                                >
                                <label class="btn pleno-pill-toggle-btn" for="editarTipoPlenoExtraordinario">
                                    <span class="pleno-pill-indicator" aria-hidden="true"></span>
                                    <span>Extraordinario</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <label class="form-label fw-semibold pleno-meta-label" for="editarNumeroSesion">
                                Número de Sesión
                            </label>
                            <input
                                type="text"
                                id="editarNumeroSesion"
                                class="form-control pleno-session-code"
                                value="<?php echo htmlspecialchars($sesion['numero_sesion'], ENT_QUOTES, 'UTF-8'); ?>"
                                readonly
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="editarFechaSesion">Fecha</label>
                            <input
                                type="date"
                                name="fecha"
                                id="editarFechaSesion"
                                class="form-control"
                                value="<?php echo htmlspecialchars($sesion['fecha'], ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="editarHoraSesion">Hora</label>
                            <input
                                type="time"
                                name="hora"
                                id="editarHoraSesion"
                                class="form-control"
                                value="<?php echo htmlspecialchars(substr((string)$sesion['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="plenoLugarSesionVisual">Lugar</label>
                            <select id="plenoLugarSesionVisual" class="form-select">
                                <option value="salon_plenario_3" selected>Salón Plenario 3</option>
                                <option value="otra">Otra dependencia</option>
                            </select>
                        </div>

                        <div class="col-md-6 d-none" id="plenoLugarOtroVisualWrapper">
                            <label class="form-label fw-semibold" for="plenoLugarOtroVisual">Otra dependencia</label>
                            <input
                                type="text"
                                id="plenoLugarOtroVisual"
                                class="form-control"
                                maxlength="150"
                                placeholder="Escriba la dependencia"
                                aria-describedby="plenoLugarOtroVisualFeedback"
                            >
                            <div id="plenoLugarOtroVisualFeedback" class="invalid-feedback">
                                Debe ingresar el nombre de la dependencia.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="editarObservacionesSesion">Observaciones</label>
                            <textarea
                                name="observaciones"
                                id="editarObservacionesSesion"
                                rows="4"
                                class="form-control"
                            ><?php echo htmlspecialchars((string)$sesion['observaciones'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                    </div>

                    <div class="pleno-session-tools mt-4 pt-3">
                        <div class="pleno-session-tool-buttons">
                            <button
                                type="button"
                                class="btn pleno-tool-btn pleno-tool-btn-outline"
                                data-bs-toggle="modal"
                                data-bs-target="#modalAgregarComision"
                            >
                                <i class="fas fa-plus me-2"></i>Agregar Comisión
                            </button>
                            <button
                                type="button"
                                class="btn pleno-tool-btn pleno-tool-btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#modalComisionImprevista"
                            >
                                <i class="fas fa-exclamation-circle me-2"></i>Comisión Imprevista
                            </button>
                            <button
                                type="button"
                                class="btn pleno-tool-btn pleno-tool-btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalPuntoTabla"
                            >
                                <i class="fas fa-plus me-2"></i>Punto de Tabla
                            </button>
                        </div>
                    </div>

                    <section class="card shadow-sm pleno-session-card pleno-summary-card" aria-labelledby="plenoSummaryTitle">
                        <div class="card-body">
                            <div class="pleno-summary-header">
                                <h3 id="plenoSummaryTitle" class="pleno-summary-title">Resumen de Puntos Agendados</h3>
                                <div class="pleno-summary-meta">
                                    <span id="plenoSummaryCount" class="pleno-summary-count">0 ACTIVOS</span>
                                    <p class="pleno-summary-updated mb-0">Última actualización: hoy, 09:12 AM</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table pleno-summary-table mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">Tipo de Punto</th>
                                            <th scope="col">Descripción Detallada</th>
                                            <th scope="col" class="text-center">Gestión</th>
                                        </tr>
                                    </thead>
                                    <tbody id="plenoSummaryBody">
                                        <tr id="plenoSummaryEmptyRow">
                                            <td colspan="3" class="pleno-summary-empty">Aún no hay puntos agendados.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <div class="pleno-final-actions">
                        <a href="index.php?vista=sesiones" class="btn btn-pleno-cancelar">Cancelar</a>
                        <button type="submit" class="btn btn-pleno-guardar-final">
                            <i class="fas fa-save me-1"></i>Actualizar sesión
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalAgregarComision" tabindex="-1" aria-labelledby="modalAgregarComisionLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content pleno-modal-content border-0 shadow">
                    <div class="modal-header pleno-modal-header pleno-modal-header-success">
                        <h5 class="modal-title fw-bold" id="modalAgregarComisionLabel">
                            <i class="fas fa-sitemap me-2"></i>Agregar Comisión
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="plenoComision" class="form-label text-muted small fw-bold">COMISIÓN</label>
                            <select id="plenoComision" class="form-select">
                                <option value="">Seleccionar comisión</option>
                                <?php foreach ($comisionesActivas as $comision): ?>
                                    <option value="<?php echo (int)($comision['idComision'] ?? 0); ?>">
                                        <?php echo htmlspecialchars((string)($comision['nombreComision'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label for="plenoTemaComision" class="form-label text-muted small fw-bold">TEMA</label>
                            <select id="plenoTemaComision" class="form-select" disabled>
                                <option value="">Seleccione primero una comisión</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" id="btnAgregarComisionResumen" class="btn btn-sm btn-success">
                            <i class="fas fa-plus me-1"></i>Agregar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalComisionImprevista" tabindex="-1" aria-labelledby="modalComisionImprevistaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content pleno-modal-content border-0 shadow">
                    <div class="modal-header pleno-modal-header pleno-modal-header-warning">
                        <h5 class="modal-title fw-bold" id="modalComisionImprevistaLabel">
                            <i class="fas fa-exclamation-triangle me-2"></i>Comisión Imprevista
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="plenoNombreImprevista" class="form-label text-muted small fw-bold">NOMBRE DE LA COMISIÓN</label>
                            <input type="text" id="plenoNombreImprevista" class="form-control" placeholder="Ej: Comisión extraordinaria de urgencia">
                        </div>
                        <div class="mb-0">
                            <label for="plenoObservacionImprevista" class="form-label text-muted small fw-bold">OBSERVACIÓN</label>
                            <textarea id="plenoObservacionImprevista" class="form-control" rows="4" placeholder="Describa brevemente el contexto o motivo."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" id="btnAgregarImprevistaResumen" class="btn btn-sm btn-warning text-dark">
                            <i class="fas fa-plus me-1"></i>Agregar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalPuntoTabla" tabindex="-1" aria-labelledby="modalPuntoTablaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content pleno-modal-content border-0 shadow">
                    <div class="modal-header pleno-modal-header pleno-modal-header-primary">
                        <h5 class="modal-title fw-bold text-white" id="modalPuntoTablaLabel">
                            <i class="fas fa-list-ul me-2"></i>Punto de Tabla
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="plenoTituloPunto" class="form-label text-muted small fw-bold">TÍTULO</label>
                            <input type="text" id="plenoTituloPunto" class="form-control" placeholder="Ej: Exposición de acuerdo regional">
                        </div>
                        <div class="mb-3">
                            <label for="plenoDescripcionPunto" class="form-label text-muted small fw-bold">DESCRIPCIÓN</label>
                            <textarea id="plenoDescripcionPunto" class="form-control" rows="4" placeholder="Resumen o detalle del punto que irá en tabla."></textarea>
                        </div>
                        <div class="mb-0">
                            <label for="plenoOrdenPunto" class="form-label text-muted small fw-bold">ORDEN</label>
                            <input type="number" id="plenoOrdenPunto" class="form-control" min="1" placeholder="1">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" id="btnAgregarTablaResumen" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>Agregar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script id="plenoPuntosInicialesData" type="application/json"><?php echo json_encode($puntosSesion, JSON_UNESCAPED_UNICODE); ?></script>
