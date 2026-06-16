<?php
$h = static function ($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};

$formatearFecha = static function (?string $fecha): string {
    if (!$fecha) {
        return 'No disponible';
    }

    $timestamp = strtotime($fecha);
    return $timestamp ? date('d/m/Y', $timestamp) : $fecha;
};

$formatearHora = static function (?string $hora): string {
    if (!$hora) {
        return 'No disponible';
    }

    return substr($hora, 0, 5) . ' hrs.';
};

$formatearFechaHora = static function (?string $fecha): string {
    if (!$fecha) {
        return 'No disponible';
    }

    $timestamp = strtotime($fecha);
    return $timestamp ? date('d/m/Y H:i', $timestamp) . ' hrs.' : $fecha;
};

$crearDataUri = static function (string $path): string {
    if (!is_file($path)) {
        return '';
    }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($extension === 'png' && !extension_loaded('gd')) {
        return '';
    }

    $contenido = file_get_contents($path);
    if ($contenido === false) {
        return '';
    }

    $mime = in_array($extension, ['jpg', 'jpeg'], true) ? 'image/jpeg' : 'image/png';

    return 'data:' . $mime . ';base64,' . base64_encode($contenido);
};

$normalizarEstado = static function ($estado): string {
    $estado = trim((string)$estado);
    return $estado !== '' ? strtoupper(str_replace('_', ' ', $estado)) : 'No disponible';
};

$seccionPunto = static function (string $numero): string {
    if ($numero === '2') {
        return 'Cuenta Gobernador';
    }
    if ($numero === '3' || strpos($numero, '3.') === 0) {
        return 'Cuenta Comisiones';
    }
    if ($numero === '4' || strpos($numero, '4.') === 0) {
        return 'Varios';
    }
    return 'Tabla';
};

$datosSesion = is_array($snapshot['datos_sesion'] ?? null) ? $snapshot['datos_sesion'] : [];
$asistencia = is_array($snapshot['asistencia'] ?? null) ? $snapshot['asistencia'] : [];
$participantes = is_array($asistencia['participantes'] ?? null) ? $asistencia['participantes'] : [];
$ordenDia = is_array($snapshot['orden_dia'] ?? null) ? $snapshot['orden_dia'] : [];
$votaciones = is_array($snapshot['votaciones'] ?? null) ? $snapshot['votaciones'] : [];
$acuerdos = is_array($snapshot['acuerdos'] ?? null) ? $snapshot['acuerdos'] : [];
$documentos = is_array($snapshot['documentos'] ?? null) ? $snapshot['documentos'] : [];
$usuario = is_array($snapshot['usuario'] ?? null) ? $snapshot['usuario'] : [];
$totales = is_array($snapshot['totales'] ?? null) ? $snapshot['totales'] : [];
$fechaGeneracion = (string)($snapshot['fecha_generacion'] ?? ($resumen['fecha_generacion'] ?? ''));
$version = (int)($snapshot['version'] ?? ($resumen['version'] ?? 1));
$numeroResumen = (string)($resumen['numero_resumen'] ?? '');
$hashValidacion = (string)($resumen['hash_validacion'] ?? '');
$usuarioGenerador = trim((string)($usuario['nombre_completo'] ?? '')) ?: (string)($resumen['usuario_generador'] ?? 'Sin registro');
$certificado = is_array($documentos['certificado_acuerdos'] ?? null) ? $documentos['certificado_acuerdos'] : null;

$totalAsistentes = (int)($asistencia['total'] ?? 0);
$presentes = (int)($asistencia['presentes'] ?? 0);
$ausentes = (int)($asistencia['ausentes'] ?? 0);
$porcentaje = $totalAsistentes > 0 ? round(($presentes / $totalAsistentes) * 100, 1) . '%' : 'No disponible';

$projectRoot = dirname(__DIR__, 3);
$logoEscudo = $crearDataUri($projectRoot . '/public/img/logo2.png');
$logoCore = $crearDataUri($projectRoot . '/public/img/logoCore1.png');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 38px 44px 58px; }
        body {
            color: #182f3f;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10.5px;
            line-height: 1.45;
        }
        .footer {
            border-top: 1px solid #b9c9d2;
            bottom: -36px;
            color: #607684;
            font-size: 8.5px;
            left: 0;
            position: fixed;
            right: 0;
            text-align: center;
        }
        .page-number:after { content: counter(page); }
        .document {
            border-top: 5px solid #003B5C;
            padding-top: 16px;
        }
        .header {
            border-bottom: 1px solid #b9c9d2;
            margin-bottom: 20px;
            padding-bottom: 14px;
        }
        .header-table, .simple-table, .meta-table {
            border-collapse: collapse;
            width: 100%;
        }
        .logo-cell {
            vertical-align: top;
            width: 23%;
        }
        .logo-cell.right { text-align: right; }
        .header-center {
            text-align: center;
            vertical-align: top;
            width: 54%;
        }
        .logo-escudo { width: 70px; }
        .logo-core { width: 92px; }
        .logo-fallback {
            border: 1px solid #b9c9d2;
            color: #003B5C;
            display: inline-block;
            font-size: 8.5px;
            font-weight: bold;
            padding: 8px 10px;
            text-align: center;
        }
        .institution {
            color: #003B5C;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .title {
            color: #003B5C;
            font-size: 16px;
            font-weight: bold;
            margin-top: 8px;
            text-transform: uppercase;
        }
        .subtitle {
            color: #304f62;
            font-size: 10px;
            margin-top: 6px;
        }
        .intro, .closing {
            margin: 12px 0 16px;
            text-align: justify;
        }
        .section-title {
            border-bottom: 1.5px solid #003B5C;
            color: #003B5C;
            font-size: 12px;
            font-weight: bold;
            margin: 18px 0 8px;
            padding-bottom: 4px;
            text-transform: uppercase;
        }
        .meta-table th {
            background: #eef3f6;
            border: 1px solid #c7d4dc;
            color: #003B5C;
            font-weight: bold;
            padding: 6px;
            text-align: left;
            width: 28%;
        }
        .meta-table td {
            border: 1px solid #c7d4dc;
            padding: 6px;
        }
        .simple-table th {
            background: #eef3f6;
            border: 1px solid #c7d4dc;
            color: #003B5C;
            font-weight: bold;
            padding: 5px;
            text-align: left;
        }
        .simple-table td {
            border: 1px solid #c7d4dc;
            padding: 5px;
            vertical-align: top;
        }
        .empty {
            border: 1px solid #c7d4dc;
            color: #607684;
            padding: 9px;
        }
        .hash {
            color: #607684;
            font-size: 8.5px;
            word-break: break-all;
        }
    </style>
</head>
<body>
<div class="footer">
    Consejo Regional de Valparaiso - Gobierno Regional de Valparaiso | Pagina <span class="page-number"></span>
</div>
<div class="document">
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <?php if ($logoEscudo !== ''): ?>
                        <img src="<?php echo $h($logoEscudo); ?>" class="logo-escudo" alt="Gobierno Regional">
                    <?php else: ?>
                        <span class="logo-fallback">GOBIERNO<br>REGIONAL</span>
                    <?php endif; ?>
                </td>
                <td class="header-center">
                    <div class="institution">Consejo Regional de Valparaiso</div>
                    <div class="title">Resumen Ejecutivo de Sesion Plenaria</div>
                    <div class="subtitle">
                        Sesion <?php echo $h($datosSesion['tipo_pleno'] ?? 'No disponible'); ?>
                        N&deg; <?php echo $h($datosSesion['numero_sesion'] ?? 'No disponible'); ?>
                    </div>
                </td>
                <td class="logo-cell right">
                    <?php if ($logoCore !== ''): ?>
                        <img src="<?php echo $h($logoCore); ?>" class="logo-core" alt="CORE Valparaiso">
                    <?php else: ?>
                        <span class="logo-fallback">CORE<br>VALPARAISO</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <p class="intro">
        En Valparaiso, con fecha <?php echo $h($formatearFecha($datosSesion['fecha'] ?? null)); ?>, se deja constancia del resumen ejecutivo correspondiente a la sesion plenaria <?php echo $h($datosSesion['tipo_pleno'] ?? 'No disponible'); ?> N&deg; <?php echo $h($datosSesion['numero_sesion'] ?? 'No disponible'); ?>, realizada en <?php echo $h($datosSesion['lugar'] ?? 'No disponible'); ?>, registrandose la asistencia, materias tratadas, votaciones efectuadas y acuerdos adoptados durante su desarrollo.
    </p>

    <div class="section-title">Informacion general de la sesion</div>
    <table class="meta-table">
        <tr><th>Numero de sesion</th><td><?php echo $h($datosSesion['numero_sesion'] ?? 'No disponible'); ?></td></tr>
        <tr><th>Tipo</th><td><?php echo $h($datosSesion['tipo_pleno'] ?? 'No disponible'); ?></td></tr>
        <tr><th>Estado</th><td><?php echo $h($normalizarEstado($datosSesion['estado'] ?? '')); ?></td></tr>
        <tr><th>Fecha</th><td><?php echo $h($formatearFecha($datosSesion['fecha'] ?? null)); ?></td></tr>
        <tr><th>Hora</th><td><?php echo $h($formatearHora($datosSesion['hora'] ?? null)); ?></td></tr>
        <tr><th>Lugar</th><td><?php echo $h($datosSesion['lugar'] ?? 'No disponible'); ?></td></tr>
        <tr><th>Usuario generador</th><td><?php echo $h($usuarioGenerador); ?></td></tr>
    </table>

    <div class="section-title">Asistencia</div>
    <table class="simple-table">
        <tr>
            <th>Total asistentes</th>
            <th>Presentes</th>
            <th>Ausentes</th>
            <th>Porcentaje de asistencia</th>
        </tr>
        <tr>
            <td><?php echo (int)$totalAsistentes; ?></td>
            <td><?php echo (int)$presentes; ?></td>
            <td><?php echo (int)$ausentes; ?></td>
            <td><?php echo $h($porcentaje); ?></td>
        </tr>
    </table>
    <?php if (empty($participantes)): ?>
        <div class="empty">No se registran asistentes para esta sesion.</div>
    <?php else: ?>
        <table class="simple-table">
            <tr><th>Participante</th><th>Estado</th><th>Hora de registro</th></tr>
            <?php foreach ($participantes as $participante): ?>
                <tr>
                    <td><?php echo $h($participante['nombreCompleto'] ?? 'No disponible'); ?></td>
                    <td><?php echo $h($normalizarEstado($participante['estadoAsistencia'] ?? 'AUSENTE')); ?></td>
                    <td><?php echo $h($formatearFechaHora($participante['fechaRegistroAsistencia'] ?? null)); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="section-title">Orden del dia</div>
    <?php if (empty($ordenDia)): ?>
        <div class="empty">No disponible.</div>
    <?php else: ?>
        <table class="simple-table">
            <tr><th>Seccion</th><th>Punto</th><th>Materia tratada</th></tr>
            <?php foreach ($ordenDia as $punto): ?>
                <?php $numeroPunto = (string)($punto['numero'] ?? ''); ?>
                <tr>
                    <td><?php echo $h($seccionPunto($numeroPunto)); ?></td>
                    <td><?php echo $h($numeroPunto !== '' ? $numeroPunto : 'No disponible'); ?></td>
                    <td><?php echo $h($punto['titulo'] ?? 'No disponible'); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="section-title">Votaciones</div>
    <?php if (empty($votaciones)): ?>
        <div class="empty">No se registran votaciones para esta sesion.</div>
    <?php else: ?>
        <table class="simple-table">
            <tr>
                <th>Punto asociado</th>
                <th>Estado</th>
                <th>Apertura</th>
                <th>Cierre</th>
                <th>A favor</th>
                <th>En contra</th>
                <th>Abstencion</th>
                <th>Resultado</th>
            </tr>
            <?php foreach ($votaciones as $votacion): ?>
                <tr>
                    <td><?php echo $h(trim((string)($votacion['numero'] ?? '')) . ' - ' . trim((string)($votacion['titulo'] ?? ''))); ?></td>
                    <td><?php echo $h($normalizarEstado($votacion['estado_votacion'] ?? '')); ?></td>
                    <td><?php echo $h($formatearFechaHora($votacion['fecha_inicio_votacion'] ?? null)); ?></td>
                    <td><?php echo $h($formatearFechaHora($votacion['fecha_cierre_votacion'] ?? null)); ?></td>
                    <td><?php echo (int)($votacion['si'] ?? 0); ?></td>
                    <td><?php echo (int)($votacion['no'] ?? 0); ?></td>
                    <td><?php echo (int)($votacion['abstencion'] ?? 0); ?></td>
                    <td><?php echo $h($votacion['resultado'] ?? 'No disponible'); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="section-title">Acuerdos adoptados</div>
    <?php if (empty($acuerdos)): ?>
        <div class="empty">No se registran acuerdos adoptados para esta sesion.</div>
    <?php else: ?>
        <table class="simple-table">
            <tr><th>Numero</th><th>Punto asociado</th><th>Texto o descripcion</th><th>Observaciones</th><th>Fecha creacion</th><th>Estado</th></tr>
            <?php foreach ($acuerdos as $indice => $acuerdo): ?>
                <tr>
                    <td><?php echo (int)$indice + 1; ?></td>
                    <td><?php echo $h(trim((string)($acuerdo['punto_numero'] ?? '')) . ' - ' . trim((string)($acuerdo['titulo_punto'] ?? ''))); ?></td>
                    <td><?php echo nl2br($h($acuerdo['texto_acuerdo'] ?? 'No disponible')); ?></td>
                    <td><?php echo $h($acuerdo['observaciones'] ?? 'No disponible'); ?></td>
                    <td><?php echo $h($formatearFechaHora($acuerdo['fecha_creacion'] ?? null)); ?></td>
                    <td><?php echo $h($normalizarEstado($acuerdo['estado'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="section-title">Documentos generados</div>
    <table class="simple-table">
        <tr><th>Documento</th><th>Version</th><th>Fecha de generacion</th></tr>
        <tr>
            <td>Certificado de acuerdos</td>
            <td><?php echo $certificado ? $h($certificado['version'] ?? 'No disponible') : 'No disponible'; ?></td>
            <td><?php echo $certificado ? $h($formatearFechaHora($certificado['fecha_generacion'] ?? null)) : 'No disponible'; ?></td>
        </tr>
        <tr>
            <td>Resumen ejecutivo generado</td>
            <td><?php echo (int)$version; ?></td>
            <td><?php echo $h($formatearFechaHora($fechaGeneracion)); ?></td>
        </tr>
    </table>

    <p class="closing">
        El presente Resumen Ejecutivo consolida la informacion registrada durante la sesion plenaria, incluyendo asistencia, votaciones, acuerdos adoptados y materias tratadas, constituyendo un documento de consulta y respaldo institucional.
    </p>

    <div class="section-title">Control documental</div>
    <table class="meta-table">
        <tr><th>Numero resumen</th><td><?php echo $h($numeroResumen); ?></td></tr>
        <tr><th>Version</th><td><?php echo (int)$version; ?></td></tr>
        <tr><th>Fecha generacion</th><td><?php echo $h($formatearFechaHora($fechaGeneracion)); ?></td></tr>
        <tr><th>Usuario generador</th><td><?php echo $h($usuarioGenerador); ?></td></tr>
        <tr><th>Hash de validacion</th><td class="hash"><?php echo $h($hashValidacion); ?></td></tr>
    </table>
</div>
</body>
</html>
