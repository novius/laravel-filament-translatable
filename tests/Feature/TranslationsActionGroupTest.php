<?php

use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Novius\LaravelFilamentTranslatable\Filament\Actions\TranslationsActionGroup;
use Novius\LaravelTranslatable\Support\TranslatableModelConfig;
use Workbench\App\Filament\Resources\Posts\Pages\EditPost;
use Workbench\App\Filament\Resources\Posts\Pages\ViewPost;
use Workbench\App\Filament\Resources\Posts\PostResource;
use Workbench\App\Models\Post;
use Workbench\App\Models\User;

class MonolingualPost extends Post
{
    protected $table = 'posts';

    public function translatableConfig(): TranslatableModelConfig
    {
        return new TranslatableModelConfig(['fr']);
    }
}

/**
 * Every ability granted, except the update of the English posts and, when $canCreate is false, the creation.
 */
class TranslationsActionGroupPostPolicy
{
    public static bool $canCreate = true;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return self::$canCreate;
    }

    public function update(User $user, Post $post): bool
    {
        return $post->locale !== 'en';
    }

    public function delete(User $user, Post $post): bool
    {
        return true;
    }
}

beforeEach(function () {
    $this->actingAsAdmin();
});

afterEach(function () {
    TranslationsActionGroupPostPolicy::$canCreate = true;
});

function createPost(string $locale = 'fr', ?Post $parent = null): Post
{
    return Post::query()->create([
        'title' => 'Post '.$locale,
        'publication_status' => 'published',
        'locale' => $locale,
        'locale_parent_id' => $parent?->id,
    ]);
}

it('opens an existing translation on the edit page', function () {
    $fr = createPost();
    $en = createPost('en', $fr);

    Livewire::test(EditPost::class, ['record' => $fr->getRouteKey()])
        ->assertActionVisible('openTranslation_en')
        ->assertActionHasLabel('openTranslation_en', 'Anglais')
        ->assertActionHasUrl('openTranslation_en', PostResource::getUrl('edit', ['record' => $en]))
        ->assertActionHidden('createTranslation_en')
        ->assertSee('Traductions');
});

it('does not list the locale of the record', function () {
    $fr = createPost();
    createPost('en', $fr);

    Livewire::test(EditPost::class, ['record' => $fr->getRouteKey()])
        ->assertActionHidden('openTranslation_fr')
        ->assertActionHidden('createTranslation_fr');
});

it('creates a missing translation with the create page', function () {
    $fr = createPost();

    Livewire::test(EditPost::class, ['record' => $fr->getRouteKey()])
        ->assertActionHidden('openTranslation_en')
        ->assertActionVisible('createTranslation_en')
        ->assertActionHasLabel('createTranslation_en', 'Traduire en Anglais')
        ->assertActionHasUrl(
            'createTranslation_en',
            PostResource::getUrl('create', ['locale_parent' => $fr->getRouteKey(), 'locale' => 'en']),
        );
});

it('opens the original from a translation', function () {
    $fr = createPost();
    $en = createPost('en', $fr);

    Livewire::test(EditPost::class, ['record' => $en->getRouteKey()])
        ->assertActionHidden('openTranslation_en')
        ->assertActionHasUrl('openTranslation_fr', PostResource::getUrl('edit', ['record' => $fr]));
});

it('opens a translation on the view page from a view page', function () {
    $fr = createPost();
    $en = createPost('en', $fr);

    Livewire::test(ViewPost::class, ['record' => $fr->getRouteKey()])
        ->assertActionHasUrl('openTranslation_en', PostResource::getUrl('view', ['record' => $en]));
});

it('opens a translation on the view page when the user cannot edit it', function () {
    Gate::policy(Post::class, TranslationsActionGroupPostPolicy::class);
    $fr = createPost();
    $en = createPost('en', $fr);

    Livewire::test(EditPost::class, ['record' => $fr->getRouteKey()])
        ->assertActionHasUrl('openTranslation_en', PostResource::getUrl('view', ['record' => $en]));
});

it('does not create a translation when the user cannot create', function () {
    TranslationsActionGroupPostPolicy::$canCreate = false;
    Gate::policy(Post::class, TranslationsActionGroupPostPolicy::class);
    $fr = createPost();

    Livewire::test(EditPost::class, ['record' => $fr->getRouteKey()])
        ->assertActionHidden('createTranslation_en')
        // Without any action to show
        ->assertDontSee('Traductions');
});

it('is hidden for a model with less than two locales, or not translatable', function () {
    expect(TranslationsActionGroup::forModel(MonolingualPost::class)->isHidden())->toBeTrue()
        ->and(TranslationsActionGroup::forModel(User::class)->isHidden())->toBeTrue();
});
