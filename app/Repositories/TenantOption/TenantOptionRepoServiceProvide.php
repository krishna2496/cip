<?php

namespace App\Repositories\TenantOption;

use Illuminate\Support\ServiceProvider;

class TenantOptionRepoServiceProvide extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        
    }


    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Repositories\TenantOption\TenantOptionInterface', 'App\Repositories\TenantOption\TenantOptionRepository');
    }
}