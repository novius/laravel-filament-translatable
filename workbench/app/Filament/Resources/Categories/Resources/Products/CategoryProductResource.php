<?php

namespace Workbench\App\Filament\Resources\Categories\Resources\Products;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Workbench\App\Filament\Resources\Categories\CategoryResource;
use Workbench\App\Filament\Resources\Products\ProductResource;
use Workbench\App\Models\Product;

/**
 * The products of a category, nested in CategoryResource: a product is created through the `products` relationship of
 * its category.
 */
class CategoryProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $parentResource = CategoryResource::class;

    public static function form(Schema $schema): Schema
    {
        return ProductResource::form($schema);
    }

    public static function getPages(): array
    {
        return [
            'create' => Pages\CreateCategoryProduct::route('/create'),
            'edit' => Pages\EditCategoryProduct::route('/{record}/edit'),
        ];
    }
}
