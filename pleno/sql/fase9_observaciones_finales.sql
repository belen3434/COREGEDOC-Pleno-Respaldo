-- Ejecutar solo si las columnas no existen.
ALTER TABLE sesiones_plenarias
ADD COLUMN observaciones_finales TEXT NULL,
ADD COLUMN observaciones_finales_usuario INT NULL,
ADD COLUMN observaciones_finales_fecha DATETIME NULL;
