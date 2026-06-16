<?php

class PlenoController
{
    private const ESTADO_SESION_POR_DEFECTO = 'programada';
    private const LUGAR_SALON_PLENARIO = 'Salón Plenario 3';
    private $sesiones;
    private $temas;
    private $auth;
    private $rolesGestion = [6, 20];

    public function __construct(SesionPlenaria $sesiones, TemaPleno $temas, array $auth)
    {
        $this->sesiones = $sesiones;
        $this->temas = $temas;
        $this->auth = $auth;
    }

    public function puedeGestionar(): bool
    {
        return in_array((int)($this->auth['tipoUsuarioId'] ?? 0), $this->rolesGestion, true);
    }

    public function listarSesiones(): array
    {
        return $this->sesiones->listar();
    }

    public function obtenerSesion(int $id): ?array
    {
        return $this->sesiones->obtenerPorId($id);
    }

    public function obtenerUltimaSesionVigente(): ?array
    {
        return $this->sesiones->obtenerUltimaVigente();
    }

    public function obtenerSesionVigenteHoy(): ?array
    {
        return $this->sesiones->obtenerSesionVigenteHoy();
    }

    public function numeroSesionSugerido(): string
    {
        return $this->sesiones->generarNumeroSesion();
    }

    public function listarComisionesActivas(): array
    {
        return $this->sesiones->listarComisionesActivas();
    }

    public function listarTemasPorComision(int $idComision): array
    {
        if ($idComision <= 0) {
            return [];
        }

        return $this->sesiones->listarTemasPorComision($idComision);
    }

    public function listarPuntosSesion(int $idSesion): array
    {
        if ($idSesion <= 0) {
            return [];
        }

        return $this->temas->listarPuntosSesion($idSesion);
    }

    public function listarTemasComisionSesion(int $idSesion): array
    {
        if ($idSesion <= 0) {
            return [];
        }

        return $this->temas->listarTemasComisionSesion($idSesion);
    }

    public function listarPuntosVariosSesion(int $idSesion): array
    {
        if ($idSesion <= 0) {
            return [];
        }

        return $this->temas->listarPuntosVariosSesion($idSesion);
    }

    public function contarConsejerosYGobernador(): int
    {
        return $this->sesiones->contarConsejerosYGobernador();
    }

    public function manejarAccion(?string $action): void
    {
        if ($action === null || $action === '') {
            return;
        }

        switch ($action) {
            case 'guardar_sesion':
                $this->guardarSesion();
                break;
            case 'actualizar_sesion':
                $this->actualizarSesion((int)($_GET['id'] ?? 0));
                break;
            case 'eliminar_sesion':
                $this->eliminarSesion((int)($_GET['id'] ?? 0));
                break;
            case 'actualizar_orden_tabla':
                $this->actualizarOrdenTabla();
                break;
            default:
                return;
        }
    }

    private function guardarSesion(): void
    {
        if (!$this->asegurarPermisoGestion()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir('index.php?vista=crear_sesion');
        }

        $data = $this->normalizarSesion($_POST);
        $errores = $this->validarSesion($data);

        if (!empty($errores)) {
            $this->flash('danger', isset($errores['lugar']) ? 'Debe ingresar el nombre de la dependencia.' : 'Debe completar los campos obligatorios.');
            $this->redirigir('index.php?vista=crear_sesion');
        }

        $data['usuario_creador'] = isset($_SESSION['idUsuario']) ? (int)$_SESSION['idUsuario'] : null;

        $idSesion = $this->sesiones->crear($data);
        $puntos = $this->normalizarPuntos($_POST['puntos_resumen_json'] ?? '[]');
        $this->temas->guardarPuntosSesion($idSesion, $puntos);

        $this->flash('success', 'Sesión creada correctamente.');
        $this->redirigir('index.php?vista=sesiones');
    }

    private function actualizarSesion(int $id): void
    {
        if (!$this->asegurarPermisoGestion()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $id <= 0) {
            $this->flash('danger', 'Debe completar los campos obligatorios.');
            $this->redirigir('index.php?vista=sesiones');
        }

        $sesionActual = $this->sesiones->obtenerPorId($id);

        if ($sesionActual === null) {
            $this->flash('danger', 'La sesión solicitada no existe.');
            $this->redirigir('index.php?vista=sesiones');
        }

        $data = $this->normalizarSesion($_POST, (string)($sesionActual['estado'] ?? ''));
        $data['numero_sesion'] = $sesionActual['numero_sesion'];
        $errores = $this->validarSesion($data);

        if (!empty($errores)) {
            $this->flash('danger', isset($errores['lugar']) ? 'Debe ingresar el nombre de la dependencia.' : 'Debe completar los campos obligatorios.');
            $this->redirigir('index.php?vista=editar_sesion&id=' . $id);
        }

        $this->sesiones->actualizar($id, $data);
        $puntos = $this->normalizarPuntos($_POST['puntos_resumen_json'] ?? '[]');
        $this->temas->guardarPuntosSesion($id, $puntos);
        $puntosEliminados = $this->normalizarPuntosEliminados($_POST['puntos_eliminados_json'] ?? '[]');
        foreach ($puntosEliminados as $idPuntoEliminado) {
            $this->temas->eliminarLogicoPunto($idPuntoEliminado, $id);
        }

        $this->flash('success', 'Sesión actualizada correctamente.');
        $this->redirigir('index.php?vista=sesiones');
    }

    private function eliminarSesion(int $id): void
    {
        if (!$this->asegurarPermisoGestion()) {
            return;
        }

        if ($id <= 0) {
            $this->flash('danger', 'La sesión solicitada no existe.');
            $this->redirigir('index.php?vista=sesiones');
        }

        $this->sesiones->eliminar($id);
        $this->flash('success', 'Sesión eliminada correctamente.');
        $this->redirigir('index.php?vista=sesiones');
    }

    private function actualizarOrdenTabla(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJson([
                'ok' => false,
                'error' => 'Método no permitido.',
            ], 405);
        }

        $idSesion = (int)($_GET['id_sesion'] ?? 0);
        $rawBody = file_get_contents('php://input');
        $ordenPuntos = json_decode($rawBody, true);

        if ($idSesion <= 0) {
            $this->responderJson([
                'ok' => false,
                'error' => 'Debe indicar una sesión válida.',
            ], 400);
        }

        if (!is_array($ordenPuntos) || empty($ordenPuntos)) {
            $this->responderJson([
                'ok' => false,
                'error' => 'Debe enviar un orden válido.',
            ], 400);
        }

        $actualizado = $this->temas->actualizarOrdenPuntosSesion($idSesion, $ordenPuntos);

        if (!$actualizado) {
            $this->responderJson([
                'ok' => false,
                'error' => 'No fue posible actualizar el orden de la tabla.',
            ], 400);
        }

        $this->responderJson([
            'ok' => true,
        ]);
    }

    private function normalizarSesion(array $input, ?string $estadoActual = null): array
    {
        $numeroSesion = trim((string)($input['numero_sesion'] ?? ''));
        $estadoPersistido = trim((string)$estadoActual);
        $lugar = $this->normalizarLugarSesion($input);
        $tipoPleno = $this->normalizarTipoPleno($input['tipo_pleno'] ?? '');

        if ($estadoPersistido === '') {
            $estadoPersistido = self::ESTADO_SESION_POR_DEFECTO;
        }

        return [
            'tipo_pleno' => $tipoPleno,
            'numero_sesion' => $numeroSesion !== '' ? $numeroSesion : $this->numeroSesionSugerido(),
            'fecha' => trim((string)($input['fecha'] ?? '')),
            'hora' => trim((string)($input['hora'] ?? '')),
            'lugar' => $lugar,
            'estado' => $estadoPersistido,
            'aprobacion_actas' => trim((string)($input['aprobacion_actas'] ?? '')),
            'observaciones' => trim((string)($input['observaciones'] ?? '')),
        ];
    }

    private function normalizarTipoPleno($tipo): string
    {
        $tipo = trim((string)$tipo);
        $tipoNormalizado = function_exists('mb_strtolower')
            ? mb_strtolower($tipo, 'UTF-8')
            : strtolower($tipo);

        if ($tipoNormalizado === 'normal' || $tipoNormalizado === 'ordinario') {
            return 'Ordinario';
        }

        if ($tipoNormalizado === 'extraordinario') {
            return 'Extraordinario';
        }

        return $tipo;
    }

    private function normalizarLugarSesion(array $input): string
    {
        $opcionLugar = trim((string)($input['lugar_opcion'] ?? 'salon_plenario_3'));
        $lugar = $opcionLugar === 'otra'
            ? trim((string)($input['lugar_otro'] ?? ''))
            : self::LUGAR_SALON_PLENARIO;

        if (function_exists('mb_substr')) {
            return mb_substr($lugar, 0, 150, 'UTF-8');
        }

        return substr($lugar, 0, 150);
    }

    private function normalizarPuntos($json): array
    {
        if (!is_string($json) || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $puntos = [];
        foreach ($decoded as $punto) {
            if (!is_array($punto)) {
                continue;
            }

            $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));
            $idComision = isset($punto['id_comision']) && $punto['id_comision'] !== '' ? (int)$punto['id_comision'] : null;
            $idTema = isset($punto['id_tema']) && $punto['id_tema'] !== '' ? (int)$punto['id_tema'] : null;
            $nombreImprevista = trim((string)($punto['nombre_imprevista'] ?? ''));
            $observacionImprevista = trim((string)($punto['observacion_imprevista'] ?? ''));
            $tituloPunto = trim((string)($punto['titulo_punto'] ?? ''));
            $descripcionPunto = trim((string)($punto['descripcion_punto'] ?? ''));
            $seccionOrden = trim((string)($punto['seccion_orden'] ?? ''));
            if ($seccionOrden === '') {
                $seccionOrden = $tipoPunto === 'TABLA' ? 'varios' : 'cuenta_comisiones';
            }
            $seccionesPermitidas = ['cuenta_gobernador', 'cuenta_comisiones', 'varios'];
            $seccionOrden = $seccionOrden === 'cuenta_intendente' ? 'cuenta_gobernador' : $seccionOrden;
            $seccionOrden = in_array($seccionOrden, $seccionesPermitidas, true) ? $seccionOrden : 'varios';

            if ($tipoPunto === 'IMPREVISTA' && $nombreImprevista === '') {
                continue;
            }

            if ($tipoPunto === 'TABLA' && $tituloPunto === '') {
                continue;
            }

            if (!in_array($tipoPunto, ['IMPREVISTA', 'TABLA'], true) && (($idComision ?? 0) <= 0)) {
                continue;
            }

            $puntos[] = [
                'id' => (int)($punto['id'] ?? 0),
                'id_comision' => $idComision,
                'id_tema' => $idTema,
                'tipo_punto' => $tipoPunto !== '' ? $tipoPunto : 'COMISION',
                'nombre_imprevista' => $nombreImprevista,
                'observacion_imprevista' => $observacionImprevista,
                'titulo_punto' => $tituloPunto,
                'descripcion_punto' => $descripcionPunto,
                'seccion_orden' => $seccionOrden,
            ];
        }

        return $puntos;
    }

    private function normalizarPuntosEliminados($json): array
    {
        if (!is_string($json) || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $ids = [];
        foreach ($decoded as $id) {
            $idPunto = (int)$id;
            if ($idPunto > 0) {
                $ids[] = $idPunto;
            }
        }

        return array_values(array_unique($ids));
    }

    private function validarSesion(array $data): array
    {
        $errores = [];

        foreach (['tipo_pleno', 'numero_sesion', 'fecha', 'hora', 'lugar'] as $campo) {
            if (($data[$campo] ?? '') === '') {
                $errores[$campo] = true;
            }
        }

        return $errores;
    }

    private function asegurarPermisoGestion(): bool
    {
        if ($this->puedeGestionar()) {
            return true;
        }

        $this->flash('danger', 'No tiene permisos para realizar esta acción.');
        $this->redirigir('index.php?vista=sesiones');
        return false;
    }

    public function consumirFlash(): ?array
    {
        $flash = $_SESSION['pleno_flash'] ?? null;
        unset($_SESSION['pleno_flash']);

        return is_array($flash) ? $flash : null;
    }

    private function flash(string $tipo, string $mensaje): void
    {
        $_SESSION['pleno_flash'] = [
            'tipo' => $tipo,
            'mensaje' => $mensaje,
        ];
    }

    private function redirigir(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    private function responderJson(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit();
    }
}
