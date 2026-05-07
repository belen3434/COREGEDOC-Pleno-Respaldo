<?php
// Vista base para crear una sesión plenaria.
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-plus-circle me-2 text-success"></i>Crear sesión plenaria
        </h2>
        <a href="index.php" class="btn btn-pleno-back">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="#" method="post">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Título de sesión</label>
                        <input type="text" name="titulo" class="form-control" placeholder="Ej: Sesión Ordinaria N 01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Seleccionar</option>
                            <option value="programada">Programada</option>
                            <option value="en_curso">En curso</option>
                            <option value="cerrada">Cerrada</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fecha</label>
                        <input type="date" name="fecha" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Hora</label>
                        <input type="time" name="hora" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Observaciones</label>
                        <textarea name="observaciones" rows="4" class="form-control" placeholder="Detalle adicional"></textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="index.php?vista=sesiones" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Guardar sesión
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

