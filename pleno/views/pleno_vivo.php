<?php
require __DIR__ . '/partials/sidebar_pleno.php';
?>
<div class="pleno-live-view">
    <div class="pleno-live-header">
        <div>
            <p class="pleno-live-status mb-2">
                <span aria-hidden="true">&bull;</span> PLENO EN VIVO
            </p>
            <h1 class="pleno-live-title mb-2">Pleno Ordinario N&deg; 24</h1>
            <p class="pleno-live-subtitle mb-0">
                <i class="fas fa-map-marker-alt me-2" aria-hidden="true"></i>Edificio Institucional CORE, Valpara&iacute;so
            </p>
        </div>

        <aside class="pleno-live-profile" aria-label="Presidente de comision">
            <div class="pleno-live-profile-avatar" aria-hidden="true">
                <i class="fas fa-user"></i>
            </div>
            <div class="pleno-live-profile-body">
                <h2 class="mb-1">Rodrigo Valenzuela</h2>
                <p class="mb-2">Provincia de Valpara&iacute;so</p>
                <span class="pleno-live-profile-badge">PRESIDENTE COMISI&Oacute;N</span>
            </div>
        </aside>
    </div>

    <div class="pleno-live-grid">
        <section class="pleno-live-main">
            <article class="pleno-live-card pleno-live-topic-card">
                <span class="pleno-live-topic-badge">PUNTO DE TABLA 04.A</span>
                <h2>Proyecto de Restauraci&oacute;n del Muelle Prat: Fondos Concursables FNDR 2024</h2>
                <p>
                    Se solicita la aprobaci&oacute;n de un incremento presupuestario de $1.200.000.000 para las obras de refuerzo estructural y restauraci&oacute;n arquitect&oacute;nica del entorno del Muelle Prat, Patrimonio de la Humanidad.
                </p>

                <div class="pleno-live-metrics">
                    <div class="pleno-live-metric-box">
                        <span>MONTO SOLICITADO</span>
                        <strong>M$ 1.200.000</strong>
                    </div>
                    <div class="pleno-live-metric-box">
                        <span>ETAPA ACTUAL</span>
                        <strong>Ejecuci&oacute;n</strong>
                    </div>
                </div>
            </article>

            <article class="pleno-live-card pleno-live-results-card">
                <div class="pleno-live-card-heading">
                    <h2>ESTADO GLOBAL DE VOTACI&Oacute;N</h2>
                    <p>24 de 28 Consejeros presentes</p>
                </div>

                <div class="pleno-live-vote-bar" aria-label="Resultados de votacion de ejemplo">
                    <span class="pleno-live-vote-favor" style="width: 64.2857%"></span>
                    <span class="pleno-live-vote-against" style="width: 14.2857%"></span>
                    <span class="pleno-live-vote-abstain" style="width: 7.1428%"></span>
                    <span class="pleno-live-vote-pending" style="width: 14.2857%"></span>
                </div>

                <div class="pleno-live-legend">
                    <span><i class="pleno-live-dot pleno-live-dot-favor"></i>18 A Favor</span>
                    <span><i class="pleno-live-dot pleno-live-dot-against"></i>4 En Contra</span>
                    <span><i class="pleno-live-dot pleno-live-dot-abstain"></i>2 Abstenci&oacute;n</span>
                </div>
            </article>
        </section>

        <aside class="pleno-live-card pleno-live-vote-card" aria-label="Emitir voto visual">
            <div class="pleno-live-vote-heading">
                <h2>EMITIR SU VOTO</h2>
                <p>Su voto es p&uacute;blico y quedar&aacute; registrado en el acta oficial</p>
            </div>

            <div class="pleno-live-vote-actions">
                <button class="pleno-live-option pleno-live-option-favor" type="button">
                    <span class="pleno-live-option-icon">
                        <i class="fas fa-check" aria-hidden="true"></i>
                    </span>
                    <span>A FAVOR</span>
                </button>

                <button class="pleno-live-option pleno-live-option-against" type="button">
                    <span class="pleno-live-option-icon">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </span>
                    <span>EN CONTRA</span>
                </button>

                <button class="pleno-live-option pleno-live-option-abstain" type="button">
                    <span class="pleno-live-option-icon">
                        <i class="fas fa-minus" aria-hidden="true"></i>
                    </span>
                    <span>ABSTENCI&Oacute;N</span>
                </button>
            </div>

            <div class="pleno-live-info-box">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <p class="mb-0">Su voto ser&aacute; registrado p&uacute;blicamente bajo su identidad oficial. Esta acci&oacute;n quedar&aacute; asociada al acta institucional.</p>
            </div>
        </aside>
    </div>
</div>
