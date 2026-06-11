<?php
require __DIR__ . '/partials/sidebar_pleno.php';
?>
<style>
    .pleno-session-dashboard {
        color: #1f3240;
    }

    .pleno-session-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .pleno-session-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem 1.2rem;
        color: #657987;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .pleno-session-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .pleno-start-btn {
        min-width: 170px;
        min-height: 48px;
        border-radius: 12px;
        font-weight: 700;
    }

    .pleno-session-grid {
        display: grid;
        grid-template-columns: minmax(0, 7fr) minmax(280px, 3fr);
        gap: 1.25rem;
        align-items: start;
    }

    .pleno-agenda-list {
        display: flex;
        flex-direction: column;
        gap: 0.95rem;
    }

    .pleno-agenda-item,
    .pleno-subtema-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        border: 1px solid #e1e9ee;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(15, 38, 56, 0.05);
    }

    .pleno-agenda-item {
        padding: 1rem 1.1rem;
        border-radius: 12px;
    }

    .pleno-agenda-number {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        border-radius: 50%;
        color: #ffffff;
        background: #198754;
        font-size: 1.2rem;
        font-weight: 800;
        box-shadow: 0 8px 18px rgba(25, 135, 84, 0.24);
    }

    .pleno-agenda-content {
        min-width: 0;
        flex: 1;
    }

    .pleno-agenda-title {
        margin: 0 0 0.25rem;
        color: #203949;
        font-size: 1.03rem;
        font-weight: 750;
    }

    .pleno-agenda-description {
        margin: 0;
        color: #657987;
        font-size: 0.92rem;
    }

    .pleno-agenda-duration {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        flex-shrink: 0;
        color: #198754;
        font-size: 0.88rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .pleno-agenda-arrow {
        color: #8aa0ad;
        flex-shrink: 0;
    }

    .pleno-subtemas-list {
        display: grid;
        gap: 0.65rem;
        margin: 0.75rem 0 0.2rem;
        padding-left: 3.9rem;
    }

    .pleno-subtema-card {
        padding: 0.7rem 0.85rem;
        border-radius: 10px;
        color: #203949;
        font-weight: 650;
    }

    .pleno-side-stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .pleno-info-row {
        display: flex;
        justify-content: space-between;
        gap: 0.8rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #edf2f5;
    }

    .pleno-info-row:last-child {
        border-bottom: 0;
    }

    .pleno-info-label {
        color: #657987;
        font-weight: 650;
    }

    .pleno-info-value {
        color: #203949;
        font-weight: 750;
        text-align: right;
    }

    .pleno-summary-box {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.9rem;
        border: 1px solid #e1e9ee;
        border-radius: 12px;
        background: #f8fafc;
    }

    .pleno-summary-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: #198754;
        background: rgba(25, 135, 84, 0.1);
        font-size: 1.1rem;
    }

    .pleno-summary-number {
        color: #203949;
        font-size: 1.55rem;
        font-weight: 850;
        line-height: 1;
    }

    .pleno-doc-button {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 48px;
        border-radius: 12px;
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .pleno-session-hero,
        .pleno-session-grid {
            display: block;
        }

        .pleno-start-btn {
            width: 100%;
            margin-top: 1rem;
        }

        .pleno-side-stack {
            margin-top: 1.25rem;
        }
    }

    @media (max-width: 575.98px) {
        .pleno-agenda-item {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .pleno-subtemas-list {
            padding-left: 0;
        }

        .pleno-agenda-duration {
            margin-left: 3.9rem;
        }
    }
</style>

<div class="container-fluid mt-4 pleno-session-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-sitemap me-2 text-success"></i>Comisiones
        </h2>
        <a href="index.php" class="btn btn-pleno-back">
            <i class="fas fa-arrow-left me-2"></i>Volver al inicio
        </a>
    </div>

    <div class="pleno-session-hero">
        <div>
            <div class="pleno-eyebrow text-success mb-2">SESIÓN PLENARIA</div>
            <h1 class="display-6 pleno-page-title mb-3">Sesión ordinaria N° 931</h1>
            <div class="pleno-session-meta">
                <span><i class="fas fa-calendar-alt text-success"></i>15 de mayo de 2025</span>
                <span><i class="fas fa-clock text-success"></i>10:00 hrs.</span>
                <span><i class="fas fa-map-marker-alt text-success"></i>Salón Plenario CORE Valparaíso</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">ORDINARIA</span>
            </div>
        </div>
        <button type="button" class="btn btn-success pleno-btn-success pleno-start-btn">
            <i class="fas fa-play me-2"></i>Iniciar Pleno
        </button>
    </div>

    <div class="pleno-session-grid">
        <section class="pleno-panel">
            <div class="pleno-panel-body">
                <h2 class="h4 pleno-section-title mb-4">Orden del Día</h2>

                <div class="pleno-agenda-list">
                    <article class="pleno-agenda-item">
                        <span class="pleno-agenda-number">1</span>
                        <div class="pleno-agenda-content">
                            <h3 class="pleno-agenda-title">Cuenta del Gobernador Regional</h3>
                            <p class="pleno-agenda-description">Informe ejecutivo de gestión regional y materias prioritarias.</p>
                        </div>
                        <span class="pleno-agenda-duration"><i class="fas fa-stopwatch"></i>20 min</span>
                        <i class="fas fa-chevron-right pleno-agenda-arrow"></i>
                    </article>

                    <article class="pleno-agenda-item">
                        <span class="pleno-agenda-number">2</span>
                        <div class="pleno-agenda-content">
                            <h3 class="pleno-agenda-title">Aprobación de Acta Anterior</h3>
                            <p class="pleno-agenda-description">Revisión y aprobación formal del acta de la sesión anterior.</p>
                        </div>
                        <span class="pleno-agenda-duration"><i class="fas fa-stopwatch"></i>10 min</span>
                        <i class="fas fa-chevron-right pleno-agenda-arrow"></i>
                    </article>

                    <article class="pleno-agenda-item">
                        <span class="pleno-agenda-number">3</span>
                        <div class="pleno-agenda-content">
                            <h3 class="pleno-agenda-title">Cuenta de Comisiones</h3>
                            <p class="pleno-agenda-description">Presentación de informes y acuerdos elevados por las comisiones.</p>
                        </div>
                        <span class="pleno-agenda-duration"><i class="fas fa-stopwatch"></i>40 min</span>
                        <i class="fas fa-chevron-right pleno-agenda-arrow"></i>
                    </article>

                    <div class="pleno-subtemas-list">
                        <div class="pleno-subtema-card">3.1 Régimen Interior</div>
                        <div class="pleno-subtema-card">3.2 Inversiones, Presupuesto y Patrimonio Regional</div>
                        <div class="pleno-subtema-card">3.3 Control de Gestión y Fiscalización</div>
                        <div class="pleno-subtema-card">3.4 Educación, Arte, Cultura, Deportes y Recreación</div>
                        <div class="pleno-subtema-card">3.5 Relaciones Internacionales</div>
                        <div class="pleno-subtema-card">3.6 Ciencia, Tecnología e Innovación</div>
                    </div>

                    <article class="pleno-agenda-item">
                        <span class="pleno-agenda-number">4</span>
                        <div class="pleno-agenda-content">
                            <h3 class="pleno-agenda-title">Varios</h3>
                            <p class="pleno-agenda-description">Temas extraordinarios o de última incorporación.</p>
                        </div>
                        <span class="pleno-agenda-duration"><i class="fas fa-stopwatch"></i>20 min</span>
                        <i class="fas fa-chevron-right pleno-agenda-arrow"></i>
                    </article>
                </div>
            </div>
        </section>

        <aside class="pleno-side-stack">
            <section class="pleno-panel">
                <div class="pleno-panel-body">
                    <h2 class="h5 pleno-section-title mb-3">Información de la Sesión</h2>
                    <div class="pleno-info-row">
                        <span class="pleno-info-label">Tipo de sesión:</span>
                        <span class="pleno-info-value">Ordinaria</span>
                    </div>
                    <div class="pleno-info-row">
                        <span class="pleno-info-label">Número:</span>
                        <span class="pleno-info-value">931</span>
                    </div>
                    <div class="pleno-info-row">
                        <span class="pleno-info-label">Fecha:</span>
                        <span class="pleno-info-value">15/05/2025</span>
                    </div>
                    <div class="pleno-info-row">
                        <span class="pleno-info-label">Hora:</span>
                        <span class="pleno-info-value">10:00 hrs.</span>
                    </div>
                    <div class="pleno-info-row">
                        <span class="pleno-info-label">Lugar:</span>
                        <span class="pleno-info-value">Salón Plenario CORE Valparaíso</span>
                    </div>
                    <div class="pleno-info-row">
                        <span class="pleno-info-label">Estado:</span>
                        <span class="badge pleno-badge-warning">PROGRAMADA</span>
                    </div>
                </div>
            </section>

            <section class="pleno-panel">
                <div class="pleno-panel-body">
                    <h2 class="h5 pleno-section-title mb-3">Resumen del Pleno</h2>
                    <div class="d-flex flex-column gap-3">
                        <div class="pleno-summary-box">
                            <span class="pleno-summary-icon"><i class="fas fa-users"></i></span>
                            <div>
                                <div class="pleno-summary-number">29</div>
                                <div class="small text-muted fw-semibold">Consejeros y Gobernador</div>
                            </div>
                        </div>
                        <div class="pleno-summary-box">
                            <span class="pleno-summary-icon"><i class="fas fa-list-check"></i></span>
                            <div>
                                <div class="pleno-summary-number">10</div>
                                <div class="small text-muted fw-semibold">Puntos en tabla</div>
                            </div>
                        </div>
                        <div class="pleno-summary-box">
                            <span class="pleno-summary-icon"><i class="fas fa-hourglass-half"></i></span>
                            <div>
                                <div class="pleno-summary-number">90</div>
                                <div class="small text-muted fw-semibold">Duración estimada total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="pleno-panel">
                <div class="pleno-panel-body">
                    <h2 class="h5 pleno-section-title mb-3">Documentos Asociados</h2>
                    <button type="button" class="btn btn-light border pleno-doc-button">
                        <span><i class="fas fa-folder-open me-2 text-success"></i>Ver documentos de la sesión</span>
                        <i class="fas fa-chevron-right text-success"></i>
                    </button>
                </div>
            </section>
        </aside>
    </div>
</div>
