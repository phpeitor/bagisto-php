<?php

namespace Webkul\Culqi\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\Theme\ViewRenderEventManager;
use Illuminate\Support\Facades\Event;

class CulqiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'culqi');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'culqi');

        Event::listen('bagisto.shop.layout.body.after', static function (ViewRenderEventManager $viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('culqi::checkout.onepage.culqi-checkout');
        });
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/paymentmethods.php', 'payment_methods'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/system.php', 'core'
        );

        $this->app['config']->set('logging.channels.culqi', [
            'driver' => 'single',
            'path'   => storage_path('logs/culqi.log'),
            'level'  => 'debug',
        ]);
    }
}
