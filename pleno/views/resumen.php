<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$resultados = [
    ['punto' => '1. Aprobación de Acta: GGG', 'resultado' => 'Aprobado', 'favor' => 25, 'contra' => 1, 'abstenciones' => 1],
    ['punto' => '2. Cuenta Presidente del Consejo Regional', 'resultado' => 'Aprobado', 'favor' => 26, 'contra' => 0, 'abstenciones' => 2],
    ['punto' => '3.1 Comisión de Régimen Interior', 'resultado' => 'Aprobado', 'favor' => 24, 'contra' => 3, 'abstenciones' => 1],
    ['punto' => '3.2 Comisión de Educación', 'resultado' => 'Rechazado', 'favor' => 12, 'contra' => 15, 'abstenciones' => 1],
    ['punto' => '4. Varios: Modificación Reglamento Interno', 'resultado' => 'Aprobado', 'favor' => 23, 'contra' => 2, 'abstenciones' => 3],
];

$acuerdos = [
    ['numero' => 'Acuerdo N°1', 'texto' => 'Se aprueba el Presupuesto Regional 2026.'],
    ['numero' => 'Acuerdo N°2', 'texto' => 'Se aprueba la modificación del Reglamento Interno.'],
    ['numero' => 'Acuerdo N°3', 'texto' => 'Se aprueba la ejecución del proyecto de infraestructura vial.'],
];
?>
<style>
    .pleno-resumen-page {
        color: #1f3240;
    }

    .pleno-resumen-heading {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .pleno-resumen-heading-icon {
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

    .pleno-resumen-title {
        margin: 0;
        color: #172f3f;
        font-size: 2rem;
        font-weight: 850;
    }

    .pleno-resumen-card {
        border: 1px solid #e2ebf0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 26px rgba(15, 38, 56, 0.08);
    }

    .pleno-resumen-card + .pleno-resumen-card,
    .pleno-resumen-grid,
    .pleno-resumen-lower-grid {
        margin-top: 1.25rem;
    }

    .pleno-resumen-card-body {
        padding: 1.35rem;
    }

    .pleno-resumen-card-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin: 0 0 1.15rem;
        color: #203949;
        font-size: 1.18rem;
        font-weight: 850;
    }

    .pleno-resumen-card-title i {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        color: #0f8f4c;
        background: #e8f7ee;
    }

    .pleno-resumen-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.12fr) minmax(320px, 0.88fr);
        gap: 1.25rem;
        align-items: stretch;
    }

    .pleno-session-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .pleno-session-summary-item {
        min-height: 78px;
        padding: 0.9rem;
        border: 1px solid #e8f0f4;
        border-radius: 13px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
    }

    .pleno-session-label {
        margin-bottom: 0.28rem;
        color: #657987;
        font-size: 0.76rem;
        font-weight: 850;
        text-transform: uppercase;
    }

    .pleno-session-value {
        color: #203949;
        font-size: 1rem;
        font-weight: 850;
    }

    .pleno-badge-finalizado,
    .pleno-badge-generado,
    .pleno-result-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 0.25rem 0.68rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 850;
        white-space: nowrap;
    }

    .pleno-badge-finalizado {
        background: #e8f7ee;
        color: #0f8f4c;
    }

    .pleno-badge-generado {
        background: #eaf3ff;
        color: #0d6efd;
        letter-spacing: 0.04em;
    }

    .pleno-attendance-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.9rem;
    }

    .pleno-attendance-tile {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        min-height: 108px;
        padding: 1rem;
        border-radius: 14px;
        border: 1px solid transparent;
    }

    .pleno-attendance-green {
        background: #eef9f2;
        border-color: #d8efdf;
    }

    .pleno-attendance-blue {
        background: #eef6ff;
        border-color: #d7e9fb;
    }

    .pleno-attendance-orange {
        background: #fff6e8;
        border-color: #f6dfb9;
    }

    .pleno-attendance-purple {
        background: #f2f0ff;
        border-color: #ded9fb;
    }

    .pleno-attendance-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        border-radius: 50%;
        color: #ffffff;
        font-size: 1rem;
    }

    .pleno-attendance-green .pleno-attendance-icon {
        background: #0f8f4c;
    }

    .pleno-attendance-blue .pleno-attendance-icon {
        background: #0d6efd;
    }

    .pleno-attendance-orange .pleno-attendance-icon {
        background: #f28c18;
    }

    .pleno-attendance-purple .pleno-attendance-icon {
        background: #6055d8;
    }

    .pleno-attendance-number {
        color: #182f3f;
        font-size: 1.9rem;
        font-weight: 900;
        line-height: 1;
    }

    .pleno-attendance-text {
        margin-top: 0.22rem;
        color: #647887;
        font-size: 0.84rem;
        font-weight: 780;
    }

    .pleno-results-table-wrap {
        overflow-x: auto;
    }

    .pleno-results-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .pleno-results-table th {
        padding: 0.85rem 0.95rem;
        background: #f1f5f8;
        color: #5d7180;
        font-size: 0.76rem;
        font-weight: 850;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .pleno-results-table th:first-child {
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }

    .pleno-results-table th:last-child {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    .pleno-results-table td {
        padding: 0.9rem 0.95rem;
        border-bottom: 1px solid #edf2f5;
        color: #273f4f;
        font-weight: 650;
        vertical-align: middle;
    }

    .pleno-results-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .pleno-result-approved {
        background: #e8f7ee;
        color: #0f8f4c;
    }

    .pleno-result-rejected {
        background: #fdecef;
        color: #d72638;
    }

    .pleno-vote-favor,
    .pleno-vote-contra,
    .pleno-vote-abstencion {
        font-weight: 900;
    }

    .pleno-vote-favor {
        color: #0f8f4c;
    }

    .pleno-vote-contra {
        color: #d72638;
    }

    .pleno-vote-abstencion {
        color: #f28c18;
    }

    .pleno-resumen-lower-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(340px, 0.86fr);
        gap: 1.25rem;
        align-items: stretch;
    }

    .pleno-agreements-list {
        display: grid;
        gap: 0.85rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .pleno-agreement-card {
        display: flex;
        gap: 0.85rem;
        padding: 0.95rem;
        border: 1px solid #e5eff3;
        border-radius: 13px;
        background: #fbfdff;
    }

    .pleno-agreement-check {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 36px;
        border-radius: 50%;
        color: #ffffff;
        background: #0f8f4c;
        box-shadow: 0 8px 16px rgba(15, 143, 76, 0.18);
    }

    .pleno-agreement-number {
        margin-bottom: 0.15rem;
        color: #203949;
        font-weight: 850;
    }

    .pleno-agreement-text {
        color: #5f7280;
        font-weight: 650;
        line-height: 1.4;
    }

    .pleno-view-all-btn,
    .pleno-certificate-actions .btn {
        min-height: 40px;
        border-radius: 10px;
        font-weight: 750;
    }

    .pleno-view-all-btn {
        margin-top: 1rem;
        border-color: #0f8f4c;
        color: #0f8f4c;
    }

    .pleno-view-all-btn:hover,
    .pleno-view-all-btn:focus {
        border-color: #0b743d;
        color: #0b743d;
        background: #eef9f2;
    }

    .pleno-certificate-box {
        display: flex;
        gap: 0.9rem;
        padding: 1rem;
        border: 1px solid #e5eff3;
        border-radius: 14px;
        background: #fbfdff;
    }

    .pleno-pdf-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        border-radius: 13px;
        color: #d72638;
        background: #fdecef;
        font-size: 1.35rem;
    }

    .pleno-certificate-name {
        color: #203949;
        font-weight: 850;
        word-break: break-word;
    }

    .pleno-certificate-date {
        margin: 0.2rem 0 0.45rem;
        color: #657987;
        font-size: 0.88rem;
        font-weight: 650;
    }

    .pleno-certificate-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .pleno-certificate-actions .btn-success {
        grid-column: 1 / -1;
        background: #0f8f4c;
        border-color: #0f8f4c;
    }

    .pleno-certificate-actions .btn-outline-success {
        color: #0f8f4c;
        border-color: #0f8f4c;
    }

    .pleno-observation-text {
        margin: 0 0 1rem;
        color: #273f4f;
        font-size: 1.02rem;
        font-weight: 650;
        line-height: 1.55;
    }

    .pleno-observation-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.5rem;
        padding-top: 0.95rem;
        border-top: 1px solid #edf2f5;
        color: #657987;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .pleno-observation-meta strong {
        color: #203949;
    }

    @media (max-width: 1199.98px) {
        .pleno-session-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .pleno-resumen-grid,
        .pleno-resumen-lower-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .pleno-resumen-card-body {
            padding: 1rem;
        }

        .pleno-session-summary-grid,
        .pleno-attendance-grid,
        .pleno-certificate-actions {
            grid-template-columns: 1fr;
        }

        .pleno-results-table {
            min-width: 780px;
        }
    }
</style>

<div class="container-fluid mt-4 pleno-resumen-page">
    <div class="pleno-resumen-heading">
        <span class="pleno-resumen-heading-icon"><i class="fas fa-chart-pie"></i></span>
        <h1 class="pleno-resumen-title">Resumen</h1>
    </div>

    <div class="pleno-resumen-grid">
        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-calendar-check"></i>
                    Resumen de la Sesión
                </h2>
                <div class="pleno-session-summary-grid">
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Número de sesión</div>
                        <div class="pleno-session-value">PL-2026-048</div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Tipo de pleno</div>
                        <div class="pleno-session-value">Ordinario</div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Fecha</div>
                        <div class="pleno-session-value">11/06/2026</div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Hora inicio</div>
                        <div class="pleno-session-value">22:00 hrs.</div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Hora término</div>
                        <div class="pleno-session-value">23:15 hrs.</div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Estado</div>
                        <div class="pleno-session-value"><span class="pleno-badge-finalizado">Finalizado</span></div>
                    </div>
                    <div class="pleno-session-summary-item">
                        <div class="pleno-session-label">Lugar</div>
                        <div class="pleno-session-value">Salón Plenario 3</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-users"></i>
                    Asistencia Final
                </h2>
                <div class="pleno-attendance-grid">
                    <div class="pleno-attendance-tile pleno-attendance-green">
                        <span class="pleno-attendance-icon"><i class="fas fa-users"></i></span>
                        <div>
                            <div class="pleno-attendance-number">27</div>
                            <div class="pleno-attendance-text">Consejeros presentes</div>
                        </div>
                    </div>
                    <div class="pleno-attendance-tile pleno-attendance-blue">
                        <span class="pleno-attendance-icon"><i class="fas fa-user-tie"></i></span>
                        <div>
                            <div class="pleno-attendance-number">1</div>
                            <div class="pleno-attendance-text">Gobernador</div>
                        </div>
                    </div>
                    <div class="pleno-attendance-tile pleno-attendance-orange">
                        <span class="pleno-attendance-icon"><i class="fas fa-user-minus"></i></span>
                        <div>
                            <div class="pleno-attendance-number">2</div>
                            <div class="pleno-attendance-text">Ausentes</div>
                        </div>
                    </div>
                    <div class="pleno-attendance-tile pleno-attendance-purple">
                        <span class="pleno-attendance-icon"><i class="fas fa-clipboard-check"></i></span>
                        <div>
                            <div class="pleno-attendance-number">28</div>
                            <div class="pleno-attendance-text">Total asistentes</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="pleno-resumen-card">
        <div class="pleno-resumen-card-body">
            <h2 class="pleno-resumen-card-title">
                <i class="fas fa-poll"></i>
                Resultados por Punto Tratado
            </h2>
            <div class="pleno-results-table-wrap">
                <table class="pleno-results-table">
                    <thead>
                        <tr>
                            <th>Punto</th>
                            <th>Resultado</th>
                            <th>A favor</th>
                            <th>En contra</th>
                            <th>Abstenciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $resultado): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($resultado['punto'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="pleno-result-badge <?php echo $resultado['resultado'] === 'Aprobado' ? 'pleno-result-approved' : 'pleno-result-rejected'; ?>">
                                        <?php echo htmlspecialchars($resultado['resultado'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="pleno-vote-favor"><?php echo (int)$resultado['favor']; ?></td>
                                <td class="pleno-vote-contra"><?php echo (int)$resultado['contra']; ?></td>
                                <td class="pleno-vote-abstencion"><?php echo (int)$resultado['abstenciones']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div class="pleno-resumen-lower-grid">
        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-check-double"></i>
                    Acuerdos Adoptados
                </h2>
                <ul class="pleno-agreements-list">
                    <?php foreach ($acuerdos as $acuerdo): ?>
                        <li class="pleno-agreement-card">
                            <span class="pleno-agreement-check"><i class="fas fa-check"></i></span>
                            <div>
                                <div class="pleno-agreement-number"><?php echo htmlspecialchars($acuerdo['numero'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="pleno-agreement-text"><?php echo htmlspecialchars($acuerdo['texto'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn pleno-view-all-btn">
                    <i class="fas fa-list-check me-2"></i>Ver todos los acuerdos
                </button>
            </div>
        </section>

        <section class="pleno-resumen-card">
            <div class="pleno-resumen-card-body">
                <h2 class="pleno-resumen-card-title">
                    <i class="fas fa-file-signature"></i>
                    Certificado de Acuerdos
                </h2>
                <div class="pleno-certificate-box">
                    <span class="pleno-pdf-icon"><i class="fas fa-file-pdf"></i></span>
                    <div>
                        <div class="pleno-certificate-name">Certificado_Acuerdos_PL-2026-048.pdf</div>
                        <div class="pleno-certificate-date">Generado el 11/06/2026 - 23:16 hrs.</div>
                        <span class="pleno-badge-generado">GENERADO</span>
                    </div>
                </div>
                <div class="pleno-certificate-actions">
                    <button type="button" class="btn btn-outline-success">
                        <i class="fas fa-eye me-2"></i>Vista previa
                    </button>
                    <button type="button" class="btn btn-outline-success">
                        <i class="fas fa-download me-2"></i>Descargar PDF
                    </button>
                    <button type="button" class="btn btn-success">
                        <i class="fas fa-upload me-2"></i>Publicar Certificado
                    </button>
                </div>
            </div>
        </section>
    </div>

    <section class="pleno-resumen-card">
        <div class="pleno-resumen-card-body">
            <h2 class="pleno-resumen-card-title">
                <i class="fas fa-comment-dots"></i>
                Observaciones Finales
            </h2>
            <p class="pleno-observation-text">La sesión se desarrolló sin incidentes y concluyó a las 23:15 hrs.</p>
            <div class="pleno-observation-meta">
                <span>Registrado por: <strong>Secretaría de Pleno</strong></span>
                <span>Fecha: <strong>11/06/2026 - 23:16 hrs.</strong></span>
            </div>
        </div>
    </section>
</div>
