# Plan detallado para agregar roles en modulo Pleno

## Objetivo

Incorporar tres nuevos roles para el modulo `pleno`:

- `Secretaria Pleno`
- `Secretario ejecutivo`
- `Gobernacion`

Sin modificar la logica de los modulos principales existentes (`minutas`, `reuniones`, `comisiones`, `usuarios`, rutas globales del core).

---

## Alcance y criterio de no intervencion del modulo principal

Este plan propone:

1. Crear roles en base de datos.
2. Definir permisos solo dentro de `pleno`.
3. Controlar visualizacion y acceso del modulo `pleno` en forma aislada.

No propone:

1. Cambiar logica de permisos de otros modulos.
2. Alterar reglas de negocio de controladores existentes fuera de `pleno`.
3. Reestructurar autenticacion global.

---

## Matriz de permisos recomendada para Pleno

### Rol: Secretaria Pleno

- Acceso: `index`, `listarSesiones`, `verTabla`, `crearSesion`, `editarSesion`, `precargarTemas`
- Sin acceso: `eliminarSesion` (opcional segun politica interna)

### Rol: Secretario ejecutivo

- Acceso: `index`, `listarSesiones`, `verTabla`, `crearSesion`, `editarSesion`, `precargarTemas`, `eliminarSesion`

### Rol: Gobernacion

- Acceso: `index`, `listarSesiones`, `verTabla`
- Sin acceso: `crearSesion`, `editarSesion`, `eliminarSesion`, `precargarTemas`

---

## Plan de implementacion por fases

## Fase 1: Alta de roles en BD

1. Respaldar tabla de tipos de usuario (`t_tipousuario`).
2. Insertar los tres nuevos registros con IDs libres.
3. Verificar que se reflejen en consultas de usuarios.

Resultado esperado:

- Los nuevos roles quedan disponibles para asignacion en `t_usuario.tipoUsuario_id`.

## Fase 2: Constantes de rol (opcional pero recomendado)

Definir constantes para evitar numeros "quemados" en `pleno`:

- `ROL_SECRETARIA_PLENO`
- `ROL_SECRETARIO_EJECUTIVO`
- `ROL_GOBERNACION`

Nota:

- Si se decide no tocar `app/config/Constants.php`, estas constantes pueden vivir en un archivo local del modulo `pleno` (recomendado para aislar cambios).

## Fase 3: Guard de autorizacion dentro de Pleno

1. Agregar en `pleno/controllers/PlenoController.php` un metodo de autorizacion, por ejemplo:
   - `verificarSesionPleno()`
   - `verificarPermisoPleno($accion)`
2. Crear un mapa de permisos por accion:
   - `index`
   - `listarSesiones`
   - `crearSesion`
   - `editarSesion`
   - `eliminarSesion`
   - `verTabla`
   - `precargarTemas`
3. En cada metodo del controlador, validar rol antes de ejecutar.

Resultado esperado:

- Aunque un usuario conozca la URL, no entra a acciones fuera de su perfil.

## Fase 4: Visibilidad del acceso a Pleno

1. Mantener el enlace del menu solo para roles autorizados a `pleno`.
2. Evitar mostrar el boton a roles no habilitados.

Resultado esperado:

- UX consistente: ve el modulo solo quien realmente puede usarlo.

## Fase 5: Pruebas de acceso por rol

Casos minimos:

1. `Secretaria Pleno`:
   - Debe entrar a listado y formularios permitidos.
   - Debe bloquearse en acciones restringidas.
2. `Secretario ejecutivo`:
   - Debe poder gestionar flujo completo del modulo.
3. `Gobernacion`:
   - Solo lectura.
4. Rol no asignado a pleno:
   - No debe ver enlace ni poder entrar por URL directa.

---

## SQL sugerido para agregar los roles en base de datos

Importante:

- Ajusta los IDs si ya existen.
- Ejecuta primero el bloque de verificacion.

```sql
-- 1) Verificar IDs y nombres ya existentes
SELECT idTipoUsuario, descTipoUsuario
FROM t_tipousuario
ORDER BY idTipoUsuario;

-- 2) Insertar roles nuevos (ejemplo usando IDs 8, 9, 10)
INSERT INTO t_tipousuario (idTipoUsuario, descTipoUsuario)
VALUES
  (8, 'Secretaria Pleno'),
  (9, 'Secretario ejecutivo'),
  (10, 'Gobernacion');

-- 3) Confirmar insercion
SELECT idTipoUsuario, descTipoUsuario
FROM t_tipousuario
WHERE idTipoUsuario IN (8, 9, 10);
```

Si tu tabla `t_tipousuario` usa autoincrement y no requiere ID manual:

```sql
INSERT INTO t_tipousuario (descTipoUsuario)
VALUES
  ('Secretaria Pleno'),
  ('Secretario ejecutivo'),
  ('Gobernacion');
```

---

## Como asignar estos roles a usuarios

Ejemplo:

```sql
-- Asignar "Secretaria Pleno" (id 8) a un usuario puntual
UPDATE t_usuario
SET tipoUsuario_id = 8
WHERE idUsuario = 123;
```

Validacion:

```sql
SELECT idUsuario, pNombre, aPaterno, tipoUsuario_id
FROM t_usuario
WHERE idUsuario = 123;
```

---

## Riesgos y controles

1. Riesgo: IDs de rol duplicados.
   - Control: verificar IDs antes de insertar.
2. Riesgo: usuario ve boton pero no tiene permiso real.
   - Control: validar en backend (controlador), no solo en vista.
3. Riesgo: contaminar permisos de modulos existentes.
   - Control: encapsular cambios en carpeta `pleno`.

---

## Cambios al modulo principal

Estado de este entregable:

- En este paso solo se creo este archivo de plan.
- No se modifico codigo del modulo principal.
- No se modificaron rutas globales ni controladores existentes.

