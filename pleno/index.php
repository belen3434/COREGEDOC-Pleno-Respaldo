<?php

$vista = $_GET['vista'] ?? 'index';

$vistasPermitidas = [
    'index' => __DIR__ . '/views/index.php',
    'sesiones' => __DIR__ . '/views/sesiones.php',
    'crear_sesion' => __DIR__ . '/views/crear_sesion.php',
    'editar_sesion' => __DIR__ . '/views/editar_sesion.php',
    'tabla' => __DIR__ . '/views/tabla.php',
];

$vistaActual = $vistasPermitidas[$vista] ?? $vistasPermitidas['index'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modulo de Pleno</title>
    <link rel="stylesheet" href="assets/css/pleno.css">
</head>
<body>
    <nav>
        <a href="../index.php?action=home">Volver al CORE</a>
        <a href="index.php">Inicio Pleno</a>
        <a href="index.php?vista=sesiones">Sesiones</a>
        <a href="index.php?vista=crear_sesion">Crear Sesion</a>
        <a href="index.php?vista=tabla">Tabla</a>
    </nav>

    <main>
        <?php include $vistaActual; ?>
    </main>

    <script src="assets/js/pleno.js"></script>
</body>
</html>
