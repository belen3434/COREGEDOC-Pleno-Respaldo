<?php

use App\Config\Database;

if (!function_exists('plenoRequireAuthorizedUser')) {
    function plenoRequireAuthorizedUser(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['idUsuario'])) {
            header('Location: ../index.php?action=login');
            exit();
        }

        $allowedTipoUsuario = [1, 6, 20, 21, 22];
        $tipoUsuarioId = (int)($_SESSION['tipoUsuario_id'] ?? 0);

        if (!in_array($tipoUsuarioId, $allowedTipoUsuario, true)) {
            http_response_code(403);
            return [
                'authorized' => false,
                'tipoUsuarioId' => $tipoUsuarioId,
                'perfilNombre' => 'No autorizado',
                'allowedTipoUsuario' => $allowedTipoUsuario,
            ];
        }

        $perfilNombre = plenoGetTipoUsuarioDescripcion($tipoUsuarioId);

        return [
            'authorized' => true,
            'tipoUsuarioId' => $tipoUsuarioId,
            'perfilNombre' => $perfilNombre,
            'allowedTipoUsuario' => $allowedTipoUsuario,
        ];
    }
}

if (!function_exists('plenoGetTipoUsuarioDescripcion')) {
    function plenoGetTipoUsuarioDescripcion(int $tipoUsuarioId): string
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $stmt = $conn->prepare(
                'SELECT descTipoUsuario FROM t_tipousuario WHERE idTipoUsuario = :id LIMIT 1'
            );
            $stmt->execute([':id' => $tipoUsuarioId]);
            $row = $stmt->fetch();

            if (is_array($row) && !empty($row['descTipoUsuario'])) {
                return (string)$row['descTipoUsuario'];
            }
        } catch (\Throwable $e) {
            // Fallback silencioso para no romper acceso al modulo.
        }

        return 'Tipo de usuario #' . $tipoUsuarioId;
    }
}

if (!function_exists('plenoPuedeGenerarCertificados')) {
    function plenoPuedeGenerarCertificados($tipoUsuarioId): bool
    {
        return in_array((int)$tipoUsuarioId, [6, 20], true);
    }
}
