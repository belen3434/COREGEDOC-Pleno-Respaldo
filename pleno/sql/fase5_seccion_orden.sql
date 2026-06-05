ALTER TABLE sesion_plenaria_temas
ADD COLUMN seccion_orden VARCHAR(50) NOT NULL DEFAULT 'varios';

UPDATE sesion_plenaria_temas
SET seccion_orden = 'cuenta_gobernador'
WHERE seccion_orden = 'cuenta_intendente';
