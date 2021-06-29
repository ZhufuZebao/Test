<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapStatefulApiRoutes();    // ★追加

        $this->mapAdminApiRoutes();// ★管理者追加

        $this->mapAdminWebRoutes();// ★管理者追加
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    // ★↓↓↓↓↓追加
    /**
     * Define the "statefulApi" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapStatefulApiRoutes()
    {
        Route::prefix('api')
             ->middleware('stateful_api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
// ★↑↑↑↑↑追加

    /**
     * Define the "adminApi" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapAdminApiRoutes()
    {
        Route::prefix('api')
            ->middleware('admin')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminApi.php'));
    }

    /**
     * Define the "adminWeb" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapAdminWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/adminWeb.php'));
    }
}
