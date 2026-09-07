# Arquitectura Frontend — etlweb (Bagisto)

Contexto real del repo (no genérico). Proyecto: **Bagisto** (Laravel 11 + PHP 8.2), tienda `metadatape.com` (theme "PHPeitor Store"). El frontend NO es una SPA: es **Blade first**, con **Vue 3** montado como una única instancia global por área (storefront / admin) que hidrata componentes declarados inline en Blade.

## 1. Las tres "aplicaciones" frontend

Cada una es un paquete independiente con su propio `package.json`, `vite.config.js` y `tailwind.config.js`:

| Área | Paquete | Build output | Entry JS/CSS |
|---|---|---|---|
| Storefront | `packages/Webkul/Shop` | `public/themes/shop/default/build` | `src/Resources/assets/js/app.js`, `.../css/app.css` |
| Admin | `packages/Webkul/Admin` | `public/themes/admin/default/build` | `src/Resources/assets/js/app.js` |
| Installer | `packages/Webkul/Installer` | build propio | — |

`resources/themes` (raíz del proyecto) existe para **overrides de theme** custom, pero hoy está **vacío** — no hay theme override activo, todo corre sobre el theme "default" de `packages/Webkul/Shop`. Si en el futuro se crea un theme custom, Bagisto resuelve vistas por convención de nombre de theme, y esas vistas tienen prioridad sobre las de `packages/Webkul/Shop/src/Resources/views`.

## 2. Patrón de componentes: Vue global + `text/x-template`

No hay archivos `.vue` (SFC) en el proyecto. El patrón real, visto en `packages/Webkul/Shop/src/Resources/views/components/layouts/header/desktop/top.blade.php`:

```blade
<v-locale-switcher></v-locale-switcher>

@pushOnce('scripts')
    <script type="text/x-template" id="v-locale-switcher-template">
        <div>...</div>
    </script>

    <script type="module">
        app.component('v-locale-switcher', {
            template: '#v-locale-switcher-template',
            data() { return { locales: @json(...) }; },
            methods: { change(locale) { ... } },
        });
    </script>
@endPushOnce
```

- `window.app` se crea una sola vez en `src/Resources/assets/js/app.js` (`createApp({...})`) y se monta sobre el body del layout principal.
- Cada componente Blade que necesita interactividad se registra con `app.component(nombre, config)` dentro de un `@pushOnce('scripts')`, usando como plantilla un `<script type="text/x-template" id="...">` con el mismo `id`.
- Datos del backend se inyectan al componente vía `@json(...)` de PHP directamente en `data()` — **no hay llamadas AJAX** para el estado inicial; el HTML ya viene con los datos.
- `v-pre` se usa en los nodos que son Blade puro (evita que Vue intente interpolar `{{ }}` de Blade ya renderizado).
- Interpolaciones Vue en el `x-template` usan `@{{ variable }}` (el `@` escapa el `{{` de Blade).
- **`@pushOnce` no deduplica entre archivos Blade distintos** que empujan al mismo stack `'scripts'` con IDs iguales (ver `desktop/top.blade.php` vs `mobile/index.blade.php`, que ambos reusan `#v-locale-switcher-template` sin redefinirlo — solo uno de los dos define el componente). Si se duplica un `id` de `x-template` en dos archivos distintos, gana el primero en el DOM.

## 3. Extensibilidad: `view_render_event`

Puntos de extensión declarados en casi todas las vistas relevantes:

```blade
{!! view_render_event('bagisto.shop.products.view.description.before', ['product' => $product]) !!}
```

Al agregar funcionalidad nueva a una vista core, preferir **escuchar estos eventos desde otro paquete** en vez de editar la vista core, salvo que el cambio sea estructural (como los que hicimos esta sesión sobre `product-description-content`).

## 4. Estilos

- Tailwind CSS 3 (`tailwind.config.js` por paquete), utility-first.
- El `preflight` de Tailwind resetea `list-style: none` en `ul/ol` globalmente. Contenido HTML dinámico (descripciones WYSIWYG) necesita overrides **scoped a una clase específica**, nunca un reset global. Ejemplo real ya implementado:

```blade
@pushOnce('styles')
    <style>
        .product-description-content ul { list-style: disc !important; ... }
        .product-description-content ol { list-style: decimal !important; ... }
    </style>
@endPushOnce
```

- Contenido rico (`ul`, `table`, etc.) debe ir envuelto en `<div>`, nunca en `<p>` (un `<p>` no puede contener bloques; el navegador lo cierra antes, produciendo HTML inválido y layout impredecible).

## 5. Imágenes y assets dinámicos

- Toda imagen de producto/categoría subida se sirve vía ruta de caché dinámica `/cache/{template}/{filename}` (`Webkul\Core\ImageCache\Controller`), que aplica resize (`fit()`) con Intervention Image (driver **GD**).
- **GIF es un caso especial**: GD solo lee el primer frame de un GIF animado. Por eso:
  - En subida (`ProductMediaRepository::upload()`), los GIF se guardan **sin** re-encodear a WebP (se preserva el archivo original).
  - En entrega (`ImageCache\Controller::getImage()`), si el archivo original es `.gif`, se **bypasea** todo el pipeline de Intervention y se devuelve el binario crudo.
  - Cualquier nuevo punto que sirva imágenes de producto (carousels, cards, etc.) debe replicar esta excepción o reusar el mismo helper — comprobar por extensión antes de generar `srcset`/thumbnails.

## 6. i18n / multi-idioma / multi-moneda en frontend

- Locale activo: `app()->getLocale()`. Selector de idioma hace **full page reload** con `?locale=xx` (no es SPA routing).
- Selector de moneda igual, `?currency=XXX`.
- Regla de negocio agregada (2026-09): al cambiar a `en` desde el selector de idioma, el mismo click también setea `currency=USD`; al volver a `es`, vuelve a la moneda base del canal (`core()->getBaseCurrencyCode()`). Ver `localeCurrencyMap` en `v-locale-switcher` (`top.blade.php`). Si se agregan más locales, extender ese mapa ahí (no hay modelo de datos para "moneda por locale" en Bagisto, es una convención de este proyecto).
- Textos: `@lang('shop::app.clave')`, archivos en `src/Resources/lang/{locale}/app.php` por paquete.

## 7. Caché a tener en cuenta al iterar frontend

- Blade compilado: `php artisan view:clear`.
- Página completa (Spatie ResponseCache + `Webkul\FPC`): la key de caché es `path` (sin query string, salvo `search?query=`) + `channel-locale-currency`. Ver `packages/Webkul/FPC/src/Hasher/DefaultHasher.php`. Cambios visuales que dependen de otro query param no se van a diferenciar en caché a menos que se ajuste el hasher.
- OPcache en PHP-FPM tiene `validate_timestamps=On` (revalida cada 2s), así que cambios en `.php` se reflejan solos; **no así** los cachés de Laravel (config/view/route), que sí requieren `php artisan optimize:clear` explícito después de tocar `config/*.php` o Blade.
