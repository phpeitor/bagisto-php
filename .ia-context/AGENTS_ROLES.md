# Roles de Agentes IA — etlweb (Bagisto)

Este proyecto se itera con asistencia de agentes IA (Claude Code u otros) directamente sobre el código y, cuando hace falta, sobre la base de datos de producción de `metadatape.com`. Este documento define roles, alcance y protocolo de trabajo para que cualquier agente que entre a este repo produzca resultados consistentes con los estándares ya definidos, sin tener que re-descubrirlos cada vez.

Los documentos hermanos (`FRONTEND_*`, `BACKEND_*`, `DATABASE_STANDARDS.md`) son la fuente de verdad técnica. Este documento es el "quién hace qué y cómo".

## Principio general

No existen equipos humanos separados por capa en este proyecto — es un solo desarrollador (dueño del sitio) apoyado por agentes IA. Los "roles" de abajo no son personas, son **modos de trabajo** que un agente debe adoptar según el tipo de tarea, para acotar el radio de cambio y aplicar el estándar correcto.

## Rol: Frontend Agent

- **Alcance**: `packages/Webkul/Shop/src/Resources/{views,assets}`, `packages/Webkul/Admin/src/Resources/{views,assets}`, `resources/themes` (si algún día se activa un theme override).
- **Debe leer antes de tocar nada**: `FRONTEND_ARQUITECTURE.md`, `FRONTEND_STANDARDS.md`.
- **Responsabilidades**: markup Blade, componentes Vue inline (`x-template`), CSS/Tailwind, textos i18n, comportamiento de UI (dropdowns, formularios, selectores).
- **No debe**: agregar validación de negocio solo en cliente sin la contraparte en `FormRequest`; hardcodear textos sin `@lang`; tocar `config/purify.php` (eso es Backend Agent, aunque el síntoma se vea en frontend).
- **Verificación mínima antes de cerrar una tarea**: `php artisan view:clear`, y confirmación visual real (navegador si está disponible, si no `curl` comparando HTML servido).

## Rol: Backend Agent

- **Alcance**: `packages/Webkul/*/src/{Http,Repositories,Models,Providers,Config}`, `config/*.php` (raíz del proyecto).
- **Debe leer antes de tocar nada**: `BACKEND_ARQUITECTURE.md`, `BACKEND_STANDARDS.md`.
- **Responsabilidades**: FormRequests, repositories, pipeline de imágenes, sanitización HTML, configuración editable (`core_config` / `Admin/src/Config/system.php`), caché de aplicación (Spatie ResponseCache / FPC).
- **No debe**: validar en el controller directo; deshabilitar HTMLPurifier en vez de extender la whitelist; hardcodear límites que deberían ser config editable.
- **Verificación mínima**: revisar `storage/logs/laravel.log` tras cualquier guardado/formulario probado; limpiar cachés relevantes (`config:clear`/`view:clear`/`optimize:clear`) antes de dar el fix por probado.

## Rol: Database Agent

- **Alcance**: `packages/Webkul/*/src/Database/Migrations`, operaciones directas vía `artisan tinker` sobre datos existentes (settings, traducciones, contenido).
- **Debe leer antes de tocar nada**: `DATABASE_STANDARDS.md`.
- **Responsabilidades**: migraciones idempotentes, correcciones de datos puntuales (p.ej. un `core_config` vacío, una traducción con el placeholder demo de Bagisto), índices de performance.
- **No debe**: correr `UPDATE`/`DELETE` sin `where` acotado; escribir directo en tablas derivadas (`product_flat`); dropear columnas/tablas sin confirmación explícita del usuario — este entorno **es** producción.
- **Verificación mínima**: `SELECT` antes y después de cualquier escritura manual; limpiar caché de config/app si el dato tocado se cachea.

## Rol: Release / Housekeeping Agent

- **Alcance**: `.ia-context/CAMBIOS_IMPLEMENTADOS.md`, limpieza de caché post-deploy, verificación end-to-end de un set de cambios ya hechos por los roles anteriores.
- **Responsabilidad principal**: al cierre de cada sesión de trabajo (o cada bloque de cambios coherente), **agregar una entrada nueva** a `CAMBIOS_IMPLEMENTADOS.md` con fecha, archivos tocados, y el problema que resolvía cada cambio — no reescribir entradas anteriores, solo agregar.
- Corre el checklist de caché final (`php artisan optimize:clear`) y deja constancia de qué se verificó (curl, tinker, etc.) y qué quedó pendiente de que el usuario confirme visualmente.

## Protocolo de trabajo (todos los roles)

1. **Diagnosticar antes de tocar código.** La mayoría de bugs reales de este proyecto hasta ahora no eran "el código está mal escrito" sino: caché en alguna de sus 3-4 capas, un límite/whitelist desactualizado, o un dato de contenido vacío/demo. Leer logs y el estado real (BD, config) antes de asumir dónde está el bug.
2. **Ubicar la capa correcta.** Un mismo síntoma (p.ej. "el GIF no se ve animado") puede tener causa en subida (Repository), en entrega (ImageCache Controller), o en ambas — no declarar resuelto hasta verificar las capas involucradas de punta a punta.
3. **Cambio mínimo y explicado.** Extender whitelists/configs de forma puntual y documentar el porqué (no "por si acaso").
4. **Limpiar caché correspondiente** después de cualquier cambio a `config/*.php`, Blade, o datos en `core_config`/`*_translations`.
5. **Verificar sin asumir.** Con navegador si está disponible; si no, con `curl`/`tinker` reproduciendo la request o el dato exacto. No reportar "listo" sin evidencia.
6. **Registrar el cambio** en `CAMBIOS_IMPLEMENTADOS.md` (rol Release/Housekeeping, o el mismo agente si trabaja solo) — es el historial que reemplaza a un changelog de PRs individuales, dado que aquí se itera directo sobre el repo.
7. **Pedir confirmación antes de acciones destructivas o irreversibles** (drop de columnas, borrar archivos subidos por el usuario, force-push, etc.) — igual que con cualquier otro proyecto, pero con más motivo al ser este un entorno productivo real.
