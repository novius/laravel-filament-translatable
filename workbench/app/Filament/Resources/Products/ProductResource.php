<?php

namespace Workbench\App\Filament\Resources\Products;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Novius\LaravelFilamentTranslatable\Filament\Forms\Components\CommonRecordSection;
use Novius\LaravelFilamentTranslatable\Filament\Forms\Components\Locale;
use Novius\LaravelFilamentTranslatable\Filament\Tables\Columns\TranslationsColumn;
use Workbench\App\Models\Product;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            TextInput::make('subtitle'),
            Locale::make('locale')->required(),
            Hidden::make('locale_parent_id'),
            CommonRecordSection::make()->schema([
                TextInput::make('reference')->required(),
            ]),
            // A second section of the common record: saved with the first one
            CommonRecordSection::make('Logistics')->schema([
                TextInput::make('weight')->numeric(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title'),
            TranslationsColumn::make('translations'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
