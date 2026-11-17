<?php

namespace Novius\LaravelFilamentTranslatable\Tests;

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use Workbench\App\Models\User;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;
    use WithWorkbench;

    /**
     * The installed packages are discovered: laravel-translatable and laravel-publishable (their Blueprint macros),
     * Filament, Livewire.
     *
     * @return array<int, string>
     */
    public function ignorePackageDiscoveriesFrom(): array
    {
        return [];
    }

    protected function defineEnvironment($app): void
    {
        // The Locale field offers the installed locales (laravel-lang), the application and fallback ones included:
        // no lang files are needed
        $app['config']->set('app.locale', 'fr');
        $app['config']->set('app.fallback_locale', 'en');
    }

    /**
     * Logged in the admin panel of the workbench.
     */
    protected function actingAsAdmin(): static
    {
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $this;
    }
}
