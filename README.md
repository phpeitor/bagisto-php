<a href="https://www.instagram.com/amvsoft.tech/" target="_blank">
  <img src="https://metadatape.com/cache/large/product/18/K9q2cOfmv3r9vxIzZ11Ku2C13dTFhnpmkZxSXvJR.webp" />
</a>

# Introduction

Bagisto is an opensource [laravel eCommerce](https://www.bagisto.com/) framework built on some of the hottest technologies such as [Laravel](https://laravel.com/) (a [PHP](https://secure.php.net/) framework) and [Vue.js](https://vuejs.org/) a progressive Javascript framework.

Bagisto can help you cut down your time, cost, and workforce for building online stores or migrating from physical stores to the ever-demanding online world. Your business—whether small or huge—can benefit. The best part, it's straightforward to set it up!

## Local Installation (Quick Steps)

These steps are intended for local/dev environments in this repository:

1. Clone the repository and enter the project folder.
2. Install PHP dependencies.
3. Copy environment variables and generate app key.
4. Configure database credentials in `.env`.
5. Run migrations and Bagisto installer.
6. Install frontend dependencies and build assets.
7. Start the local server.

```bash
git clone <your-repo-url> bagisto
cd bagisto

composer install
cp .env.example .env
php artisan key:generate

# Edit .env and set DB_* values before continuing

php artisan migrate
php artisan bagisto:install

npm install
npm run build

php artisan storage:link
php artisan serve
```

Optional during development:

```bash
npm run dev
```

If you do not use local PHP server, point your web server document root to the `public/` directory.

## Project Change Context

For customizations and fixes applied in this workspace (GIF support, category image behavior, product description HTML rendering, and CSS list overrides), review:

- [CAMBIOS_IMPLEMENTADOS.md](CAMBIOS_IMPLEMENTADOS.md)

After applying new view/config changes, you can clear cache with:

```bash
php artisan optimize:clear
```