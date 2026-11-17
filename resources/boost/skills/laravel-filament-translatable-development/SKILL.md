---
name: laravel-filament-translatable-development
description: >
  Manage translatable Eloquent models (novius/laravel-translatable) in Filament with novius/laravel-filament-translatable:
  locale field, locale and translations columns, locale filter, and creating a translation from the back office. Use
  when building or changing the Filament resource of a translatable model.
license: MIT
metadata:
  author: Novius
---

# Laravel Filament Translatable

Use this skill when a Filament resource manages a model using the `Translatable` trait of
`novius/laravel-translatable`.

## Primary Goal

- give the resource of a translatable model its locale field, its locale and translations columns, its locale filter,
  and a create page that creates a translation of an existing record

## Workflow

### 1. Publish the flags

The locale and translations columns show flags:

```bash
php artisan vendor:publish --provider="Novius\LaravelFilamentTranslatable\LaravelFilamentTranslatableServiceProvider" --tag="public"
```

### 2. Form: locale and parent

```php
use Filament\Forms\Components\Hidden;
use Novius\LaravelFilamentTranslatable\Filament\Forms\Components\Locale;

public static function form(Schema $schema): Schema
{
    return $schema->components([
        Locale::make('locale')->required(), // the available locales of the model, hidden with one locale
        Hidden::make('locale_parent_id'),   // filled when creating a translation
        // ...
    ]);
}
```

### 3. Table: columns and filter

```php
use Novius\LaravelFilamentTranslatable\Filament\Tables\Columns\LocaleColumn;
use Novius\LaravelFilamentTranslatable\Filament\Tables\Columns\TranslationsColumn;
use Novius\LaravelFilamentTranslatable\Filament\Tables\Filters\LocaleFilter;

$table
    ->columns([
        LocaleColumn::make('locale'),
        TranslationsColumn::make('translations'), // ->locales([...]), ->flagWidth(16), ->onlyMissing(), ->withoutMissing()
    ])
    ->filters([
        LocaleFilter::make('locale'),
    ]);
```

`TranslationsColumn` links each translation to its edit page, and each missing locale to the create page with
`?locale_parent=<id>&locale=<locale>`.

### 4. Create page

Extend the package's `CreateRecord` so the create page fills the form from the parent when the `locale_parent` and
`locale` query parameters are given:

```php
use Illuminate\Database\Eloquent\Model;
use Novius\LaravelFilamentTranslatable\Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    // Optional: the data of the form for a translation (by default, all the attributes of the parent)
    protected function getDataFromTranslate(Model $parent, string $locale): array
    {
        return [...$parent->attributesToArray(), 'slug' => null];
    }
}
```

## Examples

- Add a translations column with only the missing locales: `TranslationsColumn::make('translations')->onlyMissing()`.
- Restrict the locales of a model: override `translatableConfig()` on the model; the field, the columns and the filter
  follow its `available_locales`.

## Anti-Patterns

- Extending Filament's `CreateRecord` instead of the package's one: the translation links open an empty form.
- Forgetting `Hidden::make('locale_parent_id')`: the new record is not linked to its group of translations.
- Copying unique attributes (slug) of the parent in `getDataFromTranslate()` without changing them.
