-- Fase 1: Alta de roles para modulo pleno
-- Proyecto: COREGEDOC
-- Base: corevota (segun app/config/database.ini)

-- Verificacion previa de IDs objetivo
SELECT idTipoUsuario, descTipoUsuario
FROM t_tipousuario
WHERE idTipoUsuario IN (20, 21, 22)
ORDER BY idTipoUsuario;

-- Insercion segura (solo si no existe el ID)
INSERT INTO t_tipousuario (idTipoUsuario, descTipoUsuario)
SELECT 20, 'Secretaria Pleno'
WHERE NOT EXISTS (
    SELECT 1 FROM t_tipousuario WHERE idTipoUsuario = 20
);

INSERT INTO t_tipousuario (idTipoUsuario, descTipoUsuario)
SELECT 21, 'Secretario ejecutivo'
WHERE NOT EXISTS (
    SELECT 1 FROM t_tipousuario WHERE idTipoUsuario = 21
);

INSERT INTO t_tipousuario (idTipoUsuario, descTipoUsuario)
SELECT 22, 'Gobernacion'
WHERE NOT EXISTS (
    SELECT 1 FROM t_tipousuario WHERE idTipoUsuario = 22
);

-- Verificacion final
SELECT idTipoUsuario, descTipoUsuario
FROM t_tipousuario
WHERE idTipoUsuario IN (20, 21, 22)
ORDER BY idTipoUsuario;
