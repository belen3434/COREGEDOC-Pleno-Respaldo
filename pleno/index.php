<?php

require_once dirname(__DIR__) . '/app/config/Constants.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['idUsuario'])) {
    header('Location: ../index.php?action=login');
    exit();
}

$vista = $_GET['vista'] ?? 'index';

$vistasPermitidas = [
    'index' => __DIR__ . '/views/index.php',
    'sesiones' => __DIR__ . '/views/sesiones.php',
    'crear_sesion' => __DIR__ . '/views/crear_sesion.php',
    'editar_sesion' => __DIR__ . '/views/editar_sesion.php',
    'tabla' => __DIR__ . '/views/tabla.php',
];

$childView = $vistasPermitidas[$vista] ?? $vistasPermitidas['index'];

$data = [
    'usuario' => [
        'nombre' => $_SESSION['pNombre'] ?? '',
        'apellido' => $_SESSION['aPaterno'] ?? '',
        'rol' => $_SESSION['tipoUsuario_id'] ?? 0,
    ],
    'pagina_actual' => 'pleno',
];

require_once dirname(__DIR__) . '/app/views/layouts/main.php';
