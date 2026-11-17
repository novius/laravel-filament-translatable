<?php

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Novius\LaravelFilamentTranslatable\LaravelFilamentTranslatableServiceProvider;

it('loads the translations of the package', function () {
    expect(trans('laravel-filament-translatable::messages.translations'))
        ->not->toBe('laravel-filament-translatable::messages.translations');
});

it('loads the views of the package', function () {
    expect(View::exists('laravel-filament-translatable::locale'))->toBeTrue()
        ->and(View::exists('laravel-filament-translatable::translations'))->toBeTrue();
});

it('publishes the lang files with the lang tag', function () {
    expect(ServiceProvider::pathsToPublish(LaravelFilamentTranslatableServiceProvider::class, 'lang'))
        ->toContain(lang_path('vendor/laravel-filament-translatable'));
});

it('publishes the flags with the public tag', function () {
    expect(ServiceProvider::pathsToPublish(LaravelFilamentTranslatableServiceProvider::class, 'public'))
        ->toContain(public_path('vendor/laravel-filament-translatable/images'));
});
