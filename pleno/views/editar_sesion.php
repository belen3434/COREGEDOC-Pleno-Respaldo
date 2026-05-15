<?php
// Vista para editar una sesión plenaria.
require __DIR__ . '/partials/sidebar_pleno.php';

$sesion = $data['pleno_sesion_actual'] ?? null;
$puedeGestionar = (bool)($data['pleno_puede_gestionar'] ?? false);
$flash = $data['pleno_flash'] ?? null;
$crudError = $data['pleno_crud_error'] ?? null;
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
                    <input type="hidden" name="numero_sesion" value="<?php echo htmlspecialchars($sesion['numero_sesion'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="row g-3">
                        <div class="col-lg-3">
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

                        <div class="col-lg-5">
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

                        <div class="col-lg-4">
                            <label class="form-label fw-semibold" for="editarEstadoSesion">Estado</label>
                            <select name="estado" id="editarEstadoSesion" class="form-select" required>
                                <option value="">Seleccionar</option>
                                <option value="programada" <?php echo $sesion['estado'] === 'programada' ? 'selected' : ''; ?>>Programada</option>
                                <option value="en_curso" <?php echo $sesion['estado'] === 'en_curso' ? 'selected' : ''; ?>>En curso</option>
                                <option value="cerrada" <?php echo $sesion['estado'] === 'cerrada' ? 'selected' : ''; ?>>Cerrada</option>
                            </select>
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

                    <div class="pleno-final-actions">
                        <a href="index.php?vista=sesiones" class="btn btn-pleno-cancelar">Cancelar</a>
                        <button type="submit" class="btn btn-pleno-guardar-final">
                            <i class="fas fa-save me-1"></i>Actualizar sesión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
