<?php
// Vista base de la tabla del pleno.
require __DIR__ . '/partials/sidebar_pleno.php';
$perfilActivo = $data['pleno_auth']['perfilNombre'] ?? 'No detectado';
?>
<div class="container-fluid mt-4">
    <div class="alert alert-info py-2" role="alert">
        <strong>Perfil activo:</strong> <?php echo htmlspecialchars($perfilActivo, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-table me-2 text-primary"></i>Tabla del Pleno
        </h2>
        <a href="index.php" class="btn btn-pleno-back">
            <i class="fas fa-arrow-left me-2"></i>Atrás
        </a>
    </div>

    <div class="orden-dia-card">
        <div class="orden-dia-titulo">Orden del día</div>

        <div class="orden-dia-lista" id="ordenDiaLista">
            <div class="orden-dia-item" draggable="true">
                <span class="orden-dia-handle">⠿</span>
                <span class="orden-dia-numero">1.</span>
                <span class="orden-dia-texto">Lectura y aprobación del acta anterior</span>
            </div>

            <div class="orden-dia-item" draggable="true">
                <span class="orden-dia-handle">⠿</span>
                <span class="orden-dia-numero">2.</span>
                <span class="orden-dia-texto">Temas de comisiones</span>
            </div>

            <div class="orden-dia-item" draggable="true">
                <span class="orden-dia-handle">⠿</span>
                <span class="orden-dia-numero">3.</span>
                <span class="orden-dia-texto">Puntos varios</span>
            </div>
        </div>
    </div>
</div>

