<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as FilamentLoginResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as FilamentLogoutResponse;
use App\Http\Responses\LoginResponse as CustomLoginResponse;
use App\Http\Responses\LogoutResponse as CustomLogoutResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind your custom class to override Filament's internal login redirection handler
        $this->app->singleton(FilamentLoginResponse::class, CustomLoginResponse::class);

        // Bind your custom class to override Filament's internal logout redirection handler
        $this->app->singleton(FilamentLogoutResponse::class, CustomLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
