<?php

use Livewire\Livewire;
use Workbench\App\Filament\Resources\Posts\Pages\ListPosts;
use Workbench\App\Models\Post;

beforeEach(function () {
    $this->actingAsAdmin();
});

it('shows the flag and the name of the locale of the record', function () {
    Post::query()->create(['title' => 'Hello', 'publication_status' => 'published', 'locale' => 'en']);

    Livewire::test(ListPosts::class)
        ->assertSeeHtml('vendor/laravel-filament-translatable/images/flags/en.svg')
        ->assertSeeHtml('alt="Anglais"');
});
