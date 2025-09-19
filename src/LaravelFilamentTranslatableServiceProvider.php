<?php

namespace Novius\LaravelFilamentTranslatable;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class LaravelFilamentTranslatableServiceProvider extends ServiceProvider
{
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-filament-translatable');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-filament-translatable');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/laravel-filament-translatable'),
        ], 'lang');

        $this->publishes([
            __DIR__.'/../resources/images' => public_path('vendor/laravel-filament-translatable/images'),
        ], 'public');
    }
}
