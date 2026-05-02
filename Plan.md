# Plan de trabajo por fases (Historias de Usuario)

Base: `Backlog.csv`  
Contexto actual: la **Fase 1 de roles en base de datos ya fue ejecutada** (roles de pleno desde ID 20 en adelante).

## Estado inicial (ya completado)

- Roles cargados en BD:
  - `20`: Secretaria Pleno
  - `21`: Secretario ejecutivo
  - `22`: Gobernacion
- Resultado: ya es posible asignar estos perfiles a usuarios en `t_usuario.tipoUsuario_id`.

---

## Fase 2: Acceso y autorizacion al modulo pleno

Objetivo: habilitar entrada segura al modulo `pleno` segun perfil.

Historias incluidas:

- `HU-01` Acceso Secretaria de Pleno (Must)
- `HU-02` Acceso Consejero Regional (Must)
- `HU-03` Acceso Gobernador (Should)
- `HU-04` Acceso Secretario Ejecutivo (Must)
- `HU-05` Cerrar sesion seguro (Should)

Entregables:

1. Guard de permisos en `pleno` por accion (lectura vs gestion).
2. Visibilidad del acceso a `pleno` segun roles permitidos.
3. Redireccion o bloqueo controlado para accesos no autorizados.
4. Uso de cierre de sesion existente del sistema (sin duplicar autenticacion).

Criterios de aceptacion:

1. Usuarios con rol permitido acceden a `pleno`.
2. Usuarios sin permiso no acceden por URL directa.
3. Cierre de sesion sigue funcionando de forma unificada.

---

## Fase 3: Gestion de sesiones plenarias (CRUD base)

Objetivo: habilitar administracion de sesiones plenarias para perfiles de gestion.

Historias incluidas:

- `HU-06` Crear sesion (Must)
- `HU-07` Modificar sesion (Must)
- `HU-08` Eliminar sesion (Should)
- `HU-09` Listar sesiones (Must)

Entregables:

1. Modelo y consultas para `SesionPlenaria` (crear, editar, listar, eliminar).
2. Formularios funcionales en vistas `crear_sesion`, `editar_sesion`, `sesiones`.
3. Validaciones de campos obligatorios.
4. Mensajes de exito/error en operaciones.

Criterios de aceptacion:

1. Secretaria Pleno crea, edita y lista sesiones.
2. Eliminacion disponible segun perfil y reglas definidas.
3. Acciones registran cambios esperados en BD de prueba.

---

## Fase 4: Gestion de temas y precarga desde comisiones

Objetivo: integrar temas de comision en la sesion plenaria y permitir su administracion.

Historias incluidas:

- `HU-10` Precargar temas desde comisiones (Must)
- `HU-11` Ver temas asociados a sesion (Must)
- `HU-12` Eliminar tema asociado (Should)
- `HU-13` Modificar asociacion/orden de tema (Could)

Entregables:

1. Estructura de datos para asociar `sesion` <-> `temas`.
2. Flujo de precarga de temas de comisiones activas.
3. Vista de temas asociados por sesion.
4. Reordenamiento/edicion basica de temas (si aplica por negocio).

Criterios de aceptacion:

1. Temas de comision se incorporan a una sesion de pleno.
2. Secretaria Pleno puede revisar y ajustar la carga.
3. Gobernacion/Consejero/Secretario Ejecutivo mantienen acceso segun permisos.

---

## Fase 5: Tabla del pleno (orden del dia)

Objetivo: construir y visualizar la tabla oficial de la sesion.

Historias incluidas:

- `HU-14` Gestionar puntos de tabla (Must)
- `HU-15` Visualizar tabla para perfiles de lectura (Must)

Entregables:

1. Editor de tabla (puntos fijos, comisiones, varios).
2. Vista de tabla para consulta general.
3. Orden persistente de puntos en BD.

Criterios de aceptacion:

1. Secretaria Pleno define y actualiza la tabla.
2. Consejero, Gobernacion y Secretario Ejecutivo la visualizan correctamente.
3. Se respeta el orden del dia guardado.

---

## Priorizacion sugerida de ejecucion

1. Fase 2 (Acceso y autorizacion)
2. Fase 3 (CRUD sesiones)
3. Fase 4 (Temas y precarga)
4. Fase 5 (Tabla del pleno)

Razon:

- Primero seguridad y acceso, luego datos base, despues integraciones y finalmente salida consolidada (tabla).

---

## Notas de implementacion

1. Mantener encapsulada la logica en carpeta `pleno` para minimizar impacto.
2. Reutilizar autenticacion de sesion actual (`$_SESSION['tipoUsuario_id']`).
3. Evitar cambios en rutas globales salvo integracion minima ya aprobada.
4. Agregar pruebas manuales por rol en cada fase.

---

## Cambios al modulo principal

En este paso solo se creo el archivo `Plan.md`.  
No se modifico codigo del modulo principal.

