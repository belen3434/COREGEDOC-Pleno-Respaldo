<?php
// Vista base para crear una sesión plenaria.
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-plus-circle me-2 text-success"></i>Crear sesión plenaria
        </h2>
        <a href="index.php" class="btn btn-pleno-back">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="card shadow-sm pleno-session-card">
        <div class="card-body">
            <form action="#" method="post">
                <div class="row g-3">
                    <div class="col-lg-3">
                        <label class="form-label fw-semibold pleno-meta-label">Tipo de Pleno</label>
                        <div class="pleno-pill-toggle" role="group" aria-label="Tipo de pleno">
                            <input
                                type="radio"
                                class="btn-check"
                                name="tipo_pleno"
                                id="tipoPlenoNormal"
                                value="normal"
                                autocomplete="off"
                                checked
                            >
                            <label class="btn pleno-pill-toggle-btn" for="tipoPlenoNormal">
                                <span class="pleno-pill-indicator" aria-hidden="true"></span>
                                <span>Normal</span>
                            </label>

                            <input
                                type="radio"
                                class="btn-check"
                                name="tipo_pleno"
                                id="tipoPlenoExtraordinario"
                                value="extraordinario"
                                autocomplete="off"
                            >
                            <label class="btn pleno-pill-toggle-btn" for="tipoPlenoExtraordinario">
                                <span class="pleno-pill-indicator" aria-hidden="true"></span>
                                <span>Extraordinario</span>
                            </label>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <label class="form-label fw-semibold pleno-meta-label" for="numeroSesionVisual">
                            Número de Sesión
                            <i class="fas fa-circle-info text-muted ms-1 small"></i>
                        </label>
                        <input
                            type="text"
                            id="numeroSesionVisual"
                            class="form-control pleno-session-code"
                            value="#PL-2024-042"
                            readonly
                        >
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Seleccionar</option>
                            <option value="programada">Programada</option>
                            <option value="en_curso">En curso</option>
                            <option value="cerrada">Cerrada</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fecha</label>
                        <input type="date" name="fecha" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hora</label>
                        <input type="time" name="hora" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Observaciones</label>
                        <textarea name="observaciones" rows="4" class="form-control" placeholder="Detalle adicional"></textarea>
                    </div>
                </div>

                <div class="pleno-session-tools mt-4 pt-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
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

                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <a href="index.php?vista=sesiones" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Guardar sesión
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
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
                        <option>Comisión de Régimen Interno</option>
                        <option>Comisión de Inversiones</option>
                        <option>Comisión de Ordenamiento Territorial</option>
                    </select>
                </div>
                <div class="mb-0">
                    <label for="plenoTemaComision" class="form-label text-muted small fw-bold">TEMA</label>
                    <select id="plenoTemaComision" class="form-select">
                        <option value="">Seleccionar tema</option>
                        <option>Presentación de propuesta</option>
                        <option>Revisión de antecedentes</option>
                        <option>Votación en tabla</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-sm btn-success">
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
                <button type="button" class="btn btn-sm btn-warning text-dark">
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
                <button type="button" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Agregar
                </button>
            </div>
        </div>
    </div>
</div>

