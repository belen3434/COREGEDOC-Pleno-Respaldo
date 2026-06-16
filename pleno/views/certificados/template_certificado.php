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

$datosSesion = is_array($snapshot['datos_sesion'] ?? null) ? $snapshot['datos_sesion'] : [];
$acuerdos = is_array($snapshot['acuerdos'] ?? null) ? $snapshot['acuerdos'] : [];
$usuario = is_array($snapshot['usuario'] ?? null) ? $snapshot['usuario'] : [];
$fechaGeneracion = (string)($snapshot['fecha_generacion'] ?? ($certificado['fecha_generacion'] ?? ''));
$version = (int)($snapshot['version'] ?? ($certificado['version'] ?? 1));
$numeroCertificado = (string)($certificado['numero_certificado'] ?? '');
$hashValidacion = (string)($certificado['hash_validacion'] ?? '');
$usuarioGenerador = trim((string)($usuario['nombre_completo'] ?? '')) ?: (string)($certificado['usuario_generador'] ?? 'Sin registro');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 34px 42px 42px;
        }

        body {
            color: #203949;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }

        .header {
            border-bottom: 3px solid #0f8f4c;
            padding-bottom: 16px;
            margin-bottom: 22px;
        }

        .logo {
            width: 118px;
            height: auto;
        }

        .institution {
            color: #5d7180;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        h1 {
            margin: 18px 0 6px;
            color: #172f3f;
            font-size: 22px;
            letter-spacing: 0.05em;
            text-align: center;
        }

        .certificate-number {
            color: #0f8f4c;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }

        .section {
            margin-top: 22px;
        }

        .section-title {
            border-bottom: 1px solid #dce7ed;
            color: #172f3f;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            text-transform: uppercase;
        }

        .session-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .session-grid td {
            border: 1px solid #e2ebf0;
            padding: 8px 9px;
            vertical-align: top;
        }

        .label {
            color: #657987;
            display: block;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .value {
            color: #203949;
            font-weight: bold;
        }

        .agreement {
            border: 1px solid #e2ebf0;
            border-left: 4px solid #0f8f4c;
            margin-bottom: 12px;
            padding: 11px 12px;
        }

        .agreement-title {
            color: #0f8f4c;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .agreement-label {
            color: #657987;
            font-size: 10px;
            font-weight: bold;
            margin-top: 6px;
            text-transform: uppercase;
        }

        .certification-text {
            background: #eef9f2;
            border: 1px solid #d8efdf;
            color: #203949;
            font-size: 13px;
            font-weight: bold;
            padding: 13px 15px;
            text-align: justify;
        }

        .footer {
            border-top: 1px solid #dce7ed;
            color: #657987;
            font-size: 10px;
            margin-top: 26px;
            padding-top: 12px;
        }

        .footer-row {
            margin-bottom: 5px;
        }

        .hash {
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Logo deshabilitado temporalmente para diagnóstico de Dompdf/GD -->
        <div class="institution">Consejo Regional de Valpara&iacute;so</div>
        <h1>CERTIFICADO DE ACUERDOS PLENARIOS</h1>
        <div class="certificate-number"><?php echo $h($numeroCertificado); ?></div>
    </div>

    <div class="section">
        <div class="section-title">Datos de la sesi&oacute;n</div>
        <table class="session-grid">
            <tr>
                <td>
                    <span class="label">N&uacute;mero de sesi&oacute;n</span>
                    <span class="value"><?php echo $h($datosSesion['numero_sesion'] ?? 'Sin numero'); ?></span>
                </td>
                <td>
                    <span class="label">Tipo de sesi&oacute;n</span>
                    <span class="value"><?php echo $h($datosSesion['tipo_pleno'] ?? 'Sin tipo'); ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Fecha</span>
                    <span class="value"><?php echo $h($formatearFecha($datosSesion['fecha'] ?? null)); ?></span>
                </td>
                <td>
                    <span class="label">Hora</span>
                    <span class="value"><?php echo $h($formatearHora($datosSesion['hora'] ?? null)); ?></span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Lugar</span>
                    <span class="value"><?php echo $h($datosSesion['lugar'] ?? 'Sin lugar'); ?></span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Acuerdos</div>
        <?php foreach ($acuerdos as $indice => $acuerdo): ?>
            <div class="agreement">
                <div class="agreement-title">Acuerdo N&deg; <?php echo (int)$indice + 1; ?></div>
                <div class="agreement-label">Punto:</div>
                <div><?php echo $h($acuerdo['titulo_punto'] ?? ($acuerdo['punto_numero'] ?? 'Sin punto')); ?></div>
                <div class="agreement-label">Descripci&oacute;n:</div>
                <div><?php echo nl2br($h($acuerdo['texto_acuerdo'] ?? '')); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="section">
        <div class="section-title">Certificaci&oacute;n</div>
        <div class="certification-text">
            Se certifica que los acuerdos se&ntilde;alados corresponden a los acuerdos adoptados durante la sesi&oacute;n plenaria indicada.
        </div>
    </div>

    <div class="footer">
        <div class="footer-row"><strong>Usuario generador:</strong> <?php echo $h($usuarioGenerador); ?></div>
        <div class="footer-row"><strong>Fecha generaci&oacute;n:</strong> <?php echo $h($formatearFechaHora($fechaGeneracion)); ?></div>
        <div class="footer-row hash"><strong>Hash de validaci&oacute;n:</strong> <?php echo $h($hashValidacion); ?></div>
        <div class="footer-row"><strong>Versi&oacute;n:</strong> <?php echo (int)$version; ?></div>
        <div class="footer-row"><strong>N&uacute;mero certificado:</strong> <?php echo $h($numeroCertificado); ?></div>
    </div>
</body>
</html>
