# Laravel Filament Translatable

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](http://www.gnu.org/licenses/agpl-3.0)

## Introduction 

This package allows you to manage Laravel Models that use [Laravel Translatable](https://github.com/novius/laravel-translatable) in [Laravel Filament](https://filamentphp.com/).

## Requirements

* Laravel Filament >= 4
* Laravel >= 11.0 (tested with Laravel 12 and 13)
* PHP >= 8.2

## Installation

You can install the package via composer:

```bash
composer require novius/laravel-filament-translatable
```

## Assets

Next we need to publish the package's assets. We do this by running the following command:

```sh
php artisan vendor:publish --provider="Novius\LaravelFilamentTranslatable\LaravelFilamentTranslatableServiceProvider" --tag="public"
```

## Lang files

If you want to customize the lang files, you can publish them with:

```bash
php artisan vendor:publish --provider="Novius\LaravelFilamentTranslatable\LaravelFilamentTranslatableServiceProvider" --tag="lang"
```

## Locale Filter

Add the `LocaleFilter` filter on your Filament Resource.

```php
use Filament\Resources\Resource;
use Novius\LaravelFilamentTranslatable\Filament\Tables\Filters\LocaleFilter;

class Post extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            /// ...
            ->filters([
                LocaleFilter::make('locale'),
                /// ...
            ]);
    }
}
```

## Locale Column

Add the `LocaleColumn` column on your Filament Resource.

```php
use Filament\Resources\Resource;
use Novius\LaravelFilamentTranslatable\Filament\Tables\Columns\LocaleColumn;

class Post extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            /// ...
            ->columns([
                /// ...
                LocaleColumn::make('locale'),
            ]);
    }
}
```

## Locale Input

Add the `Locale` input on your Filament Resource.

```php
use Filament\Resources\Resource;
use Novius\LaravelFilamentTranslatable\Filament\Forms\Components\Locale;

class Post extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ...
                Locale::make('locale')
                    ->required(),
                // ...
            ]);
    }
}
```

## Translations Column and features

In your Filament Resource.

```php
use Filament\Resources\Resource;
use Novius\LaravelFilamentTranslatable\Filament\Tables\Columns\TranslationsColumn;

class Post extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('locale_parent_id'),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $table
            // ...
            ->columns([
                // ...
                TranslationsColumn::make('translations')
                    ->locales(['de', 'en']) // Optional. Restricted the locals displayed on this list
                    ->flagWidth(16) // Optional. Width in pixel of the flags
                    ->onlyMissing() // Optional. Will display only translations missing
                    ->withoutMissing() // Optional. Will display only existing translations
                    ,
                // ...
            ]);
    }
}
```

And declare your Create page like this :

```php
<?php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LaravelLang\Locales\Facades\Locales;
use Novius\LaravelFilamentPageManager\Filament\PageManagerPlugin;
use Novius\LaravelFilamentTranslatable\Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    public static function getResource(): string
    {
        return PageManagerPlugin::getPlugin()->getResource('PostResource');
    }

    protected function getDataFromTranslate(Model $parent, string $locale): array
    {
        // The attributes of copiedOnTranslate(), as a draft for a publishable model
        $data = parent::getDataFromTranslate($parent, $locale);

        // Modify data according the parent
        $data['title'] = $parent->title.' '.Locales::get($locale)->native;
        $data['slug'] = Str::slug($data['title']);

        return $data;
    }
}
```

By default, a translation gets all the attributes of its parent. List the attributes to copy instead with
`copiedOnTranslate()`:

```php
class CreatePost extends CreateRecord
{
    protected function copiedOnTranslate(): ?array
    {
        return ['title', 'content'];
    }
}
```

Do not copy a unique attribute (a slug) as is: leave it out of `copiedOnTranslate()`, or compute it in
`getDataFromTranslate()` as above.

A translation of a publishable model (`Publishable` trait of
[Laravel Publishable](https://github.com/novius/laravel-publishable)) starts as a draft, without the publication dates of
its parent: it is not published before being reviewed. To copy the publication of the parent instead:

```php
class CreatePost extends CreateRecord
{
    protected function translationStartsAsDraft(): bool
    {
        return false;
    }
}
```

## Common record

For a model whose data shared by all the locales is held by a common record (`HasCommonRecord` and `IsCommonRecord` of
[Laravel Translatable](https://github.com/novius/laravel-translatable)), the resource is the one of the translatable
model (`Product`), and the `CommonRecordSection` holds the fields of the common record (`ProductCommon`), bound to its
`commonRecord` relationship:

```php
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Novius\LaravelFilamentTranslatable\Filament\Forms\Components\CommonRecordSection;
use Novius\LaravelFilamentTranslatable\Filament\Forms\Components\Locale;

class ProductResource extends Resource
{
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            Locale::make('locale')->required(),
            Hidden::make('locale_parent_id'),
            CommonRecordSection::make()->schema([
                TextInput::make('reference')->required(),
                // ... the fields of the common record
            ]),
        ]);
    }
}
```

With the `CreateRecord` page of the package:

* Creating an original saves the common record from the fields of the section first, then the original with its key,
  in a database transaction: the common record gets every value of the form, required ones included, and
  `createCommonRecord()` of the model is not called. The fields of the common record can be split into several
  `CommonRecordSection` (`CommonRecordSection::make('Logistics')`), saved together.
* A hidden or disabled section (`->hiddenOn('create')`, for instance) is not saved: the model creates its common record
  with `createCommonRecord()`, and the section edits it once the original is created.
* Creating a translation shares the common record of its parent: the section shows a notice instead of its fields, and
  saves nothing.
* Editing a translation updates the common record, so all the translations.

> [!WARNING]
> A group of translations created before `HasCommonRecord` was added to the model has no common record. Editing one of
> its translations with a `CommonRecordSection` creates a common record for this translation only, which
> laravel-translatable refuses (a `TranslatableException`, `common_record_mismatch`). A model not translated yet simply
> gets its common record. Give a common record to the existing groups (a migration) before adding the section.

## Testing

Run the linter, the static analysis and the tests with:

```bash
composer test
```

## Lint

Lint your code with Laravel Pint using:

```bash
composer lint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Licence

This package is under [GNU Affero General Public License v3](http://www.gnu.org/licenses/agpl-3.0.html) or (at your option) any later version.
