<?php
$h = static function ($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};

$formatearFecha = static function (?string $fecha): string {
    if (!$fecha) {
        return 'Sin fecha';
    }

    $timestamp = strtotime($fecha);
    return $timestamp ? date('d/m/Y', $timestamp) : $fecha;
};

$formatearHora = static function (?string $hora): string {
    if (!$hora) {
        return 'Sin hora';
    }

    return substr($hora, 0, 5) . ' hrs.';
};

$formatearFechaHora = static function (?string $fecha): string {
    if (!$fecha) {
        return 'Sin registro';
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

$datosSesion = is_array($snapshot['datos_sesion'] ?? null) ? $snapshot['datos_sesion'] : [];
$acuerdos = is_array($snapshot['acuerdos'] ?? null) ? $snapshot['acuerdos'] : [];
$usuario = is_array($snapshot['usuario'] ?? null) ? $snapshot['usuario'] : [];
$fechaGeneracion = (string)($snapshot['fecha_generacion'] ?? ($certificado['fecha_generacion'] ?? ''));
$version = (int)($snapshot['version'] ?? ($certificado['version'] ?? 1));
$numeroCertificado = (string)($certificado['numero_certificado'] ?? '');
$hashValidacion = (string)($certificado['hash_validacion'] ?? '');
$usuarioGenerador = trim((string)($usuario['nombre_completo'] ?? '')) ?: (string)($certificado['usuario_generador'] ?? 'Sin registro');

$projectRoot = dirname(__DIR__, 3);
$logoEscudo = $crearDataUri($projectRoot . '/public/img/logo2.png');
$logoCore = $crearDataUri($projectRoot . '/public/img/logoCore1.png');
$numeroSesion = trim((string)($datosSesion['numero_sesion'] ?? ''));
$tipoSesion = trim((string)($datosSesion['tipo_pleno'] ?? ''));
$lugarSesion = trim((string)($datosSesion['lugar'] ?? ''));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 38px 44px 42px;
        }

        body {
            color: #182f3f;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11.5px;
            line-height: 1.48;
        }

        .document {
            border-top: 5px solid #003B5C;
            padding-top: 18px;
        }

        .header {
            border-bottom: 1px solid #b9c9d2;
            margin-bottom: 22px;
            padding-bottom: 15px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo-cell {
            width: 24%;
            vertical-align: top;
        }

        .header-logo-cell.right {
            text-align: right;
        }

        .header-center {
            width: 52%;
            text-align: center;
            vertical-align: top;
        }

        .logo-escudo {
            width: 72px;
            height: auto;
        }

        .logo-core {
            width: 118px;
            height: auto;
        }

        .logo-fallback {
            border: 1px solid #c8d7df;
            color: #17384d;
            display: inline-block;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.025em;
            line-height: 1.25;
            padding: 8px 9px;
            text-align: center;
            text-transform: uppercase;
        }

        .institution-title {
            color: #17384d;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.035em;
            margin-top: 4px;
            text-transform: uppercase;
        }

        .certificate-title {
            color: #003B5C;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 0.04em;
            margin-top: 9px;
            text-transform: uppercase;
        }

        .certificate-number {
            color: #344d5e;
            font-size: 12px;
            font-weight: bold;
            margin-top: 7px;
        }

        .intro {
            margin: 0 0 18px;
            text-align: justify;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            border-bottom: 1px solid #c8d7df;
            color: #17384d;
            font-size: 12.5px;
            font-weight: bold;
            letter-spacing: 0.025em;
            margin-bottom: 9px;
            padding-bottom: 4px;
            text-transform: uppercase;
        }

        .session-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #d7e2e8;
            border-bottom: 1px solid #d7e2e8;
        }

        .session-table td {
            padding: 7px 8px;
            vertical-align: top;
        }

        .session-label {
            color: #607686;
            display: block;
            font-size: 9.5px;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .session-value {
            color: #1d3749;
            font-weight: bold;
        }

        .agreement {
            border-top: 1px solid #b9c9d2;
            margin-top: 16px;
            padding-top: 13px;
            page-break-inside: avoid;
        }

        .agreement:first-child {
            margin-top: 0;
        }

        .agreement-title {
            color: #003B5C;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 9px;
            text-transform: uppercase;
        }

        .agreement-label {
            color: #17384d;
            font-size: 10.5px;
            font-weight: bold;
            margin: 7px 0 2px;
        }

        .agreement-text {
            color: #263f50;
            text-align: justify;
        }

        .certification-box {
            background: #f7fafb;
            border: 1px solid #c8d7df;
            border-left: 4px solid #003B5C;
            color: #203949;
            padding: 12px 14px;
            text-align: justify;
        }

        .footer {
            border-top: 1px solid #aebfc9;
            color: #526b7a;
            font-size: 9.8px;
            margin-top: 28px;
            padding-top: 11px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .footer-label {
            color: #17384d;
            font-weight: bold;
            width: 116px;
        }

        .hash-row td {
            padding-top: 7px;
        }

        .hash-value {
            color: #7a8c97;
            font-size: 8.5px;
            line-height: 1.35;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="document">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-logo-cell">
                        <?php if ($logoEscudo !== ''): ?>
                            <img class="logo-escudo" src="<?php echo $h($logoEscudo); ?>" alt="Gobierno Regional de Valpara&iacute;so">
                        <?php else: ?>
                            <span class="logo-fallback">Gobierno Regional<br>Valpara&iacute;so</span>
                        <?php endif; ?>
                    </td>
                    <td class="header-center">
                        <div class="institution-title">Consejo Regional de Valpara&iacute;so</div>
                        <div class="certificate-title">Certificado de Acuerdos Plenarios</div>
                        <div class="certificate-number">N&deg; <?php echo $h($numeroCertificado); ?></div>
                    </td>
                    <td class="header-logo-cell right">
                        <?php if ($logoCore !== ''): ?>
                            <img class="logo-core" src="<?php echo $h($logoCore); ?>" alt="CORE Valpara&iacute;so">
                        <?php else: ?>
                            <span class="logo-fallback">Consejo Regional<br>Valpara&iacute;so</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>

        <p class="intro">
            En Valpara&iacute;so, con fecha <?php echo $h($formatearFecha($datosSesion['fecha'] ?? null)); ?>,
            se certifica que en la sesi&oacute;n <?php echo $h($tipoSesion !== '' ? $tipoSesion : 'plenaria'); ?>
            <?php echo $h($numeroSesion !== '' ? $numeroSesion : 'sin numero'); ?> del Consejo Regional de Valpara&iacute;so,
            realizada en <?php echo $h($lugarSesion !== '' ? $lugarSesion : 'lugar no registrado'); ?>,
            se adoptaron los siguientes acuerdos:
        </p>

        <div class="section">
            <div class="section-title">Datos de la sesi&oacute;n</div>
            <table class="session-table">
                <tr>
                    <td>
                        <span class="session-label">N&uacute;mero de sesi&oacute;n</span>
                        <span class="session-value"><?php echo $h($numeroSesion !== '' ? $numeroSesion : 'Sin numero'); ?></span>
                    </td>
                    <td>
                        <span class="session-label">Tipo de sesi&oacute;n</span>
                        <span class="session-value"><?php echo $h($tipoSesion !== '' ? $tipoSesion : 'Sin tipo'); ?></span>
                    </td>
                    <td>
                        <span class="session-label">Fecha</span>
                        <span class="session-value"><?php echo $h($formatearFecha($datosSesion['fecha'] ?? null)); ?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="session-label">Hora</span>
                        <span class="session-value"><?php echo $h($formatearHora($datosSesion['hora'] ?? null)); ?></span>
                    </td>
                    <td colspan="2">
                        <span class="session-label">Lugar</span>
                        <span class="session-value"><?php echo $h($lugarSesion !== '' ? $lugarSesion : 'Sin lugar'); ?></span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Acuerdos</div>
            <?php foreach ($acuerdos as $indice => $acuerdo): ?>
                <?php
                $puntoNumero = trim((string)($acuerdo['punto_numero'] ?? ''));
                $tituloPunto = trim((string)($acuerdo['titulo_punto'] ?? ''));
                $puntoTratado = trim($puntoNumero . ($puntoNumero !== '' && $tituloPunto !== '' ? ' - ' : '') . $tituloPunto);
                $textoAcuerdo = trim((string)($acuerdo['texto_acuerdo'] ?? ''));
                ?>
                <div class="agreement">
                    <div class="agreement-title">Acuerdo N&deg; <?php echo (int)$indice + 1; ?></div>
                    <?php if ($puntoTratado !== ''): ?>
                        <div class="agreement-label">Punto tratado:</div>
                        <div class="agreement-text"><?php echo $h($puntoTratado); ?></div>
                    <?php endif; ?>
                    <?php if ($textoAcuerdo !== ''): ?>
                        <div class="agreement-label">Texto del acuerdo:</div>
                        <div class="agreement-text"><?php echo nl2br($h($textoAcuerdo)); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section">
            <div class="section-title">Certificaci&oacute;n</div>
            <div class="certification-box">
                Se certifica que los acuerdos precedentemente individualizados corresponden a decisiones adoptadas durante la sesi&oacute;n plenaria indicada, quedando registrados en el sistema COREGEDOC para efectos de trazabilidad documental y control institucional.
            </div>
        </div>

        <div class="footer">
            <table class="footer-table">
                <tr>
                    <td class="footer-label">Usuario generador:</td>
                    <td><?php echo $h($usuarioGenerador); ?></td>
                </tr>
                <tr>
                    <td class="footer-label">Fecha generaci&oacute;n:</td>
                    <td><?php echo $h($formatearFechaHora($fechaGeneracion)); ?></td>
                </tr>
                <tr>
                    <td class="footer-label">Versi&oacute;n:</td>
                    <td><?php echo (int)$version; ?></td>
                </tr>
                <tr>
                    <td class="footer-label">N&uacute;mero certificado:</td>
                    <td><?php echo $h($numeroCertificado); ?></td>
                </tr>
                <tr class="hash-row">
                    <td class="footer-label">Hash de validaci&oacute;n:</td>
                    <td class="hash-value"><?php echo $h($hashValidacion); ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
