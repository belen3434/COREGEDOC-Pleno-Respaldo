<?php

class PlenoController
{
    private $sesiones;
    private $auth;
    private $rolesGestion = [6, 20];

    public function __construct(SesionPlenaria $sesiones, array $auth)
    {
        $this->sesiones = $sesiones;
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

    public function numeroSesionSugerido(): string
    {
        return $this->sesiones->generarNumeroSesion();
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
            $this->flash('danger', 'Debe completar los campos obligatorios.');
            $this->redirigir('index.php?vista=crear_sesion');
        }

        $data['usuario_creador'] = isset($_SESSION['idUsuario']) ? (int)$_SESSION['idUsuario'] : null;
        $this->sesiones->crear($data);
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

        $data = $this->normalizarSesion($_POST);
        $data['numero_sesion'] = $sesionActual['numero_sesion'];
        $errores = $this->validarSesion($data);

        if (!empty($errores)) {
            $this->flash('danger', 'Debe completar los campos obligatorios.');
            $this->redirigir('index.php?vista=editar_sesion&id=' . $id);
        }

        $this->sesiones->actualizar($id, $data);
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

    private function normalizarSesion(array $input): array
    {
        $numeroSesion = trim((string)($input['numero_sesion'] ?? ''));

        return [
            'tipo_pleno' => trim((string)($input['tipo_pleno'] ?? '')),
            'numero_sesion' => $numeroSesion !== '' ? $numeroSesion : $this->numeroSesionSugerido(),
            'fecha' => trim((string)($input['fecha'] ?? '')),
            'hora' => trim((string)($input['hora'] ?? '')),
            'estado' => trim((string)($input['estado'] ?? '')),
            'observaciones' => trim((string)($input['observaciones'] ?? '')),
        ];
    }

    private function validarSesion(array $data): array
    {
        $errores = [];

        foreach (['tipo_pleno', 'numero_sesion', 'fecha', 'hora', 'estado'] as $campo) {
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
}
