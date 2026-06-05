CREATE TABLE IF NOT EXISTS sesion_plenaria_temas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_sesion INT NOT NULL,
    id_comision INT NOT NULL,
    id_tema INT NULL,
    tipo_punto VARCHAR(30) NOT NULL DEFAULT 'COMISION',
    seccion_orden VARCHAR(50) NOT NULL DEFAULT 'varios',
    orden INT NOT NULL DEFAULT 1,
    vigente TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NULL,
    CONSTRAINT fk_spt_sesion FOREIGN KEY (id_sesion) REFERENCES sesiones_plenarias(id_sesion) ON DELETE CASCADE,
    CONSTRAINT fk_spt_comision FOREIGN KEY (id_comision) REFERENCES t_comision(idComision),
    CONSTRAINT fk_spt_tema FOREIGN KEY (id_tema) REFERENCES t_tema(idTema)
);
