# Estándares Frontend — etlweb (Bagisto)

Reglas prácticas para trabajar en `packages/Webkul/Shop` y `packages/Webkul/Admin`. Ver `FRONTEND_ARQUITECTURE.md` para el porqué de cada patrón.

## Componentes Vue (`x-template`)

- Nombrar componentes con prefijo `v-` (`v-locale-switcher`, `v-currency-switcher`, `v-topbar`) — es la convención existente, no mezclar con nombres tipo `App*` o `<Component>`.
- Un componente = un `id` de `text/x-template` único en **todo el proyecto**, no solo en el archivo. Antes de crear uno nuevo, `grep -rn 'id="v-'` para evitar colisiones entre desktop/mobile/otros includes.
- Registrar el componente una sola vez (`app.component(...)` dentro de `@pushOnce('scripts')`); si el mismo componente se usa en desktop y mobile, la definición vive en **un solo archivo** (hoy: `desktop/top.blade.php`) y el otro archivo (`mobile/index.blade.php`) solo lo consume vía `<v-locale-switcher></v-locale-switcher>`. No dupliques la lógica del `methods`/`data` en el segundo archivo.
- Datos iniciales del backend van por `@json(...)` en `data()`, no por `fetch`/`axios` al montar, salvo que el dato cambie post-carga (carritos, autocompletados, etc.) — para eso ya existen los plugins `axios`/`emitter` en `src/Resources/assets/js/plugins`.
- `v-pre` en cualquier bloque que sea Blade puro sin bindings Vue, para evitar que Vue intente parsear `{{ }}` ya resueltos por el servidor.
- Dentro de un `x-template`, las interpolaciones Vue se escriben `@{{ expr }}` (el `@` es obligatorio, si no Blade se come el `{{ }}`).

## Reglas de negocio en JS

- Evitar hardcodear datos de negocio en JS si el backend ya los expone (precios, configuración de catálogo, etc.) — inyectarlos vía Blade (`@json`, `{{ core()->getConfigData(...) }}`).
- Cuando SÍ haga falta un mapeo fijo en JS (p.ej. `localeCurrencyMap: { en: 'USD', es: '...' }` porque Bagisto no modela esa relación), documentarlo con un comentario corto explicando el porqué, y derivar del backend lo que sí exista (usar `core()->getBaseCurrencyCode()` en vez de hardcodear el código de moneda base).

## CSS / Tailwind

- Utility-first siempre que se pueda. Un `<style>` inline (`@pushOnce('styles')`) solo se justifica cuando:
  1. El contenido es HTML dinámico/generado por el usuario (WYSIWYG) que Tailwind preflight resetea (listas, tablas), o
  2. Es un override muy puntual que no vale la pena modelar como utilidades.
- Todo override de este tipo debe ir **scoped a una clase específica** del contenedor (`.product-description-content ul {...}`), nunca tocar selectores globales (`ul`, `table`) que afectarían el resto del layout.
- No envolver HTML rico (listas, tablas) en un `<p>`. Usar `<div>`.

## Formularios y validación

- El manejo de errores de validación en cliente ya está centralizado en `onInvalidSubmit` (`src/Resources/assets/js/app.js`): hace scroll + focus al primer campo con error, incluso casos especiales (TinyMCE, campos array). No reimplementar scroll-to-error por formulario nuevo; si un campo nuevo no es detectado, es porque el `name` del input no calza con la key de error — ajustar el `name`, no el JS global.
- Validación real vive en el backend (`FormRequest`). El frontend no debe considerarse la fuente de verdad de reglas (mimes, tamaños máximos, etc.) — ver `BACKEND_STANDARDS.md`. Si el copy de la UI dice un límite ("máx. 50M"), verificar que sea el mismo que valida el backend antes de confiar en él (bug real encontrado esta sesión: la UI decía ~50M, el backend limitaba a 2MB).

## Imágenes subidas por usuario

- Cualquier componente/plantilla que renderice imágenes de producto/categoría debe contemplar GIF como caso especial (no aplicar `srcset`/resize responsivo si la extensión final es `.gif`; usar la imagen original). Ver ejemplo ya resuelto en `components/categories/carousel.blade.php`.

## Multi-idioma

- Todo string visible al usuario va por `@lang('paquete::app.clave')`, nunca hardcodeado en Blade ni en JS.
- Si un texto usa una variable (`:size`, `:name`), pasarla explícita en el `trans()`/`@lang()` — no concatenar strings en PHP/JS.

## Después de cualquier cambio Blade/CSS/JS inline

1. `php artisan view:clear` (compilado Blade) como mínimo; `php artisan optimize:clear` si también tocaste `config/*.php`.
2. Si el cambio afecta una ruta con `cache.response` (home, producto, categoría, CMS), probar con `curl` pasando por sesión limpia o verificar que el cache key (locale/currency/canal) realmente cambió — un "no se refleja" muchas veces es caché de página completa, no el código.
3. Si es un cambio visual, confirmarlo en storefront real (no basta con que compile) — usar `curl` para verificar HTML servido cuando no hay navegador disponible.
