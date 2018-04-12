<?php

namespace Vjsoft\Imapclient\Providers;

use Illuminate\Support\ServiceProvider;

use Vjsoft\Imapclient\Controllers\ImapClientPackageManager;
use Vjsoft\Imapclient\Controllers\Client;

class ImapClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../\config/imap.php' => config_path('imap.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ImapClientPackageManager::class, function ($app) {
            return new ImapClientPackageManager($app);
        });

        $this->app->singleton(Client::class, function ($app) {
            return $app[ImapClientPackageManager::class]->account();
        });



    }
}
