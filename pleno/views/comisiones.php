<?php
require __DIR__ . '/partials/sidebar_pleno.php';
?>
<div class="container-fluid mt-4 pleno-comisiones-view">
    <div class="pleno-comisiones-header mb-4">
        <div>
            <div class="pleno-eyebrow text-success mb-2">COMISIÓN CARGADA</div>
            <h1 class="h2 pleno-page-title mb-3">Vista de Comisión en Sesión</h1>
            <div class="d-flex flex-wrap gap-3 text-muted small">
                <!-- Futuro: fecha comisión desde BD -->
                <span><i class="fas fa-calendar-alt me-1 text-success"></i>Fecha por definir</span>
                <!-- Futuro: hora comisión desde BD -->
                <span><i class="fas fa-clock me-1 text-success"></i>Hora por definir</span>
            </div>
        </div>

        <div class="pleno-comisiones-actions">
            <button type="button" class="btn btn-light border pleno-btn-soft">
                Guardar Acta
            </button>
            <button type="button" class="btn btn-success pleno-btn-success">
                Habilitar Votación en Pleno
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="pleno-kpi-card h-100">
                <div class="pleno-kpi-label">TEMAS EN TABLA</div>
                <!-- Futuro: total temas desde BD -->
                <div class="pleno-kpi-number text-success">08</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="pleno-kpi-card h-100">
                <div class="pleno-kpi-label">QUÓRUM ACTUAL</div>
                <!-- Futuro: quórum real desde BD -->
                <div class="pleno-kpi-number text-primary">88%</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="pleno-kpi-card h-100">
                <div class="pleno-kpi-label">ESTADO GENERAL</div>
                <!-- Futuro: estado general desde BD -->
                <span class="badge pleno-badge-warning">En Proceso</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <section class="pleno-panel mb-4">
                <div class="pleno-panel-body">
                    <h2 class="h5 pleno-section-title mb-3">Orden del Día</h2>

                    <div class="d-flex flex-column gap-3">
                        <!-- Futuro: temas desde BD -->
                        <article class="pleno-topic-card">
                            <div>
                                <h3 class="h5 mb-1">Ampliación Hospital Carlos Van Buren</h3>
                                <p class="text-muted mb-2">Discusión sobre asignación presupuestaria adicional.</p>
                                <span class="badge bg-success">DEBATIENDO</span>
                            </div>
                            <i class="fas fa-arrow-right text-muted"></i>
                        </article>

                        <article class="pleno-topic-card">
                            <div>
                                <h3 class="h5 mb-1">Convenio GORE-MINSAL</h3>
                                <p class="text-muted mb-2">Revisión técnica de convenio para provincias.</p>
                                <span class="badge pleno-badge-warning">PENDIENTE</span>
                            </div>
                            <i class="fas fa-arrow-right text-muted"></i>
                        </article>

                        <article class="pleno-topic-card">
                            <div>
                                <h3 class="h5 mb-1">Actualización Posta Rural</h3>
                                <p class="text-muted mb-2">Mejoras en infraestructura básica.</p>
                                <span class="badge bg-secondary">VOTADO</span>
                            </div>
                            <i class="fas fa-arrow-right text-muted"></i>
                        </article>
                    </div>
                </div>
            </section>

            <section class="pleno-panel">
                <div class="pleno-panel-body">
                    <h2 class="h5 pleno-section-title mb-3">Registro de Acuerdos Preliminares</h2>
                    <!-- Futuro: acuerdos preliminares desde BD -->
                    <textarea class="form-control pleno-agreement-textarea mb-3" rows="6" placeholder="Escriba el acuerdo o punto relevante discutido..."></textarea>
                    <button type="button" class="btn btn-success pleno-btn-success">
                        Guardar Acuerdo
                    </button>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <aside class="pleno-panel mb-4">
                <div class="pleno-panel-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <h2 class="h6 pleno-section-title text-uppercase mb-0">ASISTENCIA ACTUAL</h2>
                        <!-- Futuro: asistencia real desde BD -->
                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">14 / 16 Presentes</span>
                    </div>

                    <div class="pleno-attendance-list">
                        <!-- Futuro: asistentes desde BD -->
                        <div class="pleno-attendance-row">
                            <div class="pleno-avatar">EV</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Elena Valenzuela</div>
                                <div class="small text-muted">Presidenta Comisión</div>
                            </div>
                            <i class="fas fa-check-circle text-success"></i>
                        </div>

                        <div class="pleno-attendance-row">
                            <div class="pleno-avatar">RL</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Roberto Lagos</div>
                                <div class="small text-muted">Secretario Técnico</div>
                            </div>
                            <i class="fas fa-check-circle text-success"></i>
                        </div>

                        <div class="pleno-attendance-row">
                            <div class="pleno-avatar pleno-avatar-muted">CS</div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Carmen Gloria Soto</div>
                                <div class="small text-muted">Consejera Regional</div>
                            </div>
                            <i class="fas fa-times-circle text-danger"></i>
                        </div>
                    </div>

                    <button type="button" class="btn btn-link text-success fw-semibold p-0 mt-3 text-decoration-none">
                        VER LISTA COMPLETA
                    </button>
                </div>
            </aside>

            <aside class="pleno-panel">
                <div class="pleno-panel-body">
                    <h2 class="h6 pleno-section-title text-uppercase mb-3">EJECUCIÓN SALUD MS</h2>

                    <!-- Futuro: ejecución presupuestaria desde BD -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span>Inversión Sectorial</span>
                            <span>85%</span>
                        </div>
                        <div class="progress pleno-progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between small fw-semibold mb-1">
                            <span>Fondos Propios FNDR</span>
                            <span>42%</span>
                        </div>
                        <div class="progress pleno-progress">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 42%;" aria-valuenow="42" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <div class="pleno-budget-card">
                        <div>
                            <div class="small text-muted">Presupuesto 2024</div>
                            <div class="pleno-budget-number">4.850,2 MM</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success rounded-circle pleno-budget-btn" aria-label="Agregar">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
