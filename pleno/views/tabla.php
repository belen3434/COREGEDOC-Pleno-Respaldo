<?php
// Vista base de la tabla del pleno.
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
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h6 class="text-muted mb-3">Orden del día</h6>
            <ol class="mb-0">
                <li class="mb-2">Lectura y aprobación del acta anterior</li>
                <li class="mb-2">Temas de comisiones</li>
                <li class="mb-2">Puntos varios</li>
            </ol>
        </div>
    </div>
</div>

