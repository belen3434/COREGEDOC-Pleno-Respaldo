CREATE TABLE IF NOT EXISTS sesiones_plenarias (
    id_sesion INT AUTO_INCREMENT PRIMARY KEY,
    tipo_pleno VARCHAR(30) NOT NULL,
    numero_sesion VARCHAR(50) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    lugar VARCHAR(150) NULL,
    estado VARCHAR(30) NOT NULL,
    observaciones TEXT NULL,
    usuario_creador INT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
