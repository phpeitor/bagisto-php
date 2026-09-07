# PHPeitor Store 🛒

<p align="center">
  <a href="https://www.instagram.com/amvsoft.tech/" target="_blank">
    <img src="https://metadatape.com/cache/large/product/18/K9q2cOfmv3r9vxIzZ11Ku2C13dTFhnpmkZxSXvJR.webp" height="220" />
  </a>
</p>

Tienda e-commerce en producción para **[metadatape.com](https://metadatape.com)**, construida sobre [Bagisto](https://www.bagisto.com/) (framework e-commerce open source en [Laravel](https://laravel.com/)). Vende templates y productos digitales (dashboards admin, UI kits, SaaS starters) en dos idiomas (Español/Inglés) con precio dual PEN/USD.

## Stack

- **Backend**: Laravel 11, PHP 8.2, MySQL — arquitectura modular en `packages/Webkul/*` (patrón Bagisto: un paquete por dominio — `Product`, `Category`, `Sales`, `Admin`, `Shop`, `Core`, etc.), acceso a datos vía Repository pattern (l5-repository).
- **Frontend**: Vue 3 (instancia global, componentes inline vía `text/x-template`, sin SFC) + Tailwind CSS + Vite, un build independiente por paquete (`Shop`, `Admin`).
- **Contenido**: catálogo EAV (atributos dinámicos por producto), descripciones WYSIWYG saneadas con HTMLPurifier (`stevebauman/purify`), imágenes convertidas a WebP on-upload (con excepción para GIF animado) y servidas vía ruta de caché dinámica.
- **Caché**: `spatie/laravel-responsecache` + paquete propio `Webkul\FPC` para full-page cache, con key por canal/locale/moneda.

Para el detalle real de cada capa (no genérico, basado en este repo), ver **[`.ia-context/`](.ia-context/)**:

| Documento | Contenido |
|---|---|
| [`FRONTEND_ARQUITECTURE.md`](.ia-context/FRONTEND_ARQUITECTURE.md) | Patrón Vue/`x-template`, theming, pipeline de imágenes, i18n/moneda |
| [`FRONTEND_STANDARDS.md`](.ia-context/FRONTEND_STANDARDS.md) | Convenciones de componentes, CSS scoped, formularios |
| [`BACKEND_ARQUITECTURE.md`](.ia-context/BACKEND_ARQUITECTURE.md) | Paquetes, repositories, EAV, multi-canal, HTMLPurifier, cache |
| [`BACKEND_STANDARDS.md`](.ia-context/BACKEND_STANDARDS.md) | Validación, sanitización, config editable, diagnóstico |
| [`DATABASE_STANDARDS.md`](.ia-context/DATABASE_STANDARDS.md) | Migraciones, EAV, `core_config`, operación directa en producción |
| [`AGENTS_ROLES.md`](.ia-context/AGENTS_ROLES.md) | Roles y protocolo de trabajo para iteración asistida por IA |
| [`CAMBIOS_IMPLEMENTADOS.md`](.ia-context/CAMBIOS_IMPLEMENTADOS.md) | Historial de cambios/fixes aplicados en este workspace |

## Instalación local (quick steps)

```bash
git clone <your-repo-url> phpeitor-store
cd phpeitor-store

composer install
cp .env.example .env
php artisan key:generate

# Editar .env y configurar DB_* antes de continuar

php artisan migrate
php artisan bagisto:install

npm install
npm run build

php artisan storage:link
php artisan serve
```

Durante desarrollo, para hot-reload de assets:

```bash
npm run dev
```

Si no usas el servidor local de PHP, apunta el document root de tu servidor web a `public/`.

## Después de tocar config/vistas

Este proyecto tiene varias capas de caché (config, vistas compiladas, full-page cache) — ver [`BACKEND_STANDARDS.md`](.ia-context/BACKEND_STANDARDS.md#caché--checklist-después-de-cambios-backend) para el detalle. Como mínimo:

```bash
php artisan optimize:clear
```

## Contribuir

Antes de un cambio de fondo en frontend, backend o base de datos, revisar el estándar correspondiente en `.ia-context/` y dejar registrado el cambio en `CAMBIOS_IMPLEMENTADOS.md`. Ver también [`CONTRIBUTING.md`](.ia-context/CONTRIBUTING.md).

## Licencia

Basado en Bagisto, licenciado bajo [MIT](LICENSE).
