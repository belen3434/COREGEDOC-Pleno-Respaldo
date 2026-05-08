<style>
    .bg-gradient-core-dark {
        /* Fallback para navegadores antiguos */
        background: #000000; 
        
        /* Degradado de 5 colores: Azul -> Verde -> Amarillo -> Gris -> Negro */
        background: linear-gradient(
            90deg, 
            #0071bc 20%,   /* Azul (Inicio) */
            #00a650 40%,   /* Verde */
            #f7931e 60%,   /* Amarillo */
            #6D6A75 80%,   /* Gris */
            #000000 100%   /* Negro (Fin) */
        );
        
        border-bottom: 1px solid #333;
    }
</style>

<?php
$isPleno = (($paginaActual ?? '') === 'pleno');
$indexPath = $isPleno ? '../index.php' : 'index.php';

$rutaImagenSesion = $_SESSION['rutaImagenPerfil'] ?? '';
$rutaImagenFisica = '';

if (!empty($rutaImagenSesion)) {
    $rutaNormalizada = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaImagenSesion);
    $rutaImagenFisica = ROOT_PATH . DIRECTORY_SEPARATOR . ltrim($rutaNormalizada, DIRECTORY_SEPARATOR);
}

$imgPerfil = (!empty($rutaImagenFisica) && file_exists($rutaImagenFisica))
    ? $rutaImagenSesion
    : 'public/img/user_placeholder.png';

if ($isPleno && !preg_match('#^(?:[a-z]+:)?//#i', $imgPerfil)) {
    $imgPerfil = '../' . ltrim($imgPerfil, '/\\');
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-core-dark shadow fixed-top">
    <div class="container-fluid">
        
        <button class="btn btn-link text-white me-3" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <a class="navbar-brand fw-bold text-white" href="<?php echo $indexPath; ?>?action=home">
            </i>CORE<span class="text-white-50"> REGIÓN DE VALPARAÍSO</span>
        </a>

        <div class="ms-auto d-flex align-items-center">
            
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-white" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $imgPerfil; ?>" alt="Perfil" width="32" height="32" class="rounded-circle me-2 border border-white" style="object-fit: cover;">
                    
                    <span class="d-none d-md-inline fw-medium">
                        <?php 
                            echo htmlspecialchars(($_SESSION['pNombre'] ?? 'Usuario') . ' ' . ($_SESSION['aPaterno'] ?? '')); 
                        ?>
                    </span>
                </a>
                
               <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                    <li>
                        <div class="dropdown-header text-center">
                            <strong><?php echo htmlspecialchars($_SESSION['pNombre'] ?? 'Usuario'); ?></strong><br>
                            <small class="text-muted">
                                <?php echo htmlspecialchars($_SESSION['email'] ?? $_SESSION['correo'] ?? 'Sin correo'); ?>
                            </small>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    
                    <li>
                        <a class="dropdown-item" href="<?php echo $indexPath; ?>?action=perfil">
                            <i class="fas fa-user fa-fw me-2 text-primary"></i> Ver mi perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?php echo $indexPath; ?>?action=configuracion">
                            <i class="fas fa-cog fa-fw me-2 text-secondary"></i> Configuración
                        </a>
                    </li>
                    
                    <li><hr class="dropdown-divider"></li>
                    
                    <li>
                        <a class="dropdown-item text-danger" href="<?php echo $indexPath; ?>?action=logout">
                            <i class="fas fa-sign-out-alt fa-fw me-2"></i> Cerrar sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
