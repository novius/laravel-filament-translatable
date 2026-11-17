<?php

use Livewire\Livewire;
use Workbench\App\Filament\Resources\Categories\Pages\CreateCategory;
use Workbench\App\Filament\Resources\Categories\Pages\EditCategory;
use Workbench\App\Filament\Resources\Categories\Resources\Products\Pages\CreateCategoryProduct;
use Workbench\App\Filament\Resources\Products\Pages\CreateProduct;
use Workbench\App\Filament\Resources\Products\Pages\EditProduct;
use Workbench\App\Models\Category;
use Workbench\App\Models\CategoryCommon;
use Workbench\App\Models\Product;
use Workbench\App\Models\ProductCommon;

beforeEach(function () {
    $this->actingAsAdmin();
});

function frenchProduct(): Product
{
    $common = ProductCommon::query()->create(['reference' => 'SV-3']);

    return Product::query()->create(['title' => 'Store', 'subtitle' => 'Toile', 'locale' => 'fr', 'product_common_id' => $common->id]);
}

it('saves the common record of an original from the section, before the original', function () {
    // The reference is required by the common table: the common record must be saved with the data of the form, and
    // Product::createCommonRecord() throws
    Livewire::test(CreateProduct::class)
        ->fillForm(['title' => 'Store', 'locale' => 'fr', 'commonRecord.reference' => 'SV-3'])
        ->call('create')
        ->assertHasNoFormErrors();

    $product = Product::query()->sole();
    expect($product->commonRecord?->reference)->toBe('SV-3')
        ->and(ProductCommon::query()->count())->toBe(1);
});

it('saves the fields of all the sections of the common record together', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm(['title' => 'Store', 'locale' => 'fr', 'commonRecord.reference' => 'SV-3', 'commonRecord.weight' => 12])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ProductCommon::query()->sole())
        ->reference->toBe('SV-3')
        ->weight->toBe(12);
});

it('saves the common record of an original of a nested resource, linked to its parent record', function () {
    $category = Category::query()->create(['title' => 'Jardin', 'locale' => 'fr']);

    Livewire::test(CreateCategoryProduct::class, ['parentRecord' => $category])
        ->fillForm(['title' => 'Store', 'locale' => 'fr', 'commonRecord.reference' => 'SV-3'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::query()->sole())
        ->category_id->toBe($category->id)
        ->commonRecord->reference->toBe('SV-3')
        ->and(ProductCommon::query()->count())->toBe(1);
});

it('does not save the common record a second time after the original', function () {
    // Filament would save it again with the data of the form, calling mutateRelationshipDataBeforeSave()
    $saves = 0;
    ProductCommon::saving(function () use (&$saves) {
        $saves++;
    });

    Livewire::test(CreateProduct::class)
        ->fillForm(['title' => 'Store', 'locale' => 'fr', 'commonRecord.reference' => 'SV-3'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect($saves)->toBe(1)
        ->and(ProductCommon::query()->sole()->reference)->toBe('SV-3');
});

it('does not leave a common record when the original fails to be saved', function () {
    Product::creating(fn () => throw new RuntimeException('The product is not saved.'));

    expect(fn () => Livewire::test(CreateProduct::class)
        ->fillForm(['title' => 'Store', 'locale' => 'fr', 'commonRecord.reference' => 'SV-3'])
        ->call('create'))
        ->toThrow(RuntimeException::class, 'The product is not saved.');

    expect(ProductCommon::query()->count())->toBe(0)
        ->and(Product::query()->count())->toBe(0);
});

it('lets the model create the common record when the section is hidden', function () {
    // The section of the categories is hidden on creation: CategoryCommon::$position is filled by createCommonRecord()
    Livewire::test(CreateCategory::class)
        ->assertFormFieldDoesNotExist('commonRecord.position')
        ->fillForm(['title' => 'Jardin', 'locale' => 'fr'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::query()->sole()->commonRecord?->position)->toBe(0)
        ->and(CategoryCommon::query()->count())->toBe(1);
});

it('edits the common record created by the model', function () {
    $category = Category::query()->create(['title' => 'Jardin', 'locale' => 'fr']);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->assertSchemaStateSet(['commonRecord.position' => 0])
        ->fillForm(['commonRecord.position' => 3])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(CategoryCommon::query()->sole()->position)->toBe(3);
});

it('validates the fields of the common record', function () {
    Livewire::test(CreateProduct::class)
        ->fillForm(['title' => 'Store', 'locale' => 'fr', 'commonRecord.reference' => ''])
        ->call('create')
        ->assertHasFormErrors(['commonRecord.reference' => 'required']);

    expect(Product::query()->count())->toBe(0)
        ->and(ProductCommon::query()->count())->toBe(0);
});

it('gives a new translation the common record of its parent, without its fields', function () {
    $fr = frenchProduct();

    Livewire::withQueryParams(['locale_parent' => $fr->id, 'locale' => 'en'])
        ->test(CreateProduct::class)
        ->assertSee(trans('laravel-filament-translatable::messages.common_record_from_parent'))
        ->assertFormFieldDoesNotExist('commonRecord.reference')
        ->assertFormFieldDoesNotExist('commonRecord.weight')
        ->fillForm(['title' => 'Blind'])
        ->call('create')
        ->assertHasNoFormErrors();

    $en = Product::query()->where('locale', 'en')->sole();
    expect($en->product_common_id)->toBe($fr->product_common_id)
        ->and($en->locale_parent_id)->toBe($fr->id)
        ->and(ProductCommon::query()->count())->toBe(1)
        ->and(ProductCommon::query()->sole()->reference)->toBe('SV-3');
});

it('updates the common record of all the translations from the section', function () {
    $fr = frenchProduct();
    $en = $fr->translate('en', ['title' => 'Blind']);

    Livewire::test(EditProduct::class, ['record' => $en->getRouteKey()])
        ->assertSchemaStateSet(['commonRecord.reference' => 'SV-3'])
        ->fillForm(['commonRecord.reference' => 'SV-4'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($fr->fresh()?->commonRecord?->reference)->toBe('SV-4')
        ->and(ProductCommon::query()->count())->toBe(1);
});
