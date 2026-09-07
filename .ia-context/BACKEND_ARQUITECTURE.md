# Arquitectura Backend — etlweb (Bagisto)

Laravel 11 + PHP 8.2. **No es un Laravel "app/" monolítico**: casi todo el código vive en paquetes propios bajo `packages/Webkul/*`, instalados como *path repositories* de Composer (no son vendors de terceros — son código del proyecto, se editan directamente).

## 1. Paquetes (bounded contexts)

`packages/Webkul/{Admin, Shop, Core, Product, Category, Customer, Sales, Checkout, Payment, Shipping, Tax, Inventory, CartRule, CatalogRule, Attribute, DataGrid, DataTransfer, FPC, Marketing, CMS, Theme, User, ...}`

Cada paquete sigue la misma convención interna:

```
src/
  Config/            # arrays de config (system.php = campos del panel Configuración)
  Contracts/          # interfaces de modelos (para poder swapearlos)
  Database/Migrations/
  Database/Seeders/
  Http/Controllers/
  Http/Middleware/
  Http/Requests/       # FormRequests — única capa de validación
  Http/Resources/       # API Resources (transformación de salida)
  Models/
  Providers/            # ServiceProvider del paquete, registra rutas/migraciones/eventos
  Repositories/          # única capa de acceso a datos desde controllers
  Resources/views/       # Blade
  Resources/lang/         # traducciones
  Resources/assets/        # JS/CSS del paquete (Admin y Shop)
  Routes/
```

`Admin` = panel `/admin/*`. `Shop` = storefront público. `Core` = helpers compartidos, EAV base, `core()` facade, sistema de configuración, image cache. El resto son dominios de negocio (Product, Category, Sales, etc.) consumidos por Admin y Shop.

## 2. Capa de datos: Repository pattern (l5-repository/Prettus)

- Toda clase repository extiende `Webkul\Core\Eloquent\Repository` (agrega cacheo vía `CacheableRepository`).
- **Los controllers no deben tocar Eloquent directamente.** Flujo estándar: `Controller` → `FormRequest` (valida) → `Repository` (crea/actualiza/orquesta side-effects como subir archivos) → `Model`.
- Lógica de side-effects (subir imagen, convertir a WebP, mover archivo) vive en el Repository (ver `ProductMediaRepository::upload()`), no en el Controller ni en el Model.

## 3. Productos: EAV (Entity-Attribute-Value)

Los productos no tienen columnas fijas por atributo; usan `attributes` + `product_attribute_values` + `product_flat` (tabla desnormalizada de lectura, una fila por producto+canal+locale). Las reglas de validación de un producto se arman **en runtime** a partir de sus atributos editables (`ProductForm::rules()`, itera `$product->getEditableAttributes()`), no son un array estático. Al agregar comportamiento nuevo sobre "un campo de producto", verificar si es un atributo EAV (dinámico) o un campo fijo del modelo — cambia dónde hay que tocar.

## 4. Multi-tenant lógico: Channel → Locale / Currency

- `Channel` tiene N `locales` y N `currencies`, y un `default_locale` / `base_currency`.
- Casi toda lectura pasa por el helper global `core()` (`Webkul\Core\Core`), p.ej. `core()->getCurrentChannel()`, `core()->getCurrentLocale()`, `core()->getConfigData('grupo.campo')`.
- En el storefront, el grupo de middleware `shop` (`ShopServiceProvider::boot()`) resuelve esto en cada request, **en este orden**: `Theme → Locale → Currency`. Locale setea `app()->setLocale()` y `session('locale')`; Currency lee `session('currency')`/query `?currency=`. No reordenar sin razón: código downstream (incluida la generación de cache key de página completa) asume que `app()->getLocale()` ya está resuelto para cuando corre el middleware de caché de respuesta.

## 5. Configuración editable desde Admin (`core_config`)

- Tabla `core_config` (`code`, `value`, `channel_code`, `locale_code`) — key/value, opcionalmente scoped por canal/locale.
- Los campos que aparecen en **Configuraciones** del admin se declaran en `packages/Webkul/Admin/src/Config/system.php` (grupos → `fields`), y se leen con `core()->getConfigData('grupo.subgrupo.campo')`.
- Ejemplo real: `catalog.products.attribute.file_attribute_upload_size` controla el tamaño máximo de video permitido en productos; si la fila no tiene valor, el código cae a un default hardcodeado (`ProductForm.php`: `?: '2048'`). **Cualquier límite/feature-flag nuevo que el negocio pueda querer ajustar va aquí, no como constante en código.**

## 6. Sanitización de HTML (HTMLPurifier)

- Todo campo de texto marcado como WYSIWYG (`enable_wysiwyg` en el atributo) pasa por `clean_content()` (`Core/src/Http/helpers.php`) en `FormRequest::passedValidation()` antes de guardarse — ver `ProductForm.php`, `CategoryController.php`, `PageController.php`, `TemplateController.php`.
- `clean_content()` = `Purify::clean()` (stevebauman/purify sobre `ezyang/htmlpurifier`) + una limpieza extra de `{{ }}`, `{!! !!}` y directivas Blade (para que un usuario no pueda inyectar Blade/PHP vía un campo de texto).
- La whitelist real vive en `config/purify.php`:
  - `HTML.Allowed`: tags + atributos permitidos por tag (`table[style]`, `td[colspan|rowspan|style]`, etc.).
  - `CSS.AllowedProperties`: propiedades CSS permitidas dentro de cualquier `style="..."`.
  - `CSS.Proprietary`: debe estar `true` para poder usar propiedades como `border-radius` (HTMLPurifier las excluye por defecto y, si están en `AllowedProperties` sin este flag, **lanza un error fatal al bootear el Purifier**, no un warning silencioso — esto tumbó el guardado de producto con un 500 real esta sesión).
  - Un tag permitido en `HTML.Allowed` pero cuyo `style` use una propiedad no listada en `CSS.AllowedProperties` **se guarda sin esa propiedad** (silencioso, no error) — causa típica de "guardé el HTML pero no se ve el estilo".
- **Nunca** desactivar el purifier para "que funcione" — extender la whitelist de forma deliberada y mínima.

## 7. Pipeline de imágenes

- Subida (`ProductMediaRepository::upload()`, y análogo en `CategoryRepository::uploadImages()`): imágenes raster se re-encodean a WebP con Intervention Image (driver GD, `config/image.php`). **GIF es la excepción**: se guarda tal cual (sin encode), porque GD solo lee el primer frame de un GIF animado y el encode lo aplanaría permanentemente.
- Entrega (`Webkul\Core\ImageCache\Controller`, ruta `/cache/{template}/{filename}`): aplica resize/fit vía Intervention bajo demanda y cachea el resultado. Misma excepción de GIF aplicada ahí (bypass total del pipeline si la extensión es `.gif`).
- Estas dos capas son independientes — una corrección en una sin la otra deja el bug a medias (pasó esta sesión: se corrigió la entrega antes que la subida, y el archivo ya estaba dañado en disco desde la subida).

## 8. Caché de página completa

- `spatie/laravel-responsecache` + paquete propio `Webkul\FPC`, activado por `RESPONSE_CACHE_ENABLED` en `.env`.
- Middleware `cache.response` (`Webkul\Shop\Http\Middleware\CacheResponse`, extiende el de Spatie) se aplica por ruta en `packages/Webkul/Shop/src/Routes/store-front-routes.php` (home, producto/categoría, CMS, búsqueda, compare).
- La key de caché la genera `Webkul\FPC\Hasher\DefaultHasher`: `path` (sin query string, salvo `search?query=`) + sufijo `canal-locale-moneda`. Cualquier variante de contenido que dependa de algo distinto a canal/locale/moneda/path **no se diferencia en caché** salvo que se toque este hasher.
- Antes de invalidar por "no se refleja el cambio", diferenciar: caché de Blade compilado, caché de página completa, y caché del navegador — son tres capas distintas con comandos/estrategias distintas.

## 9. Extensibilidad

- Eventos Laravel (`Event::dispatch`, Observers registrados en cada `ServiceProvider::boot()`, p.ej. `ProductProxy::observe(ProductObserver::class)`) para reaccionar a cambios de dominio.
- `view_render_event('namespace.punto.before|after', [...])` en Blade como puntos de extensión visual sin tocar la vista core.
- Al extender un paquete core, preferir un paquete/listener nuevo que se enganche a estos puntos. Si el cambio es estructural (como los de esta sesión: fix de bug, cambio de markup base), editar el archivo core directamente es válido — no es código de terceros, es el proyecto.
