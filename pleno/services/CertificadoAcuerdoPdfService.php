<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class CertificadoAcuerdoPdfService
{
    private $templatePath;
    private $storagePath;
    private $projectRoot;

    public function __construct(?string $templatePath = null, ?string $storagePath = null)
    {
        $this->projectRoot = dirname(__DIR__, 2);
        $this->templatePath = $templatePath ?? dirname(__DIR__) . '/views/certificados/template_certificado.php';
        $this->storagePath = $storagePath ?? dirname(__DIR__) . '/storage/certificados';
    }

    public function generar(array $snapshot, array $certificado): array
    {
        error_log('[CertificadoPDF] storage_path=' . $this->storagePath . ' exists=' . (is_dir($this->storagePath) ? 'true' : 'false') . ' writable=' . (is_writable($this->storagePath) ? 'true' : 'false'));

        if (!is_dir($this->storagePath) && !mkdir($this->storagePath, 0775, true) && !is_dir($this->storagePath)) {
            error_log('[CertificadoPDF] mkdir_failed storage_path=' . $this->storagePath);
            throw new RuntimeException('No fue posible crear la carpeta de certificados.');
        }

        $numeroCertificado = $this->limpiarNombreArchivo((string)($certificado['numero_certificado'] ?? 'certificado'));
        $fechaGeneracion = (string)($certificado['fecha_generacion'] ?? date('Y-m-d H:i:s'));
        $timestamp = date('YmdHis', strtotime($fechaGeneracion) ?: time());
        $nombreArchivo = $numeroCertificado . '_' . $timestamp . '.pdf';
        $rutaFisica = $this->storagePath . DIRECTORY_SEPARATOR . $nombreArchivo;

        error_log('[CertificadoPDF] nombre_archivo=' . $nombreArchivo);
        error_log('[CertificadoPDF] ruta_final_pdf=' . $rutaFisica);

        $html = $this->renderizarTemplate($snapshot, $certificado);
        error_log('[CertificadoPDF] html_length=' . strlen($html));

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', $this->projectRoot);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('letter', 'portrait');
        try {
            $dompdf->render();
            error_log('[CertificadoPDF] dompdf_render_ok');
        } catch (Throwable $e) {
            error_log('[CertificadoPDF] dompdf_render_exception=' . $e->getMessage() . ' file=' . $e->getFile() . ' line=' . $e->getLine());
            throw $e;
        }

        $contenido = $dompdf->output();
        error_log('[CertificadoPDF] dompdf_output_length=' . strlen($contenido));

        $resultadoEscritura = file_put_contents($rutaFisica, $contenido);
        error_log('[CertificadoPDF] file_put_contents_result=' . var_export($resultadoEscritura, true));
        error_log('[CertificadoPDF] file_exists_after_write=' . (file_exists($rutaFisica) ? 'true' : 'false'));

        if ($resultadoEscritura === false) {
            throw new RuntimeException('No fue posible guardar el PDF del certificado.');
        }

        return [
            'nombre_archivo' => $nombreArchivo,
            'path_archivo' => 'pleno/storage/certificados/' . $nombreArchivo,
            'ruta_fisica' => $rutaFisica,
        ];
    }

    private function renderizarTemplate(array $snapshot, array $certificado): string
    {
        if (!file_exists($this->templatePath)) {
            throw new RuntimeException('No se encontro el template del certificado.');
        }

        $logoPath = $this->projectRoot . '/public/img/logoCore1.png';
        $logoDataUri = is_file($logoPath) ? $this->crearDataUri($logoPath) : '';

        ob_start();
        include $this->templatePath;
        $html = ob_get_clean();

        if ($html === false || trim($html) === '') {
            throw new RuntimeException('No fue posible renderizar el certificado.');
        }

        return $html;
    }

    private function crearDataUri(string $path): string
    {
        $contenido = file_get_contents($path);
        if ($contenido === false) {
            return '';
        }

        $mime = 'image/png';
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === 'jpg' || $extension === 'jpeg') {
            $mime = 'image/jpeg';
        }

        return 'data:' . $mime . ';base64,' . base64_encode($contenido);
    }

    private function limpiarNombreArchivo(string $nombre): string
    {
        $nombre = preg_replace('/[^A-Za-z0-9_-]+/', '-', $nombre) ?? 'certificado';
        $nombre = trim($nombre, '-_');

        return $nombre !== '' ? $nombre : 'certificado';
    }
}
