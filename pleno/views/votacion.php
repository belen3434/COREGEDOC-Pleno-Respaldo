<?php
require __DIR__ . '/partials/sidebar_pleno.php';

$gruposConsejeros = [
    [
        'titulo' => 'Consejeros 1 al 6',
        'badge' => '6 PARTICIPANTES',
        'filas' => [
            ['nombre' => 'Ana Morales Rojas', 'voto' => 'Sin votar'],
            ['nombre' => 'Carlos Soto Reyes', 'voto' => 'Sin votar'],
            ['nombre' => 'Evelyn Mansilla Contreras', 'voto' => 'Sin votar'],
            ['nombre' => 'Manuel Millones Tapia', 'voto' => 'Sin votar'],
            ['nombre' => 'Paola Zamorano Alvarado', 'voto' => 'Sin votar'],
            ['nombre' => 'Rodrigo Mena Flores', 'voto' => 'Sin votar'],
        ],
    ],
    [
        'titulo' => 'Consejeros 7 al 12',
        'badge' => '6 PARTICIPANTES',
        'filas' => [
            ['nombre' => 'Isabel Nunez Tapia', 'voto' => 'Sin votar'],
            ['nombre' => 'Jorge Valdes Farias', 'voto' => 'Sin votar'],
            ['nombre' => 'Leonardo Contreras Silva', 'voto' => 'Sin votar'],
            ['nombre' => 'María Delgado Vergara', 'voto' => 'Sin votar'],
            ['nombre' => 'Sebastián Balbontín Álvarez', 'voto' => 'Sin votar'],
            ['nombre' => 'Viviana Ponce Domínguez', 'voto' => 'Sin votar'],
        ],
    ],
];

$contadores = [
    [
        'icono' => 'fas fa-check',
        'label' => 'SÍ',
        'valor' => '0',
        'cardClass' => 'pleno-votacion-counter-yes',
    ],
    [
        'icono' => 'fas fa-xmark',
        'label' => 'NO',
        'valor' => '0',
        'cardClass' => 'pleno-votacion-counter-no',
    ],
    [
        'icono' => 'fas fa-hand-paper',
        'label' => 'ABS',
        'valor' => '0',
        'cardClass' => 'pleno-votacion-counter-abs',
    ],
];
?>
<div class="container-fluid mt-4 pleno-votacion-view">
    <header class="pleno-votacion-header mb-4">
        <p class="pleno-votacion-kicker mb-2">
            <span class="pleno-votacion-kicker-dot">&bull;</span> VOTACIÓN EN VIVO
        </p>
        <h2 class="pleno-votacion-title mb-0">Votación</h2>
    </header>

    <section class="card shadow-sm pleno-session-card pleno-votacion-main-card mb-4">
        <div class="card-body">
            <div class="pleno-votacion-main-top">
                <div class="pleno-votacion-main-copy">
                    <span class="pleno-votacion-point-label">PUNTO ACTUAL EN VOTACIÓN</span>
                    <h3 class="pleno-votacion-point-title mb-0">Comisión Prueba - Tema: Agua en la comuna de Olmué</h3>
                </div>

                <button type="button" class="btn pleno-votacion-close-btn">
                    Cerrar Votación
                </button>
            </div>

            <div class="pleno-votacion-counter-grid">
                <?php foreach ($contadores as $contador): ?>
                    <article class="pleno-votacion-counter-card <?php echo htmlspecialchars($contador['cardClass'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="pleno-votacion-counter-icon">
                            <i class="<?php echo htmlspecialchars($contador['icono'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                        </div>
                        <div class="pleno-votacion-counter-copy">
                            <span class="pleno-votacion-counter-label"><?php echo htmlspecialchars($contador['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <strong class="pleno-votacion-counter-value"><?php echo htmlspecialchars($contador['valor'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="row g-4">
        <?php foreach ($gruposConsejeros as $grupo): ?>
            <div class="col-12 col-xl-6">
                <section class="pleno-panel pleno-votacion-table-card h-100">
                    <div class="pleno-panel-body">
                        <div class="pleno-votacion-table-head">
                            <h3 class="pleno-votacion-table-title mb-0"><?php echo htmlspecialchars($grupo['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <span class="pleno-votacion-table-badge"><?php echo htmlspecialchars($grupo['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>

                        <div class="table-responsive">
                            <table class="table pleno-votacion-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">CONSEJERO</th>
                                        <th scope="col">VOTO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($grupo['filas'] as $fila): ?>
                                        <tr>
                                            <td class="pleno-votacion-person-cell">
                                                <?php echo htmlspecialchars($fila['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td>
                                                <span class="pleno-votacion-vote-badge">
                                                    <?php echo htmlspecialchars($fila['voto'], ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        <?php endforeach; ?>
    </div>
</div>
