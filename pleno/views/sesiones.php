<?php
// Vista base de sesiones plenarias.
require __DIR__ . '/partials/sidebar_pleno.php';
$perfilActivo = $data['pleno_auth']['perfilNombre'] ?? 'No detectado';
$sesiones = $data['pleno_sesiones'] ?? [];
$puedeGestionar = (bool)($data['pleno_puede_gestionar'] ?? false);
$flash = $data['pleno_flash'] ?? null;
$crudError = $data['pleno_crud_error'] ?? null;
?>
<div class="container-fluid mt-4">
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo htmlspecialchars($flash['tipo'], ENT_QUOTES, 'UTF-8'); ?>" role="alert">
            <?php echo htmlspecialchars($flash['mensaje'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if ($crudError): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($crudError, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-info py-2" role="alert">
        <strong>Perfil activo:</strong> <?php echo htmlspecialchars($perfilActivo, ENT_QUOTES, 'UTF-8'); ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-calendar-check me-2 text-primary"></i>Sesiones plenarias
        </h2>
        <a href="index.php" class="btn btn-pleno-back">
            <i class="fas fa-arrow-left me-2"></i>Atrás
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Número de sesión</th>
                            <th>Tipo de pleno</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Lugar</th>
                            <?php if ($puedeGestionar): ?>
                                <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sesiones)): ?>
                            <tr>
                                <td colspan="<?php echo $puedeGestionar ? '7' : '6'; ?>" class="text-center text-muted py-4">
                                    Sin registros por mostrar.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sesiones as $sesion): ?>
                                <tr>
                                    <td><?php echo (int)$sesion['id_sesion']; ?></td>
                                    <td><?php echo htmlspecialchars($sesion['numero_sesion'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($sesion['tipo_pleno']), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($sesion['fecha'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars(substr((string)$sesion['hora'], 0, 5), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars((string)($sesion['lugar'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <?php if ($puedeGestionar): ?>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a
                                                    href="index.php?vista=editar_sesion&id=<?php echo (int)$sesion['id_sesion']; ?>"
                                                    class="btn btn-outline-primary btn-sm"
                                                >
                                                    <i class="fas fa-pen me-1"></i>Editar
                                                </a>
                                                <a
                                                    href="index.php?action=eliminar_sesion&id=<?php echo (int)$sesion['id_sesion']; ?>"
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('¿Está seguro que desea eliminar esta sesión plenaria?');"
                                                >
                                                    <i class="fas fa-trash-alt me-1"></i>Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

