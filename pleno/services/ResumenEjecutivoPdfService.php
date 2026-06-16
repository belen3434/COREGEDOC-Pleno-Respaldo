<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class ResumenEjecutivoPdfService
{
    private $templatePath;
    private $storagePath;
    private $projectRoot;

    public function __construct(?string $templatePath = null, ?string $storagePath = null)
    {
        $this->projectRoot = dirname(__DIR__, 2);
        $this->templatePath = $templatePath ?? dirname(__DIR__) . '/views/resumenes/template_resumen_ejecutivo.php';
        $this->storagePath = $storagePath ?? dirname(__DIR__) . '/storage/resumenes';
    }

    public function generar(array $snapshot, array $resumen): array
    {
        if (!is_dir($this->storagePath) && !mkdir($this->storagePath, 0775, true) && !is_dir($this->storagePath)) {
            throw new RuntimeException('No fue posible crear la carpeta de resumenes ejecutivos.');
        }

        $numeroResumen = $this->limpiarNombreArchivo((string)($resumen['numero_resumen'] ?? 'resumen-ejecutivo'));
        $fechaGeneracion = (string)($resumen['fecha_generacion'] ?? date('Y-m-d H:i:s'));
        $timestamp = date('YmdHis', strtotime($fechaGeneracion) ?: time());
        $nombreArchivo = $numeroResumen . '_' . $timestamp . '.pdf';
        $rutaFisica = $this->storagePath . DIRECTORY_SEPARATOR . $nombreArchivo;

        $html = $this->renderizarTemplate($snapshot, $resumen);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', $this->projectRoot);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        if (file_put_contents($rutaFisica, $dompdf->output()) === false) {
            throw new RuntimeException('No fue posible guardar el PDF del resumen ejecutivo.');
        }

        return [
            'nombre_archivo' => $nombreArchivo,
            'path_archivo' => 'pleno/storage/resumenes/' . $nombreArchivo,
            'ruta_fisica' => $rutaFisica,
        ];
    }

    private function renderizarTemplate(array $snapshot, array $resumen): string
    {
        if (!file_exists($this->templatePath)) {
            throw new RuntimeException('No se encontro el template del resumen ejecutivo.');
        }

        ob_start();
        include $this->templatePath;
        $html = ob_get_clean();

        if ($html === false || trim($html) === '') {
            throw new RuntimeException('No fue posible renderizar el resumen ejecutivo.');
        }

        return $html;
    }

    private function limpiarNombreArchivo(string $nombre): string
    {
        $nombre = preg_replace('/[^A-Za-z0-9_-]+/', '-', $nombre) ?? 'resumen-ejecutivo';
        $nombre = trim($nombre, '-_');

        return $nombre !== '' ? $nombre : 'resumen-ejecutivo';
    }
}
