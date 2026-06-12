<?php
require __DIR__ . '/partials/sidebar_pleno.php';
?>

<style>
    .secretaria-pleno-view {
        min-height: calc(100vh - 130px);
        padding: 0.25rem 0 1.5rem;
        color: #203949;
        background: #f4f7f9;
    }

    .secretaria-hero,
    .secretaria-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 12px 30px rgba(15, 38, 56, 0.08);
    }

    .secretaria-hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
        padding: 1.45rem 1.55rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    }

    .secretaria-live-label {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0.45rem;
        color: #c62828;
        font-size: 0.78rem;
        font-weight: 850;
        letter-spacing: 0.08em;
    }

    .secretaria-live-dot {
        width: 0.48rem;
        height: 0.48rem;
        border-radius: 999px;
        background: #dc3545;
        box-shadow: 0 0 0 5px rgba(220, 53, 69, 0.12);
    }

    .secretaria-title {
        margin: 0 0 0.85rem;
        color: #17324d;
        font-size: clamp(1.6rem, 2.4vw, 2.35rem);
        font-weight: 850;
        letter-spacing: 0;
    }

    .secretaria-session-name {
        margin-bottom: 0.85rem;
        color: #526979;
        font-size: 1.05rem;
        font-weight: 760;
    }

    .secretaria-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem 1.25rem;
        color: #5f7483;
        font-size: 0.96rem;
        font-weight: 650;
    }

    .secretaria-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .secretaria-meta i,
    .secretaria-card-title i {
        color: #198754;
    }

    .secretaria-status-badge,
    .secretaria-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        white-space: nowrap;
    }

    .secretaria-status-badge {
        min-width: 112px;
        padding: 0.55rem 0.9rem;
        border: 1px solid #b9ddc8;
        color: #12633d;
        background: #eaf7ef;
        font-size: 0.78rem;
        font-weight: 850;
        letter-spacing: 0.06em;
    }

    .secretaria-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(330px, 0.85fr);
        gap: 1.25rem;
        align-items: start;
    }

    .secretaria-stack {
        display: grid;
        gap: 1.25rem;
    }

    .secretaria-card {
        overflow: hidden;
    }

    .secretaria-card-body {
        padding: 1.25rem;
    }

    .secretaria-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .secretaria-card-title {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        margin: 0;
        color: #17324d;
        font-size: 0.9rem;
        font-weight: 850;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .agenda-list {
        display: grid;
        gap: 0.72rem;
    }

    .agenda-item {
        border: 1px solid #e4ebf0;
        border-radius: 14px;
        background: #ffffff;
    }

    .agenda-main {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 54px;
        padding: 0.85rem 1rem;
    }

    .agenda-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        color: #243f52;
        font-weight: 760;
    }

    .agenda-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        flex: 0 0 2rem;
        border-radius: 10px;
        color: #12633d;
        background: #eaf7ef;
        font-weight: 850;
    }

    .agenda-chevron {
        color: #7d93a1;
        font-size: 0.9rem;
    }

    .agenda-sublist {
        display: grid;
        gap: 0.5rem;
        padding: 0 1rem 1rem 3.75rem;
    }

    .agenda-subitem {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        min-height: 46px;
        padding: 0.72rem 0.85rem;
        border: 1px solid #e5edf2;
        border-radius: 12px;
        color: #314b5e;
        background: #fbfdfe;
        font-weight: 680;
    }

    .agenda-subitem-current {
        border-color: #8fd0a9;
        background: #eaf7ef;
        box-shadow: inset 4px 0 0 #198754;
    }

    .agenda-subtext {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-width: 0;
    }

    .agenda-bullet {
        width: 0.55rem;
        height: 0.55rem;
        flex: 0 0 0.55rem;
        border-radius: 50%;
        background: #91a3af;
    }

    .agenda-subitem-current .agenda-bullet {
        background: #198754;
    }

    .secretaria-badge {
        padding: 0.34rem 0.66rem;
        font-size: 0.72rem;
        font-weight: 850;
        letter-spacing: 0.04em;
    }

    .badge-current {
        border: 1px solid #8fd0a9;
        color: #12633d;
        background: #dff3e7;
    }

    .badge-info {
        border: 1px solid #b8d9f3;
        color: #0b5f97;
        background: #e7f3fb;
    }

    .badge-success-soft {
        border: 1px solid #b9ddc8;
        color: #12633d;
        background: #eaf7ef;
    }

    .badge-danger-soft {
        border: 1px solid #f1b8bd;
        color: #a52a35;
        background: #fdecef;
    }

    .badge-warning-soft {
        border: 1px solid #f4d37a;
        color: #996a00;
        background: #fff5d8;
    }

    .point-detail-title {
        margin: 0.7rem 0 0.35rem;
        color: #17324d;
        font-size: 1.55rem;
        font-weight: 850;
    }

    .point-detail-subtitle {
        margin-bottom: 0.75rem;
        color: #198754;
        font-weight: 760;
    }

    .point-detail-text {
        margin-bottom: 1rem;
        color: #526979;
        line-height: 1.55;
    }

    .point-detail-meta {
        display: grid;
        gap: 0.5rem;
        color: #415867;
        font-weight: 650;
    }

    .point-detail-meta strong {
        color: #17324d;
    }

    .secretaria-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
    }

    .secretaria-control-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 48px;
        padding: 0.7rem 1rem;
        border-radius: 12px;
        font-weight: 760;
        box-shadow: 0 8px 16px rgba(15, 38, 56, 0.06);
    }

    .secretaria-control-btn.btn-outline-success {
        border-color: #198754;
        color: #12633d;
        background: #ffffff;
    }

    .secretaria-control-btn.btn-success {
        border-color: #198754;
        background: #198754;
    }

    .secretaria-control-btn.btn-danger {
        border-color: #dc3545;
        background: #dc3545;
    }

    .attendance-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.8rem;
    }

    .attendance-item {
        min-height: 118px;
        padding: 1rem;
        border: 1px solid #e4ebf0;
        border-radius: 14px;
        background: #fbfdfe;
    }

    .metric-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.55rem;
        height: 2.55rem;
        margin-bottom: 0.75rem;
        border-radius: 50%;
        font-size: 1rem;
    }

    .metric-green {
        color: #198754;
        background: #e5f5eb;
    }

    .metric-orange {
        color: #c76a00;
        background: #fff0d9;
    }

    .metric-blue {
        color: #0b72b9;
        background: #e4f2fc;
    }

    .metric-purple {
        color: #6f42c1;
        background: #efe7fb;
    }

    .metric-number {
        color: #17324d;
        font-size: 1.65rem;
        font-weight: 850;
        line-height: 1;
    }

    .metric-label {
        margin-top: 0.25rem;
        color: #657987;
        font-size: 0.88rem;
        font-weight: 650;
    }

    .vote-count {
        color: #657987;
        font-size: 0.82rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .vote-results {
        display: grid;
        gap: 0.95rem;
    }

    .vote-row-head {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto auto;
        gap: 0.7rem;
        align-items: center;
        margin-bottom: 0.45rem;
        color: #334c5e;
        font-weight: 750;
    }

    .vote-name {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        min-width: 0;
    }

    .vote-dot {
        width: 0.65rem;
        height: 0.65rem;
        flex: 0 0 0.65rem;
        border-radius: 50%;
    }

    .vote-green {
        background: #198754;
    }

    .vote-red {
        background: #dc3545;
    }

    .vote-yellow {
        background: #f0ad00;
    }

    .vote-gray {
        background: #9aa8b2;
    }

    .vote-percent {
        color: #657987;
        font-size: 0.86rem;
    }

    .vote-bar {
        height: 0.62rem;
        overflow: hidden;
        border-radius: 999px;
        background: #edf2f5;
    }

    .vote-bar span {
        display: block;
        height: 100%;
        border-radius: inherit;
    }

    .vote-bar-green {
        background: #198754;
    }

    .vote-bar-red {
        background: #dc3545;
    }

    .vote-bar-yellow {
        background: #f0ad00;
    }

    .vote-bar-gray {
        background: #9aa8b2;
    }

    .latest-votes {
        display: grid;
        gap: 0.58rem;
    }

    .latest-vote-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto auto;
        gap: 0.65rem;
        align-items: center;
        min-height: 46px;
        padding: 0.65rem 0.75rem;
        border: 1px solid #e8eef2;
        border-radius: 12px;
        background: #fbfdfe;
    }

    .latest-voter {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 0;
        color: #263f51;
        font-weight: 700;
    }

    .latest-voter i {
        color: #7c909e;
    }

    .latest-time {
        color: #738795;
        font-size: 0.82rem;
        font-weight: 700;
        white-space: nowrap;
    }

    @media (max-width: 1199.98px) {
        .secretaria-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .secretaria-hero,
        .secretaria-card-body {
            padding: 1rem;
        }

        .secretaria-hero {
            display: block;
        }

        .secretaria-status-badge {
            margin-top: 1rem;
        }

        .agenda-sublist {
            padding-left: 1rem;
        }

        .attendance-grid {
            grid-template-columns: 1fr;
        }

        .latest-vote-row {
            grid-template-columns: 1fr;
            align-items: start;
        }
    }
</style>

<div class="secretaria-pleno-view">
    <div class="secretaria-hero">
        <div>
            <div class="secretaria-live-label">
                <span class="secretaria-live-dot"></span>
                <span>SESIÓN EN VIVO</span>
            </div>
            <h1 class="secretaria-title">Sesión del Día</h1>
            <div class="secretaria-session-name">Plenario Ordinario N° PL-2026-048</div>
            <div class="secretaria-meta">
                <span><i class="fas fa-calendar-alt"></i>11/06/2026</span>
                <span><i class="fas fa-clock"></i>22:00 hrs.</span>
                <span><i class="fas fa-map-marker-alt"></i>Salón Plenario 3</span>
            </div>
        </div>
        <span class="secretaria-status-badge">EN CURSO</span>
    </div>

    <div class="secretaria-grid">
        <div class="secretaria-stack">
            <section class="secretaria-card">
                <div class="secretaria-card-body">
                    <div class="secretaria-card-header">
                        <h2 class="secretaria-card-title">
                            <i class="fas fa-list-check"></i>
                            ORDEN DEL DÍA
                        </h2>
                    </div>

                    <div class="agenda-list">
                        <article class="agenda-item">
                            <div class="agenda-main">
                                <div class="agenda-label">
                                    <span class="agenda-number">1</span>
                                    <span>Aprobación de Acta: GGG</span>
                                </div>
                                <i class="fas fa-chevron-down agenda-chevron"></i>
                            </div>
                        </article>

                        <article class="agenda-item">
                            <div class="agenda-main">
                                <div class="agenda-label">
                                    <span class="agenda-number">2</span>
                                    <span>Cuenta Presidente del Consejo Regional</span>
                                </div>
                                <i class="fas fa-chevron-down agenda-chevron"></i>
                            </div>
                        </article>

                        <article class="agenda-item">
                            <div class="agenda-main">
                                <div class="agenda-label">
                                    <span class="agenda-number">3</span>
                                    <span>Cuenta de Sesión del Día</span>
                                </div>
                                <i class="fas fa-chevron-down agenda-chevron"></i>
                            </div>
                            <div class="agenda-sublist">
                                <div class="agenda-subitem agenda-subitem-current">
                                    <span class="agenda-subtext">
                                        <span class="agenda-bullet"></span>
                                        <span>3.1 Comisión Imprevista</span>
                                    </span>
                                    <span class="secretaria-badge badge-current">PUNTO ACTUAL</span>
                                </div>
                                <div class="agenda-subitem">
                                    <span class="agenda-subtext">
                                        <span class="agenda-bullet"></span>
                                        <span>3.2 Comisión Prueba</span>
                                    </span>
                                </div>
                            </div>
                        </article>

                        <article class="agenda-item">
                            <div class="agenda-main">
                                <div class="agenda-label">
                                    <span class="agenda-number">4</span>
                                    <span>Varios</span>
                                </div>
                                <i class="fas fa-chevron-down agenda-chevron"></i>
                            </div>
                            <div class="agenda-sublist">
                                <div class="agenda-subitem">
                                    <span class="agenda-subtext">
                                        <span class="agenda-bullet"></span>
                                        <span>4.1 Punto Tabla A</span>
                                    </span>
                                </div>
                                <div class="agenda-subitem">
                                    <span class="agenda-subtext">
                                        <span class="agenda-bullet"></span>
                                        <span>4.2 Punto Tabla B</span>
                                    </span>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <section class="secretaria-card">
                <div class="secretaria-card-body">
                    <div class="secretaria-card-header">
                        <h2 class="secretaria-card-title">
                            <i class="fas fa-file-lines"></i>
                            DETALLE DEL PUNTO ACTUAL
                        </h2>
                    </div>

                    <span class="secretaria-badge badge-info">PUNTO 3.1</span>
                    <h2 class="point-detail-title">Comisión Imprevista</h2>
                    <div class="point-detail-subtitle">Tema: Agua en la comuna de Olmué</div>
                    <p class="point-detail-text">
                        Se presenta análisis y propuesta respecto a la situación actual del suministro de agua potable en la comuna de Olmué, incluyendo acciones a corto y mediano plazo.
                    </p>
                    <div class="point-detail-meta">
                        <div><strong>Origen:</strong> Comisión Imprevista</div>
                        <div><strong>Documentos asociados:</strong> No hay documentos asociados.</div>
                    </div>
                </div>
            </section>

            <section class="secretaria-card">
                <div class="secretaria-card-body">
                    <div class="secretaria-card-header">
                        <h2 class="secretaria-card-title">
                            <i class="fas fa-sliders"></i>
                            CONTROLES DE LA SECRETARÍA
                        </h2>
                    </div>

                    <div class="secretaria-controls">
                        <button type="button" class="btn btn-outline-success secretaria-control-btn">
                            <i class="fas fa-arrow-left"></i>
                            Punto anterior
                        </button>
                        <button type="button" class="btn btn-outline-success secretaria-control-btn">
                            Siguiente punto
                            <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="button" class="btn btn-success secretaria-control-btn">
                            <i class="fas fa-play"></i>
                            Iniciar votación
                        </button>
                        <button type="button" class="btn btn-danger secretaria-control-btn">
                            <i class="fas fa-stop"></i>
                            Cerrar votación
                        </button>
                        <button type="button" class="btn btn-outline-secondary secretaria-control-btn">
                            <i class="fas fa-flag-checkered"></i>
                            Finalizar pleno
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <aside class="secretaria-stack">
            <section class="secretaria-card">
                <div class="secretaria-card-body">
                    <div class="secretaria-card-header">
                        <h2 class="secretaria-card-title">
                            <i class="fas fa-users"></i>
                            ASISTENCIA
                        </h2>
                    </div>

                    <div class="attendance-grid">
                        <div class="attendance-item">
                            <span class="metric-icon metric-green"><i class="fas fa-user-check"></i></span>
                            <div class="metric-number">27</div>
                            <div class="metric-label">Presentes</div>
                        </div>
                        <div class="attendance-item">
                            <span class="metric-icon metric-orange"><i class="fas fa-user-minus"></i></span>
                            <div class="metric-number">2</div>
                            <div class="metric-label">Ausentes</div>
                        </div>
                        <div class="attendance-item">
                            <span class="metric-icon metric-blue"><i class="fas fa-user-tie"></i></span>
                            <div class="metric-number">1</div>
                            <div class="metric-label">Gobernador</div>
                        </div>
                        <div class="attendance-item">
                            <span class="metric-icon metric-purple"><i class="fas fa-users-gear"></i></span>
                            <div class="metric-number">28</div>
                            <div class="metric-label">Total habilitados</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="secretaria-card">
                <div class="secretaria-card-body">
                    <div class="secretaria-card-header">
                        <h2 class="secretaria-card-title">
                            <i class="fas fa-chart-simple"></i>
                            VOTACIÓN EN CURSO
                        </h2>
                        <span class="vote-count">20 de 28 votos emitidos</span>
                    </div>

                    <div class="vote-results">
                        <div>
                            <div class="vote-row-head">
                                <span class="vote-name"><span class="vote-dot vote-green"></span>A Favor</span>
                                <span>15</span>
                                <span class="vote-percent">53,6%</span>
                            </div>
                            <div class="vote-bar"><span class="vote-bar-green" style="width: 53.6%;"></span></div>
                        </div>

                        <div>
                            <div class="vote-row-head">
                                <span class="vote-name"><span class="vote-dot vote-red"></span>En Contra</span>
                                <span>3</span>
                                <span class="vote-percent">10,7%</span>
                            </div>
                            <div class="vote-bar"><span class="vote-bar-red" style="width: 10.7%;"></span></div>
                        </div>

                        <div>
                            <div class="vote-row-head">
                                <span class="vote-name"><span class="vote-dot vote-yellow"></span>Abstención</span>
                                <span>2</span>
                                <span class="vote-percent">7,1%</span>
                            </div>
                            <div class="vote-bar"><span class="vote-bar-yellow" style="width: 7.1%;"></span></div>
                        </div>

                        <div>
                            <div class="vote-row-head">
                                <span class="vote-name"><span class="vote-dot vote-gray"></span>Pendientes</span>
                                <span>8</span>
                                <span class="vote-percent">28,6%</span>
                            </div>
                            <div class="vote-bar"><span class="vote-bar-gray" style="width: 28.6%;"></span></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="secretaria-card">
                <div class="secretaria-card-body">
                    <div class="secretaria-card-header">
                        <h2 class="secretaria-card-title">
                            <i class="fas fa-clock-rotate-left"></i>
                            ÚLTIMOS VOTOS RECIBIDOS
                        </h2>
                    </div>

                    <div class="latest-votes">
                        <div class="latest-vote-row">
                            <span class="latest-voter"><i class="fas fa-user"></i>Juan Pérez</span>
                            <span class="secretaria-badge badge-success-soft">A favor</span>
                            <span class="latest-time">22:15:30</span>
                        </div>
                        <div class="latest-vote-row">
                            <span class="latest-voter"><i class="fas fa-user"></i>María Soto</span>
                            <span class="secretaria-badge badge-danger-soft">En contra</span>
                            <span class="latest-time">22:15:18</span>
                        </div>
                        <div class="latest-vote-row">
                            <span class="latest-voter"><i class="fas fa-user"></i>Pedro González</span>
                            <span class="secretaria-badge badge-warning-soft">Abstención</span>
                            <span class="latest-time">22:15:05</span>
                        </div>
                        <div class="latest-vote-row">
                            <span class="latest-voter"><i class="fas fa-user"></i>Carla Muñoz</span>
                            <span class="secretaria-badge badge-success-soft">A favor</span>
                            <span class="latest-time">22:14:52</span>
                        </div>
                        <div class="latest-vote-row">
                            <span class="latest-voter"><i class="fas fa-user"></i>Luis Fernández</span>
                            <span class="secretaria-badge badge-success-soft">A favor</span>
                            <span class="latest-time">22:14:41</span>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
