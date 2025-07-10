<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('permission', \App\Http\Middleware\CheckPermission::class);

        // Register blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Register blade directives untuk permission
     */
    private function registerBladeDirectives(): void
    {
        Blade::directive('canAccess', function ($expression) {
            return "<?php if(auth()->user()?->hasPermission({$expression})): ?>";
        });

        Blade::directive('endcanAccess', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('isAdmin', function () {
            return "<?php if(auth()->user()?->isAdmin()): ?>";
        });

        Blade::directive('endisAdmin', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('isLogistik', function () {
            return "<?php if(auth()->user()?->isLogistik()): ?>";
        });

        Blade::directive('endisLogistik', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('isTeknisi', function () {
            return "<?php if(auth()->user()?->isTeknisi()): ?>";
        });

        Blade::directive('endisTeknisi', function () {
            return "<?php endif; ?>";
        });
    }
}
