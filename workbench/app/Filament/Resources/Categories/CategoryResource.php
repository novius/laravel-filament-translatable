<?php

namespace Workbench\App\Filament\Resources\Categories;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Novius\LaravelFilamentTranslatable\Filament\Forms\Components\CommonRecordSection;
use Novius\LaravelFilamentTranslatable\Filament\Forms\Components\Locale;
use Workbench\App\Models\Category;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            Locale::make('locale')->required(),
            Hidden::make('locale_parent_id'),
            // Hidden on creation: the common record is created by the model (createCommonRecord())
            CommonRecordSection::make()->hiddenOn('create')->schema([
                TextInput::make('position')->numeric()->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
