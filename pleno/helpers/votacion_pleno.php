<?php

use App\Config\Database;

if (!function_exists('plenoVotacionConn')) {
    function plenoVotacionConn()
    {
        $database = new Database();
        return $database->getConnection();
    }
}

if (!function_exists('plenoAsegurarTablaVotacion')) {
    function plenoAsegurarTablaVotacion($conn): void
    {
        $conn->exec(
            "CREATE TABLE IF NOT EXISTS pleno_votacion_punto (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_sesion INT NOT NULL,
                punto_numero VARCHAR(20) NOT NULL,
                estado_votacion VARCHAR(30) NOT NULL DEFAULT 'sin_votacion',
                fecha_inicio_votacion DATETIME NULL,
                fecha_cierre_votacion DATETIME NULL,
                fecha_actualizacion DATETIME NULL,
                UNIQUE KEY uq_pleno_votacion_punto (id_sesion, punto_numero)
            )"
        );
    }
}

if (!function_exists('plenoPuntoPermiteVotacion')) {
    function plenoPuntoPermiteVotacion(string $puntoNumero): bool
    {
        return $puntoNumero === '1' || preg_match('/^3\.\d+$/', $puntoNumero) === 1;
    }
}

if (!function_exists('plenoObtenerEstadoVotacion')) {
    function plenoObtenerEstadoVotacion($conn, int $idSesion, string $puntoNumero): string
    {
        plenoAsegurarTablaVotacion($conn);

        $stmt = $conn->prepare(
            'SELECT estado_votacion
             FROM pleno_votacion_punto
             WHERE id_sesion = :id_sesion
               AND punto_numero = :punto_numero
             LIMIT 1'
        );
        $stmt->execute([
            ':id_sesion' => $idSesion,
            ':punto_numero' => $puntoNumero,
        ]);
        $row = $stmt->fetch();

        return is_array($row) && !empty($row['estado_votacion'])
            ? (string)$row['estado_votacion']
            : 'sin_votacion';
    }
}
