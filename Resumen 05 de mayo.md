# Resumen 05 de mayo

## Objetivo del d�a
Implementar en el m�dulo `pleno` el control de acceso por tipo de usuario funcional (`tipoUsuario_id`) para las HU del Sprint 1, sin tocar el login global ni agregar funcionalidades fuera de alcance.

## Alcance implementado
- Se protegi� el acceso general del m�dulo `pleno` usando sesi�n existente del sistema.
- Se valid� autorizaci�n funcional solo por `tipoUsuario_id` (tabla `t_tipousuario`).
- Se permiti� acceso �nicamente a los tipos de usuario:
  - `1` Consejero Regional
  - `6` Administrador
  - `20` Secretaria Pleno
  - `21` Secretario ejecutivo
  - `22` Gobernacion
- Se bloque� acceso a cualquier otro `tipoUsuario_id` con respuesta `403` y vista de acceso denegado.
- Se agreg� visualizaci�n de prueba en pantalla:
  - `Perfil activo: [nombre del perfil detectado]`

## Restricciones respetadas
- No se implement� CRUD.
- No se implement� creaci�n/edici�n/eliminaci�n de sesiones.
- No se implement� votaciones.
- No se implement� tabla din�mica.
- No se implement� PDF.
- No se implement� FirmaGob.
- No se modific� el login global.
- No se cre� sistema nuevo.
- No se alter� el flujo principal m�s de lo necesario.
- Cambios realizados solo dentro de la carpeta `pleno/`.

## Variables reales de sesi�n detectadas
Desde autenticaci�n existente se confirmaron:
- `$_SESSION['idUsuario']`
- `$_SESSION['tipoUsuario_id']`
- `$_SESSION['pNombre']`
- `$_SESSION['aPaterno']`
- `$_SESSION['email']`
- `$_SESSION['rutaImagenPerfil']`

## Validaci�n implementada
Se cre� helper reutilizable en `pleno/helpers/auth.php`:
- Inicia sesi�n si corresponde (`session_start`).
- Si no existe `$_SESSION['idUsuario']`: redirige a `../index.php?action=login`.
- Valida `$_SESSION['tipoUsuario_id']` contra `[1, 6, 20, 21, 22]`.
- Si no est� permitido: entrega `403` y marca no autorizado.
- Si est� permitido: obtiene nombre de perfil desde `t_tipousuario.descTipoUsuario`.

## Rutas cubiertas
La protecci�n aplica desde `pleno/index.php`, por lo que cubre:
- `/pleno/index.php`
- `/pleno/index.php?vista=sesiones`
- `/pleno/index.php?vista=tabla`

## Archivos modificados/creados hoy
- Modificado: `pleno/index.php`
- Creado: `pleno/helpers/auth.php`
- Creado: `pleno/views/acceso_denegado.php`
- Modificado: `pleno/views/index.php`
- Modificado: `pleno/views/sesiones.php`
- Modificado: `pleno/views/tabla.php`

## Resultado por historias de usuario (Sprint 1)
- HU-01 Secretar�a: acceso habilitado para `tipoUsuario_id = 20`.
- HU-02 Consejero: acceso habilitado para `tipoUsuario_id = 1`.
- HU-03 Gobernador: acceso habilitado para `tipoUsuario_id = 22`.
- HU-04 Secretario Ejecutivo: acceso habilitado para `tipoUsuario_id = 21`.

En todos los casos permitidos se muestra el perfil activo detectado.

## Verificaci�n sugerida
1. Iniciar sesi�n normal en COREGEDOC.
2. Entrar a `/pleno/index.php`.
3. Probar con usuarios existentes o cambiando temporalmente `tipoUsuario_id` en BD.
4. Confirmar:
   - Acceso permitido para IDs 1, 6, 20, 21, 22.
   - Acceso bloqueado para cualquier otro ID.
   - Texto visible: `Perfil activo: ...`.

## Observaci�n t�cnica
No se ejecut� lint con `php -l` porque el comando `php` no est� disponible en el PATH de esta terminal.
