<?php
// Vista base de sesiones plenarias.
?>
<div class="container-fluid mt-4 pt-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calendar-check me-2 text-primary"></i>Sesiones Plenarias
        </h2>
        <a href="index.php?vista=crear_sesion" class="btn btn-success btn-sm">
            <i class="fas fa-plus me-1"></i>Nueva sesion
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Sesion</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Sin registros por mostrar.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

