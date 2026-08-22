<?php

namespace Modules\User\App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\User\App\Console\Commands\CleanupPendingRegistrations;

class UserServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('User', 'Database/migrations'));
        $this->loadRoutesFrom(module_path('User', 'routes/web.php'));
        $this->loadViewsFrom(module_path('User', 'resources/views'), 'user');
    }

    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupPendingRegistrations::class,
            ]);
        }
    }
}
