<?php

use Livewire\Livewire;
use Novius\LaravelPublishable\Enums\PublicationStatus;
use Workbench\App\Filament\Resources\Posts\Pages\CreatePost;
use Workbench\App\Filament\Resources\Products\Pages\CreateProduct;
use Workbench\App\Models\Post;
use Workbench\App\Models\Product;
use Workbench\App\Models\ProductCommon;

class CreatePostCopyingPublication extends CreatePost
{
    protected function translationStartsAsDraft(): bool
    {
        return false;
    }
}

beforeEach(function () {
    $this->actingAsAdmin();
});

it('creates an original of a model without common record', function () {
    Livewire::test(CreatePost::class)
        ->fillForm(['title' => 'Bonjour', 'publication_status' => 'published', 'locale' => 'fr'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->sole())
        ->title->toBe('Bonjour')
        ->locale->toBe('fr')
        ->locale_parent_id->toBeNull();
});

it('creates a translation of a model without common record', function () {
    $fr = Post::query()->create(['title' => 'Bonjour', 'publication_status' => 'published', 'locale' => 'fr']);

    Livewire::withQueryParams(['locale_parent' => $fr->id, 'locale' => 'en'])
        ->test(CreatePost::class)
        ->fillForm(['title' => 'Hello'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->where('locale', 'en')->sole()->locale_parent_id)->toBe($fr->id)
        ->and($fr->fresh()?->locale_parent_id)->toBe($fr->id);
});

it('fills the form of a translation with the attributes of its parent', function () {
    $fr = Post::query()->create(['title' => 'Bonjour', 'publication_status' => 'published', 'locale' => 'fr']);

    Livewire::withQueryParams(['locale_parent' => $fr->id, 'locale' => 'en'])
        ->test(CreatePost::class)
        ->assertSchemaStateSet(['title' => 'Bonjour', 'locale' => 'en', 'locale_parent_id' => $fr->id]);
});

it('starts a translation of a publishable model as a draft, without the publication dates of its parent', function () {
    $fr = Post::query()->create(['title' => 'Bonjour', 'publication_status' => 'published', 'locale' => 'fr']);
    expect($fr->published_first_at)->not->toBeNull();

    Livewire::withQueryParams(['locale_parent' => $fr->id, 'locale' => 'en'])
        ->test(CreatePost::class)
        ->assertSchemaStateSet(['publication_status' => PublicationStatus::draft, 'published_first_at' => null])
        ->fillForm(['title' => 'Hello'])
        ->call('create')
        ->assertHasNoFormErrors();

    // With the first publication date of its parent, laravel-publishable would save it as unpublished
    expect(Post::query()->where('locale', 'en')->sole())
        ->publication_status->toBe(PublicationStatus::draft)
        ->published_first_at->toBeNull()
        ->published_at->toBeNull()
        ->expired_at->toBeNull();
});

it('copies the publication of the parent when translationStartsAsDraft() returns false', function () {
    $fr = Post::query()->create(['title' => 'Bonjour', 'publication_status' => 'published', 'locale' => 'fr']);

    Livewire::withQueryParams(['locale_parent' => $fr->id, 'locale' => 'en'])
        ->test(CreatePostCopyingPublication::class)
        ->assertSchemaStateSet(['publication_status' => PublicationStatus::published]);
});

it('copies only the attributes listed by copiedOnTranslate()', function () {
    $common = ProductCommon::query()->create(['reference' => 'SV-3']);
    $fr = Product::query()->create(['title' => 'Store', 'subtitle' => 'Toile', 'locale' => 'fr', 'product_common_id' => $common->id]);

    Livewire::withQueryParams(['locale_parent' => $fr->id, 'locale' => 'en'])
        ->test(CreateProduct::class)
        ->assertSchemaStateSet(['title' => 'Store', 'subtitle' => null]);
});

it('ignores a locale that is not available', function () {
    $fr = Post::query()->create(['title' => 'Bonjour', 'publication_status' => 'published', 'locale' => 'fr']);

    Livewire::withQueryParams(['locale_parent' => $fr->id, 'locale' => 'de'])
        ->test(CreatePost::class)
        ->assertSchemaStateSet(['title' => null, 'locale_parent_id' => null]);
});
