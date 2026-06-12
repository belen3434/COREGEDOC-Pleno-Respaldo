<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$consejerosGrupoUno = [
    'Ana Morales Rojas',
    'Carlos Soto Reyes',
    'Evelyn Mansilla Contreras',
    'Manuel Millones Tapia',
    'Paola Zamorano Alvarado',
    'Rodrigo Mena Flores',
];

$consejerosGrupoDos = [
    'Isabel Nunez Tapia',
    'Jorge Valdes Farias',
    'Leonardo Contreras Silva',
    'María Delgado Vergara',
    'Sebastián Balbontín Álvarez',
    'Viviana Ponce Domínguez',
];

$renderTablaConsejeros = static function (string $titulo, array $consejeros): void {
    ?>
    <section class="pleno-vote-table-card">
        <div class="pleno-vote-table-head">
            <h3 class="pleno-vote-table-title"><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h3>
            <span class="pleno-vote-participants">6 PARTICIPANTES</span>
        </div>
        <div class="pleno-vote-table-wrap">
            <table class="pleno-vote-table">
                <thead>
                    <tr>
                        <th>Consejero</th>
                        <th>Voto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consejeros as $consejero): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($consejero, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="pleno-vote-status">Sin votar</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php
};
?>
<style>
    .pleno-vote-page {
        color: #1f3240;
    }

    .pleno-vote-eyebrow {
        margin-bottom: 0.35rem;
        color: #d21f2b;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .pleno-vote-title {
        margin: 0;
        color: #182b38;
        font-size: 2rem;
        font-weight: 800;
    }

    .pleno-vote-main-card,
    .pleno-vote-table-card {
        border: 1px solid #e3ebf0;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 10px 28px rgba(15, 38, 56, 0.08);
    }

    .pleno-vote-main-card {
        margin-top: 1.4rem;
        padding: 1.6rem;
    }

    .pleno-vote-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.4rem;
    }

    .pleno-vote-pill {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        background: #eaf3ff;
        color: #0d6efd;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .pleno-vote-topic {
        margin: 0.85rem 0 0;
        color: #192f3e;
        font-size: 1.28rem;
        font-weight: 800;
        line-height: 1.3;
    }

    .pleno-vote-close-btn {
        flex: 0 0 auto;
        min-height: 40px;
        padding: 0.55rem 1rem;
        border: 1px solid #e1b94e;
        border-radius: 10px;
        background: #fff4cc;
        color: #7a5a05;
        font-weight: 750;
    }

    .pleno-vote-counters {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .pleno-vote-counter {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-height: 124px;
        padding: 1.25rem;
        border-radius: 14px;
        color: #ffffff;
    }

    .pleno-vote-counter-si {
        background: #138a36;
    }

    .pleno-vote-counter-no {
        background: #d72638;
    }

    .pleno-vote-counter-abs {
        background: #f28c18;
    }

    .pleno-vote-counter-icon {
        width: 50px;
        height: 50px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 50px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.22);
        font-size: 1.35rem;
    }

    .pleno-vote-counter-label {
        font-size: 0.9rem;
        font-weight: 850;
        letter-spacing: 0.04em;
    }

    .pleno-vote-counter-number {
        margin-top: 0.1rem;
        font-size: 2.55rem;
        font-weight: 900;
        line-height: 1;
    }

    .pleno-vote-tables {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
        margin-top: 1.25rem;
    }

    .pleno-vote-table-card {
        padding: 1.25rem;
    }

    .pleno-vote-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .pleno-vote-table-title {
        margin: 0;
        color: #203949;
        font-size: 1.08rem;
        font-weight: 800;
    }

    .pleno-vote-participants {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        background: #eef2f5;
        color: #5e7180;
        font-size: 0.72rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .pleno-vote-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .pleno-vote-table th {
        padding: 0.75rem 0.85rem;
        background: #f1f5f8;
        color: #5f7280;
        font-size: 0.76rem;
        font-weight: 850;
        text-transform: uppercase;
    }

    .pleno-vote-table td {
        padding: 0.85rem;
        border-bottom: 1px solid #edf2f5;
        color: #253d4d;
        font-weight: 650;
        vertical-align: middle;
    }

    .pleno-vote-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .pleno-vote-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 78px;
        min-height: 28px;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        background: #eef2f5;
        color: #667987;
        font-size: 0.78rem;
        font-weight: 750;
        white-space: nowrap;
    }

    @media (max-width: 991.98px) {
        .pleno-vote-counters,
        .pleno-vote-tables {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .pleno-vote-main-card {
            padding: 1rem;
        }

        .pleno-vote-card-header,
        .pleno-vote-table-head {
            display: block;
        }

        .pleno-vote-close-btn {
            width: 100%;
            margin-top: 1rem;
        }

        .pleno-vote-participants {
            margin-top: 0.65rem;
        }

        .pleno-vote-table {
            min-width: 420px;
        }

        .pleno-vote-table-wrap {
            overflow-x: auto;
        }
    }
</style>

<div class="container-fluid mt-4 pleno-vote-page">
    <div class="pleno-vote-eyebrow">• VOTACIÓN EN VIVO</div>
    <h1 class="pleno-vote-title">Votación</h1>

    <section class="pleno-vote-main-card">
        <div class="pleno-vote-card-header">
            <div>
                <span class="pleno-vote-pill">PUNTO ACTUAL EN VOTACIÓN</span>
                <h2 class="pleno-vote-topic">Comisión Prueba - Tema: Agua en la comuna de Olmué</h2>
            </div>
            <button type="button" class="pleno-vote-close-btn">Cerrar Votación</button>
        </div>

        <div class="pleno-vote-counters" aria-label="Conteo visual de votos">
            <div class="pleno-vote-counter pleno-vote-counter-si">
                <span class="pleno-vote-counter-icon"><i class="fas fa-check"></i></span>
                <div>
                    <div class="pleno-vote-counter-label">SÍ</div>
                    <div class="pleno-vote-counter-number">0</div>
                </div>
            </div>
            <div class="pleno-vote-counter pleno-vote-counter-no">
                <span class="pleno-vote-counter-icon"><i class="fas fa-times"></i></span>
                <div>
                    <div class="pleno-vote-counter-label">NO</div>
                    <div class="pleno-vote-counter-number">0</div>
                </div>
            </div>
            <div class="pleno-vote-counter pleno-vote-counter-abs">
                <span class="pleno-vote-counter-icon"><i class="fas fa-hand-paper"></i></span>
                <div>
                    <div class="pleno-vote-counter-label">ABS</div>
                    <div class="pleno-vote-counter-number">0</div>
                </div>
            </div>
        </div>
    </section>

    <div class="pleno-vote-tables">
        <?php $renderTablaConsejeros('Consejeros 1 al 6', $consejerosGrupoUno); ?>
        <?php $renderTablaConsejeros('Consejeros 7 al 12', $consejerosGrupoDos); ?>
    </div>
</div>
