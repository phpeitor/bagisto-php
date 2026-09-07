# Cambios implementados

Fecha de actualizacion: 2026-09-07

## 1 Proyecto etlweb (Bagisto)

### Soporte GIF en categorias (admin + backend)
- Archivo: `packages/Webkul/Admin/src/Http/Requests/CategoryRequest.php`
- Se agrego `gif` en validacion de mime para `logo_path.*` y `banner_path.*`.

### Preservar GIF sin conversion a WebP
- Archivo: `packages/Webkul/Category/src/Repositories/CategoryRepository.php`
- En `uploadImages(...)` se implemento logica condicional:
- Si extension es GIF: guardar archivo original (`.gif`) con `storeAs`.
- Si no es GIF: mantener flujo actual de conversion a WebP.

### Texto de tipos permitidos en admin (ES)
- Archivo: `packages/Webkul/Admin/src/Resources/lang/es/app.php`
- Se actualizo mensaje de tipos permitidos para incluir GIF.

### Evitar cache estatico para GIF en storefront
- Archivo: `packages/Webkul/Shop/src/Http/Resources/CategoryResource.php`
- Se detecta extension de logo/banner.
- Si es GIF: se retorna `Storage::url(...)` directo (sin rutas `/cache/...`).
- Si no es GIF: se conservan rutas de cache small/medium/large.

### Carousel de categorias: no usar srcset en GIF
- Archivo: `packages/Webkul/Shop/src/Resources/views/components/categories/carousel.blade.php`
- Se agregaron metodos auxiliares para detectar GIF por URL.
- Para GIF se usa `original_image_url` y `srcset = null`.
- Para otros formatos se mantiene `srcset` responsivo.

## 2 Descripcion de productos (HTML en ul/li)

### Render HTML de descripcion
- Archivo: `packages/Webkul/Shop/src/Resources/views/products/view.blade.php`
- Se mantiene salida HTML con `{!! ... !!}` y se aplica `html_entity_decode(...)` para evitar entidades escapadas.
- Se corrigio estructura para no envolver contenido rico en un `<p>` que puede romper listas.

### Override CSS solo en descripcion (sin afectar layout global)
- Archivo: `packages/Webkul/Shop/src/Resources/views/products/view.blade.php`
- Se agrego clase `product-description-content` en desktop y mobile.
- Se agrego estilo scoped con `@pushOnce('styles')`:
- `.product-description-content ul { list-style: disc !important; ... }`
- `.product-description-content ol { list-style: decimal !important; ... }`
- `.product-description-content li { margin-bottom: ... }`
- Objetivo: neutralizar el reset global `ol, ul, menu { list-style: none; }` solo dentro de la descripcion.

## 3 Limpieza de cache ejecutada

Se ejecuto varias veces para aplicar cambios de vistas/config:
- `php artisan optimize:clear`

## 4 Resultado funcional esperado

- Subida de GIF en categorias habilitada en admin.
- GIF animado visible en storefront (sin conversion/caching estatico).
- Descripcion de producto renderiza HTML (incluyendo listas `ul/li`).
- Los bullets de listas se muestran solo en descripcion, sin impactar otros `ul` del layout.

---

# Sesion 2026-09-06 / 2026-09-07

## 5 Descripcion de producto: tablas y estilos (HTMLPurifier)

### Whitelist de tags de tabla
- Archivo: `config/purify.php`
- `HTML.Allowed`: se agrego `table[style], thead, tbody, tfoot, tr, th[colspan|rowspan|style], td[colspan|rowspan|style]`.
- Causa raiz: `clean_content()` (via `ProductForm.php` -> `passedValidation()`) pasa toda descripcion WYSIWYG por HTMLPurifier antes de guardar; al no estar `table/tr/td/th` en la whitelist, el purifier borraba las etiquetas y dejaba solo el texto plano concatenado (se veia "pegado" en el storefront).

### Estilos de tabla (border, etc.)
- Archivo: `config/purify.php`
- `CSS.AllowedProperties`: se agregaron `padding*, margin*, border, border-width, border-style, border-color, border-collapse, border-radius, width, height, vertical-align`.
- `CSS.Proprietary => true` agregado: requerido para que `border-radius` sea reconocido por HTMLPurifier (sin este flag, listar `border-radius` en `AllowedProperties` provocaba un error fatal (`ErrorException` en `CSSDefinition.php`) al guardar el producto -> **HTTP 500**). Fix verificado con `Purify::clean()` via tinker y guardado real del producto.

## 6 Descripcion corta de producto: bullets no se veian

- Archivo: `packages/Webkul/Shop/src/Resources/views/products/view.blade.php` (linea ~369)
- El `short_description` se renderizaba dentro de un `<p>` sin la clase `product-description-content` (la que restaura `list-style` sobre el reset de Tailwind preflight). Un `<ul>` dentro de `<p>` tambien es HTML invalido.
- Cambio: `<p>` -> `<div class="product-description-content ...">`, y se agrego `html_entity_decode(...)` igual que en la descripcion larga.

## 7 Subida de imagenes/videos en productos: GIF y limite de video

### GIF rechazado en imagenes de producto
- Archivo: `packages/Webkul/Admin/src/Http/Requests/ProductForm.php`
- La regla `mimes:bmp,jpeg,jpg,png,webp` no incluia `gif` (en las dos ocurrencias: regla general y regla condicional por archivo nuevo). Se agrego `gif` a ambas. La UI ya decia "png, jpeg, jpg, gif" pero el backend lo rechazaba silenciosamente.

### Limite de video desincronizado (2MB real vs "~50M" en la UI)
- El texto de ayuda usa `core()->getMaxUploadSize()` (limite de PHP, `upload_max_filesize`/`post_max_size`, ambos en 20M en este servidor), pero la validacion real (`ProductForm.php`) usa el config `catalog.products.attribute.file_attribute_upload_size` (KB), que estaba **vacio** en `core_config` y caia al default hardcodeado de `2048` KB (2MB).
- Se actualizo el valor en la base de datos: `core_config.code = 'catalog.products.attribute.file_attribute_upload_size'` -> `value = '20480'` (20MB), acorde al limite real de PHP. Tambien ajustable sin tocar codigo desde **Configuraciones -> Catalogo -> Productos -> Atributos**.

## 8 GIF animado en imagenes de producto no se veia animado

Causa raiz en dos capas independientes (ambas necesarias):

### Subida: se aplanaba a WebP estatico
- Archivo: `packages/Webkul/Product/src/Repositories/ProductMediaRepository.php` (`upload()`)
- Cualquier archivo con mimetype `image/*` (incluido `image/gif`) se re-codificaba con Intervention Image (`->encode('webp')`, driver GD). GD solo lee el primer frame de un GIF animado, asi que el archivo ya se guardaba aplanado y convertido a `.webp` **antes** de tocar disco.
- Fix: se excluye `image/gif` del branch de re-encode; los GIF se guardan tal cual via `$file->store(...)`.

### Entrega: el cache de imagenes tambien aplanaba
- Archivo: `packages/Webkul/Core/src/ImageCache/Controller.php` (`getImage()`)
- La ruta `/cache/{template}/{filename}` procesa toda imagen con Intervention/GD (`fit()`, etc.) al servirla, lo que tambien aplanaria un GIF aunque se hubiese guardado bien.
- Fix: si la extension del archivo original es `.gif`, se bypassea el pipeline de Intervention y se devuelve el binario original (`file_get_contents`) sin procesar.
- Nota operativa: el GIF subido *antes* de este fix en el producto 19 (`xintra-elephpant`) quedo guardado como WebP estatico de un solo frame (`cONJtgvGVehfrY34svkxT88Vt5AgLb37PybHYVUw.webp`) y no es recuperable como animacion; requiere re-subirse.

## 9 Selector de idioma "no cambia" en `/iris-ai`

- Investigado a fondo (middleware `Locale`, sesion, JS del selector). Probado por HTTP directo (`curl` con cookie jar) simulando exactamente `?locale=en`/`?locale=es`: el contenido y `<html lang="...">` cambian correctamente y la sesion persiste.
- Confirmado por el usuario en ventana de incognito: **era cache del navegador del usuario**, no un bug del sitio. Sin cambios de codigo.

## 10 Cambio de idioma a ingles debe cambiar moneda a USD automaticamente

- Archivo: `packages/Webkul/Shop/src/Resources/views/components/layouts/header/desktop/top.blade.php` (componente `v-locale-switcher`)
- Se agrego `localeCurrencyMap: { en: 'USD', es: '{{ core()->getBaseCurrencyCode() }}' }` en `data()`, y en `change(locale)` ahora se setea tambien `?currency=...` en la misma navegacion (ademas de `?locale=...`). El middleware `Currency` de Shop ya soportaba `?currency=` en la URL, no requirio cambios de backend.
- Verificado con `curl`: `?locale=en&currency=USD` -> precios en `$` (ej. `$57.00`); `?locale=es&currency=PEN` -> vuelve a soles.

## 11 Title/SEO del home distinto por idioma ("Demo store" en ingles)

- No era codigo: la fila de `channel_translations` para `locale = en` tenia el `home_seo` (meta_title/description/keywords) y `name` con los valores demo por defecto de Bagisto ("Demo store"), nunca actualizados, mientras que `es` ya tenia "PHPeitor Store".
- Se actualizo via `artisan tinker` (`DB::table('channel_translations')`) el `name` y `home_seo` de ambos locales (`en` y `es`) a **"PHPeitor Store - Metadatape"**. Verificado con `curl` que el `<title>` del home es igual en ambos idiomas.

## 12 Limpieza de cache ejecutada (sesion 2026-09-06/07)

- `php artisan config:clear`, `php artisan view:clear`, `php artisan cache:clear`, `php artisan optimize:clear` ejecutados repetidas veces tras cada cambio de config/Blade/datos, segun corresponde (ver `BACKEND_STANDARDS.md`).

## 13 Documentacion de proyecto (`.ia-context`)

- Se crearon `FRONTEND_ARQUITECTURE.md`, `FRONTEND_STANDARDS.md`, `BACKEND_ARQUITECTURE.md`, `BACKEND_STANDARDS.md`, `DATABASE_STANDARDS.md`, `AGENTS_ROLES.md` para dejar contexto reusable del stack real (Bagisto/Laravel 11, Vue 3 sin SFC via `x-template`, Tailwind, pipeline de imagenes, HTMLPurifier, cache de pagina completa, EAV de productos) y protocolo de trabajo para iteraciones futuras con agentes IA.

## 14 Resultado funcional esperado (sesion 2026-09-06/07)

- Descripcion larga de producto: tablas con bordes/estilos inline se guardan y se ven en storefront.
- Descripcion corta de producto: bullets de `ul/li` visibles.
- Subida de GIF e imagenes en productos funcionando; video acepta hasta 20MB.
- GIF subido desde ahora se mantiene animado en el storefront.
- Selector de idioma confirmado funcional (era cache de navegador).
- Cambiar a ingles cambia moneda a USD automaticamente (y viceversa a la moneda base al volver a espanol).
- Titulo/SEO del home consistente ("PHPeitor Store - Metadatape") en espanol e ingles.