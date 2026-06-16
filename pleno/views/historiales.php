<?php
require __DIR__ . '/partials/sidebar_pleno.php';
?>
<style>
    .historiales-page {
        color: #1f3240;
    }

    .historial-heading {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .historial-heading-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        color: #ffffff;
        background: #0f8f4c;
        box-shadow: 0 10px 20px rgba(15, 143, 76, 0.22);
    }

    .historial-title {
        margin: 0;
        color: #172f3f;
        font-size: 2rem;
        font-weight: 850;
    }

    .historial-subtitle {
        margin: 0.15rem 0 0;
        color: #657987;
        font-weight: 650;
    }

    .historiales-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .historial-card {
        height: 100%;
        border: 1px solid #e2ebf0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 26px rgba(15, 38, 56, 0.08);
    }

    .historial-card-body {
        height: 100%;
        display: flex;
        flex-direction: column;
        padding: 1.35rem;
    }

    .historial-card-icon {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        border-radius: 13px;
        color: #ffffff;
        background: #1f6fb2;
        box-shadow: 0 10px 20px rgba(31, 111, 178, 0.18);
    }

    .historial-card-title {
        margin: 0;
        color: #172f3f;
        font-size: 1.15rem;
        font-weight: 850;
    }

    .historial-card-text {
        margin: 0.5rem 0 1.25rem;
        color: #657987;
        font-weight: 650;
    }

    .historial-card-action {
        margin-top: auto;
    }

    @media (max-width: 767.98px) {
        .historiales-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid mt-4 historiales-page">
    <div class="historial-heading">
        <span class="historial-heading-icon"><i class="fas fa-history"></i></span>
        <div>
            <h1 class="historial-title">Historiales</h1>
            <p class="historial-subtitle">Consultar registros hist&oacute;ricos del m&oacute;dulo Pleno.</p>
        </div>
    </div>

    <div class="historiales-grid">
        <section class="historial-card">
            <div class="historial-card-body">
                <span class="historial-card-icon"><i class="fas fa-vote-yea"></i></span>
                <h2 class="historial-card-title">Historial de Votaciones</h2>
                <p class="historial-card-text">Consultar votaciones realizadas en sesiones plenarias anteriores.</p>
                <div class="historial-card-action">
                    <a class="btn btn-success" href="index.php?vista=historial_votaciones">
                        <i class="fas fa-arrow-right me-2"></i>Abrir historial
                    </a>
                </div>
            </div>
        </section>

        <section class="historial-card">
            <div class="historial-card-body">
                <span class="historial-card-icon"><i class="fas fa-file-pdf"></i></span>
                <h2 class="historial-card-title">Historial de Certificados</h2>
                <p class="historial-card-text">Consultar certificados de acuerdos generados por sesi&oacute;n.</p>
                <div class="historial-card-action">
                    <a class="btn btn-primary" href="index.php?vista=historial_certificados">
                        <i class="fas fa-arrow-right me-2"></i>Abrir historial
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>
