# Estándares Backend — etlweb (Bagisto)

Reglas prácticas de código y operación para `packages/Webkul/*` y `config/*`. Ver `BACKEND_ARQUITECTURE.md` para el contexto de cada patrón.

## Estilo de código

- PHP 8.2, Laravel Pint configurado (`pint.json`) — correr `./vendor/bin/pint --dirty` antes de dar por cerrado un cambio si se tocó mucho código.
- Seguir el estilo de docblocks ya presente en el paquete que se edita (`/** ... * @return ... */` corto, sin relleno).
- No agregar comentarios que expliquen el "qué" (el código ya lo dice); solo el "por qué" cuando no es obvio (p.ej. por qué GD no puede procesar GIF animado, por qué `CSS.Proprietary` debe ser `true`).

## Validación

- Toda validación de request va en un `FormRequest` (`Http/Requests/*`). No validar a mano dentro del controller.
- Si el campo es dinámico (atributo EAV de producto), la regla se construye en `rules()` iterando atributos — no hardcodear el campo si ya existe ese mecanismo.
- Mensajes de error de UI (`title-info`, textos de ayuda) deben coincidir con la regla real de validación. Si cambias un límite (mimes, tamaño máximo), busca también el texto que lo describe en `Resources/lang/*/app.php` y en el Blade — no dejar la copy desincronizada del código (bug real detectado esta sesión: copy decía "~50M", regla real validaba 2048 KB).

## Repositorios

- Un repository por agregado, extendiendo `Webkul\Core\Eloquent\Repository`.
- Side-effects (archivos, conversión de imágenes, llamadas externas) van en el repository, nunca en el controller ni en el FormRequest.
- Antes de tocar lógica de subida de archivos, revisar si existe ya un método reusado por Admin y Shop (p.ej. `ProductMediaRepository` es compartido) — un fix a medias (solo un lado) dejó bugs reales esta sesión.

## Sanitización HTML / campos WYSIWYG

- Nunca deshabilitar `clean_content()`/HTMLPurifier para "que pase". Si un tag o propiedad CSS legítima falta:
  1. Agregar el tag con sus atributos mínimos necesarios a `HTML.Allowed` en `config/purify.php`.
  2. Agregar las propiedades CSS puntuales a `CSS.AllowedProperties`.
  3. Si la propiedad es "proprietary" para HTMLPurifier (p.ej. `border-radius`), confirmar `CSS.Proprietary => true` está activo — si no, HTMLPurifier lanza un error fatal (no silencioso) al construir la definición CSS, y el guardado del formulario devuelve 500.
  4. Siempre `php artisan config:clear` después de tocar `config/purify.php` — es config cacheable.
- Recordar: HTML permitido pero con estilos no whitelisteados no da error — simplemente el atributo se pierde en el guardado. Si "el HTML se ve pero sin estilos", es esto, no un bug de render.

## Configuración editable (`core_config`)

- Un límite, flag o texto que el negocio pueda querer cambiar sin deploy va como campo en `Admin/src/Config/system.php` + se lee con `core()->getConfigData(...)`, con un fallback razonable en código si la fila está vacía.
- No crear tablas nuevas para settings simples; usar `core_config` (key/value, opcionalmente por canal/locale).
- Cambios directos a `core_config` vía `artisan tinker` (para setear un valor sin pasar por el admin) son aceptables para este proyecto (acceso directo autorizado a la BD de producción), pero: leer el valor actual antes de sobreescribir, y correr `php artisan cache:clear` después (estos valores pueden cachearse en `cache` store).

## Manejo de errores / diagnóstico

- Ante un 500 en cualquier flujo de guardado, **lo primero** es `tail -100 storage/logs/laravel.log` — casi siempre el stacktrace apunta directo a la causa (así se encontró el bug de `border-radius`/HTMLPurifier).
- No asumir que un mensaje genérico de error ("Ups, algo salió mal") es el problema real — es la página 500 estándar de Bagisto; el log tiene la excepción real.

## Caché — checklist después de cambios backend

1. `php artisan config:clear` si tocaste cualquier `config/*.php`.
2. `php artisan view:clear` si tocaste Blade.
3. `php artisan cache:clear` si tocaste datos en `core_config` u otro dato leído vía `Cache`.
4. `php artisan optimize:clear` como martillo general si no estás seguro de cuál aplica.
5. OPcache de PHP-FPM (`opcache.validate_timestamps=On`, revalida cada 2s) recoge cambios de `.php` solo; no reemplaza los pasos anteriores, que son cachés a nivel Laravel/app, no a nivel de bytecode.

## Verificación sin navegador

- Cuando no hay navegador disponible en la sesión, verificar cambios de storefront con `curl` simulando exactamente la request que dispararía el JS (mismos query params, cookie jar para mantener sesión) y comparando contenido (`grep`/`diff`) — así se confirmó que el fix de idioma/moneda funcionaba antes de que el usuario lo probara manualmente.
- No dar un fix por confirmado solo porque "el código se ve correcto" — correr la request real cuando sea posible.

## Archivos que casi nunca se deben tocar a la ligera

- `config/purify.php` (afecta sanitización de **todo** contenido WYSIWYG del sitio, no solo el campo que estás arreglando) — cambios ahí son globales, revisar impacto amplio antes de ampliar la whitelist.
- `packages/Webkul/Core/src/ImageCache/Controller.php` y `ProductMediaRepository.php` (afectan el pipeline de imágenes de todo el catálogo).
- `packages/Webkul/FPC/src/Hasher/DefaultHasher.php` (afecta la key de caché de página completa de todo el sitio).
