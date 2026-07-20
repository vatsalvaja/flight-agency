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
        // Default OCR engine for IndiGo document extraction. Bound to an interface
        // so it can be swapped (e.g. a test fake) without touching the callers.
        $this->app->bind(
            \App\Services\Ocr\OcrEngineInterface::class,
            \App\Services\Ocr\GoogleDocumentAiOcrService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Bind Request User Resolver to read from custom user sessions (enables native Laravel Echo / Broadcast Auth to work)
        $this->app->rebinding('request', function ($app, $request) {
            $request->setUserResolver(function () {
                $userId = session('user_id');
                return $userId ? \App\Models\User::find($userId) : null;
            });
        });

        request()->setUserResolver(function () {
            $userId = session('user_id');
            return $userId ? \App\Models\User::find($userId) : null;
        });

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
