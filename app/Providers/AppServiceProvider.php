<?php

namespace App\Providers;

use App\Auth\AuthContext;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(AuthContext::class, function () {
            throw new RuntimeException('AuthContext not initialized');
        });
    }

    public function boot(): void {}
}
