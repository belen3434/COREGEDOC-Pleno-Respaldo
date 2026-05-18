<?php
// Vista principal del módulo de pleno.
require __DIR__ . '/partials/sidebar_pleno.php';
$perfilActivo = $data['pleno_auth']['perfilNombre'] ?? 'No detectado';
$puedeGestionar = (bool)($data['pleno_puede_gestionar'] ?? false);
?>
<div class="container-fluid mt-4">
    <div class="alert alert-info py-2" role="alert">
        <strong>Perfil activo:</strong> <?php echo htmlspecialchars($perfilActivo, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-landmark me-2 text-primary"></i>Módulo de Pleno
        </h2>
    </div>

    <div class="row g-3">
        <?php if ($puedeGestionar): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Crear sesión</h5>
                        <p class="card-text text-muted">Registra una nueva sesión plenaria.</p>
                        <a href="index.php?vista=crear_sesion" class="btn btn-success btn-sm">
                            <i class="fas fa-plus me-1"></i>Nueva sesión
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Sesiones plenarias</h5>
                        <p class="card-text text-muted">Consulta y administra las sesiones del pleno.</p>
                        <a href="index.php?vista=sesiones" class="btn btn-primary btn-sm">
                            <i class="fas fa-list me-1"></i>Ver sesiones
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Tabla del Día</h5>
                    <p class="card-text text-muted">Visualiza el orden del día de la sesión.</p>
                    <a href="index.php?vista=tabla" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-table me-1"></i>Ver tabla
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

