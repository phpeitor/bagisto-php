<p align="center">
  <a href="http://www.bagisto.com">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/bagisto/temp-media/0b0984778fae92633f57e625c5494ead1fe320c3/dark-logo-P5H7MBtx.svg">
      <source media="(prefers-color-scheme: light)" srcset="https://bagisto.com/wp-content/themes/bagisto/images/logo.png">
      <img src="https://bagisto.com/wp-content/themes/bagisto/images/logo.png" alt="Bagisto logo">
    </picture>
  </a>
</p>

<p align="center">
    <a href="https://twitter.com/intent/follow?screen_name=bagistoshop"><img src="https://img.shields.io/twitter/follow/bagistoshop?style=social"></a>
    <a href="https://www.youtube.com/channel/UCbrfqnhyiDv-bb9QuZtonYQ"><img src="https://img.shields.io/youtube/channel/subscribers/UCbrfqnhyiDv-bb9QuZtonYQ?style=social"></a>
</p>

# Introduction

Bagisto is an opensource [laravel eCommerce](https://www.bagisto.com/) framework built on some of the hottest technologies such as [Laravel](https://laravel.com/) (a [PHP](https://secure.php.net/) framework) and [Vue.js](https://vuejs.org/) a progressive Javascript framework.

Bagisto can help you cut down your time, cost, and workforce for building online stores or migrating from physical stores to the ever-demanding online world. Your business—whether small or huge—can benefit. The best part, it's straightforward to set it up!

![Repo Stats](https://raw.githubusercontent.com/bagisto/temp-media/master/stats.webp)

# Getting Started

![Getting Started](https://raw.githubusercontent.com/bagisto/temp-media/master/geting-starded.png)

[Install Bagisto](https://devdocs.bagisto.com/2.3/introduction/installation.html#install-using-gui-installer) with or without Composer (Check [Requirement Details](https://bagisto.com/en/download/))

Follow the [Getting Started with Bagisto](https://www.youtube.com/watch?v=s_DhQrjK8Tw&list=PLe30vg_FG4OS3BU8rHUKQZ2mnX45xwSMc) Tutorial

You can browse through the Free [Live Demo](https://demo.bagisto.com/)

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