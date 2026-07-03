# Resumen 17 mayo belen

## Objetivo trabajado
Habilitar el acceso al módulo **Pleno** para el rol **Consejero Regional (tipoUsuario_id = 1)**, manteniendo permisos de solo visualización y sin tocar login, BD ni CRUD base.

## Cambios realizados

### 1) Menú lateral general: mostrar "Pleno" al rol 1
**Archivo:** `app/views/partials/sidebar.php`

- Se detectó que el enlace a **Pleno** estaba dentro de un bloque de administración que no incluía al rol 1.
- Se creó una condición independiente para visibilidad del acceso a Pleno:
  - Roles con acceso al enlace: `[1, 6, 20, 21, 22]`.
- Resultado:
  - Consejero ahora ve la opción **Pleno** en el menú lateral general.
  - Se mantiene acceso para Administrador (6), Secretaría Pleno (20), Secretario Ejecutivo (21), Gobernación (22).

---

### 2) Sidebar interno del módulo Pleno por rol
**Archivo:** `pleno/views/partials/sidebar_pleno.php`

- Se ajustó el render del menú interno con condición PHP por rol (no CSS).
- Lógica implementada:
  - Si rol es `6` o `20`: menú completo con
    - Configuración
    - Comisiones
    - Pleno en Vivo
    - Votación
    - Resumen
  - Para rol `1` (y perfiles visuales): solo
    - Pleno en Vivo
    - Votación
    - Resumen
- Resultado:
  - Roles visuales no ven `Configuración` ni `Comisiones`.

---

### 3) Bloqueo por URL directa dentro de /pleno
**Archivo:** `pleno/index.php`

- Se agregó control de vistas para roles de solo visualización: `[1, 21, 22]`.
- Vistas permitidas para visualización:
  - `tabla`
  - `pleno_vivo`
  - `votacion`
  - `resumen`
- Si rol visual intenta abrir una vista de gestión (`index`, `crear_sesion`, `sesiones`, `editar_sesion`, `configuracion`, `comisiones`, etc.), se redirige a:
  - `index.php?vista=tabla`

---

### 4) Portada del módulo Pleno: ocultar accesos de gestión a rol visual
**Archivo:** `pleno/views/index.php`

- Se condicionó la visualización de tarjetas de gestión con `pleno_puede_gestionar`.
- Para roles sin gestión (como rol 1):
  - No se muestran tarjetas de **Crear sesión** ni **Sesiones plenarias**.
  - Se mantiene tarjeta **Tabla del Día** para consulta.

## Estado de permisos después de cambios

### Rol 1 (Consejero Regional)
Puede:
- Ver opción **Pleno** en sidebar general.
- Entrar a `/pleno`.
- Ver `Tabla del Día`, `Pleno en Vivo`, `Votación`, `Resumen`.

No puede:
- Ver `Configuración` / `Comisiones` en menú interno.
- Acceder a vistas de gestión por URL directa (`crear_sesion`, `sesiones`, `editar_sesion`, etc.).
- Gestionar sesiones (crear/editar/eliminar).

### Rol 6 y 20
- Mantienen menú completo de Pleno y capacidades de gestión.

### Rol 21 y 22
- Quedan en perfil de visualización para navegación interna según reglas aplicadas en `/pleno`.

## Archivos modificados en esta etapa
- `app/views/partials/sidebar.php`
- `pleno/index.php`
- `pleno/views/partials/sidebar_pleno.php`
- `pleno/views/index.php`

## Verificación técnica ejecutada
Se validó sintaxis PHP sin errores con `php -l` en los 4 archivos modificados.
