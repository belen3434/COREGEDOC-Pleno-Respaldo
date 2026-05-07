<?php
// Vista base para editar una sesión plenaria.
?>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-pen-to-square me-2 text-primary"></i>Editar sesión plenaria
        </h2>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="#" method="post">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Título de sesión</label>
                        <input type="text" name="titulo" class="form-control" value="Sesión Ordinaria N 01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="programada" selected>Programada</option>
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
                        <textarea name="observaciones" rows="4" class="form-control">Sin observaciones.</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end gap-2">
                    <a href="index.php?vista=sesiones" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Actualizar sesión
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

