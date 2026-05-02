<?php
// Vista principal del modulo de pleno.
?>
<div class="container-fluid mt-4 pt-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-landmark me-2 text-primary"></i>Modulo de Pleno
        </h2>
        <a href="../index.php?action=home" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver al inicio
        </a>
    </div>

    <div class="row g-3">
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

        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Crear sesion</h5>
                    <p class="card-text text-muted">Registra una nueva sesion plenaria.</p>
                    <a href="index.php?vista=crear_sesion" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i>Nueva sesion
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Tabla del pleno</h5>
                    <p class="card-text text-muted">Visualiza el orden del dia de la sesion.</p>
                    <a href="index.php?vista=tabla" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-table me-1"></i>Ver tabla
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

