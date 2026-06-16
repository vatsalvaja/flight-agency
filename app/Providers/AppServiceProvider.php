<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        try {
            if (Schema::hasTable('settings')) {
                $appSettings = \App\Models\Setting::first() ?? new \App\Models\Setting([
                    'application_name' => 'Wings',
                    'application_logo' => null,
                    'favicon' => null,
                ]);
                view()->share('appSettings', $appSettings);
            } else {
                $appSettings = new \App\Models\Setting([
                    'application_name' => 'Wings',
                    'application_logo' => null,
                    'favicon' => null,
                ]);
                view()->share('appSettings', $appSettings);
            }
        } catch (\Exception $e) {
            $appSettings = new \App\Models\Setting([
                'application_name' => 'Wings',
                'application_logo' => null,
                'favicon' => null,
            ]);
            view()->share('appSettings', $appSettings);
        }
    }
}
