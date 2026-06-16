CREATE TABLE IF NOT EXISTS pleno_certificados_acuerdos (
    id_certificado INT AUTO_INCREMENT PRIMARY KEY,
    id_sesion INT NOT NULL,
    version INT NOT NULL,
    numero_certificado VARCHAR(100) NOT NULL,
    nombre_archivo VARCHAR(255) NULL,
    path_archivo VARCHAR(500) NULL,
    hash_validacion CHAR(64) NOT NULL,
    total_acuerdos INT NOT NULL DEFAULT 0,
    usuario_generador INT NULL,
    fecha_generacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(30) NOT NULL DEFAULT 'vigente',
    fecha_eliminacion DATETIME NULL,
    usuario_actualizador INT NULL,
    fecha_actualizacion DATETIME NULL,
    snapshot_json LONGTEXT NULL,
    UNIQUE KEY uk_cert_sesion_version (id_sesion, version),
    KEY idx_cert_id_sesion (id_sesion),
    CONSTRAINT fk_cert_acuerdos_sesion
        FOREIGN KEY (id_sesion) REFERENCES sesiones_plenarias(id_sesion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
