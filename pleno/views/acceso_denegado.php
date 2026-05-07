<?php
$plenoAuth = $data['pleno_auth'] ?? [];
$tipoNoPermitido = (int)($plenoAuth['tipoUsuarioId'] ?? 0);
?>
<div class="container-fluid mt-4">
    <div class="alert alert-danger" role="alert">
        <h5 class="alert-heading mb-2">Acceso denegado al módulo Pleno</h5>
        <p class="mb-0">
            Tu tipo de usuario actual (ID: <?php echo htmlspecialchars((string)$tipoNoPermitido, ENT_QUOTES, 'UTF-8'); ?>)
            no tiene permisos para ingresar a este módulo.
        </p>
    </div>
</div>

