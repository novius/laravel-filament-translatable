<?php

use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Workbench\App\Filament\Resources\Posts\Pages\ListPosts;
use Workbench\App\Filament\Resources\Posts\PostResource;
use Workbench\App\Models\Post;
use Workbench\App\Models\User;

/**
 * Every ability granted, except the update of the English posts and, when $canCreate is false, the creation and, when
 * $canView is false, the view of the English posts.
 */
class TranslationsColumnPostPolicy
{
    public static bool $canCreate = true;

    public static bool $canView = true;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return self::$canView || $post->locale !== 'en';
    }

    public function create(User $user): bool
    {
        return self::$canCreate;
    }

    public function update(User $user, Post $post): bool
    {
        return $post->locale !== 'en';
    }
}

beforeEach(function () {
    $this->actingAsAdmin();
});

afterEach(function () {
    TranslationsColumnPostPolicy::$canCreate = true;
    TranslationsColumnPostPolicy::$canView = true;
});

function columnPost(string $locale = 'fr', ?Post $parent = null): Post
{
    return Post::query()->create([
        'title' => 'Post '.$locale,
        'publication_status' => 'published',
        'locale' => $locale,
        'locale_parent_id' => $parent?->id,
    ]);
}

it('links the record and its translations to their edit page', function () {
    $fr = columnPost();
    $en = columnPost('en', $fr);

    Livewire::test(ListPosts::class)
        ->assertSeeHtml('href="'.e(PostResource::getUrl('edit', ['record' => $fr])).'"')
        ->assertSeeHtml('href="'.e(PostResource::getUrl('edit', ['record' => $en])).'"')
        ->assertSeeHtml('title="Anglais"');
});

it('links a missing translation to the create page', function () {
    $fr = columnPost();

    Livewire::test(ListPosts::class)
        ->assertSeeHtml('href="'.e(PostResource::getUrl('create', ['locale_parent' => $fr->getRouteKey(), 'locale' => 'en'])).'"');
});

it('links a translation to its view page when the user cannot edit it', function () {
    Gate::policy(Post::class, TranslationsColumnPostPolicy::class);
    $fr = columnPost();
    $en = columnPost('en', $fr);

    Livewire::test(ListPosts::class)
        ->assertSeeHtml('href="'.e(PostResource::getUrl('view', ['record' => $en])).'"')
        ->assertDontSeeHtml('href="'.e(PostResource::getUrl('edit', ['record' => $en])).'"');
});

it('does not link a translation the user can neither view nor edit', function () {
    TranslationsColumnPostPolicy::$canView = false;
    Gate::policy(Post::class, TranslationsColumnPostPolicy::class);
    $fr = columnPost();
    $en = columnPost('en', $fr);

    Livewire::test(ListPosts::class)
        ->assertSeeHtml('href="'.e(PostResource::getUrl('edit', ['record' => $fr])).'"')
        ->assertDontSeeHtml('href="'.e(PostResource::getUrl('view', ['record' => $en])).'"')
        ->assertDontSeeHtml('href="'.e(PostResource::getUrl('edit', ['record' => $en])).'"');
});

it('does not link a missing translation when the user cannot create', function () {
    TranslationsColumnPostPolicy::$canCreate = false;
    Gate::policy(Post::class, TranslationsColumnPostPolicy::class);
    $fr = columnPost();

    Livewire::test(ListPosts::class)
        ->assertSeeHtml('href="'.e(PostResource::getUrl('edit', ['record' => $fr])).'"')
        ->assertDontSeeHtml('locale_parent=');
});
