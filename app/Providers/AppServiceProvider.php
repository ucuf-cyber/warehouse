<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('db.connector.pgsql', \App\Database\NeonPostgresConnector::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);

        $this->patchServerEnvForBuiltInDevServer();
    }

    /**
     * PHP's built-in dev server (cli-server SAPI, i.e. `php artisan serve`) does not
     * mirror the OS environment into $_SERVER the way CLI/php-fpm do — only CGI-style
     * request variables end up there. Symfony Process's getDefaultEnv() intersects
     * getenv() against $_SERVER keys on Windows, which silently strips SYSTEMROOT,
     * PATH, TEMP, etc. from any child process it spawns (e.g. mysqldump via
     * spatie/laravel-backup), causing Winsock init failures (WSAEPROVIDERFAILEDINIT).
     * Restoring these keys into $_SERVER keeps them alive through that intersect.
     */
    private function patchServerEnvForBuiltInDevServer(): void
    {
        if (PHP_SAPI !== 'cli-server') {
            return;
        }

        foreach (['SYSTEMROOT', 'WINDIR', 'PATH', 'TEMP', 'TMP', 'COMSPEC'] as $key) {
            if (! array_key_exists($key, $_SERVER) && false !== ($value = getenv($key))) {
                $_SERVER[$key] = $value;
            }
        }
    }
}
