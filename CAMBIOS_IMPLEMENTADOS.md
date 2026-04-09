# Cambios implementados

Fecha de actualizacion: 2026-04-09

## 1) Proyecto etlweb (Bagisto)

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

## 2) Descripcion de productos (HTML en ul/li)

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

## 3) Limpieza de cache ejecutada

Se ejecuto varias veces para aplicar cambios de vistas/config:
- `php artisan optimize:clear`

## 4) Resultado funcional esperado

- Subida de GIF en categorias habilitada en admin.
- GIF animado visible en storefront (sin conversion/caching estatico).
- Descripcion de producto renderiza HTML (incluyendo listas `ul/li`).
- Los bullets de listas se muestran solo en descripcion, sin impactar otros `ul` del layout.