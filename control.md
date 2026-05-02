# Control de cambios - Modulo Pleno

## Objetivo

Crear una base limpia y separada para el nuevo modulo de sesiones plenarias llamado `pleno`, tocando lo menos posible el codigo original de COREGEDOC.

## Estructura creada

Se creo la carpeta `pleno` en la raiz del proyecto con una estructura MVC simple:

```text
pleno/
  controllers/
    PlenoController.php
  models/
    Pleno.php
    SesionPlenaria.php
    TemaPleno.php
  views/
    index.php
    sesiones.php
    crear_sesion.php
    editar_sesion.php
    tabla.php
  assets/
    css/
      pleno.css
    js/
      pleno.js
  index.php
  README.md
```

## Archivos principales del modulo

- `pleno/controllers/PlenoController.php`: controlador base con metodos simples:
  - `index()`
  - `listarSesiones()`
  - `crearSesion()`
  - `editarSesion()`
  - `eliminarSesion()`
  - `verTabla()`
  - `precargarTemas()`

- `pleno/models/Pleno.php`: modelo base del modulo.
- `pleno/models/SesionPlenaria.php`: modelo base para sesiones plenarias.
- `pleno/models/TemaPleno.php`: modelo base para temas del pleno.

- `pleno/views/index.php`: vista con el titulo "Modulo de Pleno".
- `pleno/views/sesiones.php`: vista con el titulo "Sesiones Plenarias".
- `pleno/views/crear_sesion.php`: vista con el titulo "Crear Sesion Plenaria".
- `pleno/views/editar_sesion.php`: vista con el titulo "Editar Sesion Plenaria".
- `pleno/views/tabla.php`: vista con el titulo "Tabla del Pleno".

- `pleno/assets/css/pleno.css`: archivo CSS base con comentario inicial.
- `pleno/assets/js/pleno.js`: archivo JS base con comentario inicial.
- `pleno/README.md`: explica que el modulo esta separado del codigo original para no afectar el sistema existente.

## Punto de entrada del modulo

Se creo `pleno/index.php` como entrada independiente del modulo.

Este archivo permite abrir el modulo desde:

```text
http://localhost/COREGEDOC/pleno/index.php
```

Tambien permite navegar de forma basica a estas vistas:

```text
http://localhost/COREGEDOC/pleno/index.php?vista=sesiones
http://localhost/COREGEDOC/pleno/index.php?vista=crear_sesion
http://localhost/COREGEDOC/pleno/index.php?vista=editar_sesion
http://localhost/COREGEDOC/pleno/index.php?vista=tabla
```

## Integracion minima con el core

Para que el modulo aparezca dentro del sistema, se modifico solo el archivo:

```text
app/views/partials/sidebar.php
```

Se agrego un enlace llamado `Pleno` dentro del bloque de Gestion, junto a `Gestion Reuniones`.

El enlace apunta a:

```text
pleno/index.php
```

Actualmente se muestra para usuarios con rol:

- Administrador
- Secretario Tecnico

## Lo que no se hizo

No se conecto el modulo a base de datos.

No se implemento CRUD real.

No se agrego logica de votacion.

No se agrego firma electronica.

No se modificaron rutas globales del `index.php` principal.

No se crearon controladores dentro de `app/controllers`.

No se altero la configuracion del sistema.

## Verificacion

Se verifico la sintaxis PHP con:

```text
C:\xampp2\php\php.exe -l .\pleno\index.php
C:\xampp2\php\php.exe -l .\app\views\partials\sidebar.php
```

Resultado:

```text
No syntax errors detected
```
