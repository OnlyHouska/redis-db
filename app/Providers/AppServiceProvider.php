<?php

namespace App\Providers;

use App\Auth\AuthContext;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

/**
 * Application Service Provider
 * 
 * Registers core application services and bindings.
 * Sets up AuthContext as a scoped dependency that must be initialized by middleware.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services
     * 
     * Registers AuthContext as a scoped service. This ensures that:
     * - Each request gets its own AuthContext instance
     * - The instance is shared across the request lifecycle
     * - It must be initialized by JwtAuthMiddleware before use
     */
    public function register(): void
    {
        // Register AuthContext as scoped (per-request) dependency
        // Throws exception if accessed before middleware initializes it
        $this->app->scoped(AuthContext::class, function () {
            throw new RuntimeException('AuthContext not initialized');
        });
    }

    /**
     * Bootstrap application services
     * 
     * Called after all services are registered
     */
    public function boot(): void {}
}
