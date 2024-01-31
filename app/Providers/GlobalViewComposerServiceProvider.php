<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class GlobalViewComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('*', function ($view) {
            $tenant_id = session()->get('tenant_id');
            $config_details = DB::table('config')->where('tenant_id', $tenant_id)->first();
    
            $view->with('config', $config_details);
        });
    }
}
