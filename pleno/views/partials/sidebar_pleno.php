<?php
$plenoVistaActual = $_GET['vista'] ?? 'index';
$rol = (int)($_SESSION['usuario']['tipoUsuario_id'] ?? $_SESSION['tipoUsuario_id'] ?? 0);
$puedeVerMenuCompleto = in_array($rol, [6, 20], true);

$plenoMenuPrincipal = [];

if ($puedeVerMenuCompleto) {
    $plenoMenuPrincipal['configuracion'] = [
        'label' => 'Configuración',
        'icon' => 'fas fa-cog',
        'href' => 'index.php?vista=configuracion',
    ];

    $plenoMenuPrincipal['comisiones'] = [
        'label' => 'Comisiones',
        'icon' => 'fas fa-sitemap',
        'href' => 'index.php?vista=comisiones',
    ];
}

$plenoMenuPrincipal += [
    'pleno_vivo' => [
        'label' => 'Pleno en Vivo',
        'icon' => 'fas fa-broadcast-tower',
        'href' => 'index.php?vista=pleno_vivo',
    ],
    'votacion' => [
        'label' => 'Votación',
        'icon' => 'fas fa-vote-yea',
        'href' => 'index.php?vista=votacion',
    ],
    'resumen' => [
        'label' => 'Resumen',
        'icon' => 'fas fa-chart-pie',
        'href' => 'index.php?vista=resumen',
    ],
];

$plenoMenuInferior = [
    'ayuda' => [
        'label' => 'Ayuda',
        'icon' => 'fas fa-question-circle',
        'href' => 'index.php?vista=ayuda',
        'extraClass' => '',
    ],
    'salir' => [
        'label' => 'Salir',
        'icon' => 'fas fa-sign-out-alt',
        'href' => '../index.php',
        'extraClass' => ' pleno-sidebar-link-danger',
    ],
];
?>
<template id="plenoSidebarMenuTemplate">
    <div class="pleno-sidebar-menu" aria-label="Menú interno del módulo Pleno">
        <div class="pleno-sidebar-section">
            <p class="pleno-sidebar-label mb-2">Módulo Pleno</p>
            <?php foreach ($plenoMenuPrincipal as $key => $item) : ?>
                <?php $isActive = $plenoVistaActual === $key; ?>
                <a
                    class="pleno-sidebar-link<?php echo $isActive ? ' active' : ''; ?>"
                    href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php echo $isActive ? 'aria-current="page"' : ''; ?>
                >
                    <i class="<?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                    <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="pleno-sidebar-section pleno-sidebar-section-bottom">
            <?php foreach ($plenoMenuInferior as $key => $item) : ?>
                <?php $isActive = $plenoVistaActual === $key; ?>
                <a
                    class="pleno-sidebar-link<?php echo $isActive ? ' active' : ''; ?><?php echo $item['extraClass']; ?>"
                    href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>"
                    <?php echo $isActive ? 'aria-current="page"' : ''; ?>
                >
                    <i class="<?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                    <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</template>
<script src="../pleno/assets/js/pleno.js" defer></script>
