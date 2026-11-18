---
name: laravel-filament-translatable-development
description: >
  Manage translatable Eloquent models (novius/laravel-translatable) in Filament with novius/laravel-filament-translatable:
  locale field, locale and translations columns, locale filter, translations button of the view and edit pages, and
  creating a translation from the back office. Use
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
  a translations button on its view and edit pages, and a create page that creates a translation of an existing record

## Workflow

### 1. Publish the flags

The locale and translations columns, and the translations button, show flags:

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

`TranslationsColumn` links each translation to its edit page (its view page when the user cannot edit it), and each
missing locale to the create page with `?locale_parent=<id>&locale=<locale>` when the user can create; the policies
of the model decide.

### 4. Create page

Extend the package's `CreateRecord` so the create page fills the form from the parent when the `locale_parent` and
`locale` query parameters are given:

```php
use Illuminate\Database\Eloquent\Model;
use Novius\LaravelFilamentTranslatable\Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    // Optional: the attributes copied from the parent (by default, all of them)
    protected function copiedOnTranslate(): ?array
    {
        return ['title', 'content'];
    }
}
```

`getDataFromTranslate()` builds the data of a translation from `copiedOnTranslate()`; a translation of a publishable
model (laravel-publishable) starts as a draft, without the publication dates of its parent, unless
`translationStartsAsDraft()` returns `false`.

### 5. View and edit pages: translations button

```php
use Filament\Resources\Pages\EditRecord;
use Novius\LaravelFilamentTranslatable\Filament\Resources\Pages\Concerns\HasTranslationsActionGroup;

class EditPost extends EditRecord
{
    use HasTranslationsActionGroup;

    protected function getHeaderActions(): array
    {
        return [
            $this->translationsActionGroup(), // or TranslationsActionGroup::forModel(Post::class)
        ];
    }
}
```

The `Translations` dropdown opens the other translations (view page from a view page, edit page otherwise, as
authorized), then offers the missing ones on the create page of step 4 (when the user can create). It is a Filament
`ActionGroup`: customize it with `->label()`, `->icon()`, `->iconButton()`.

### 6. Common record (HasCommonRecord)

For a translatable model whose shared data is in a common record (`Product` + `ProductCommon`, laravel-translatable
2.1), put the fields of the common record in a `CommonRecordSection` (bound to `commonRecord`):

```php
use Novius\LaravelFilamentTranslatable\Filament\Forms\Components\CommonRecordSection;

CommonRecordSection::make()->schema([
    TextInput::make('reference')->required(), // a column of product_commons
]),
```

With the package's `CreateRecord`, an original saves its common record from these fields before itself (required
columns of the common table included), in a database transaction; several `CommonRecordSection` of a form are saved
together; a hidden or disabled section (`->hiddenOn('create')`) is not saved, `createCommonRecord()` of the model then
creates the common record. A translation shares the common record of its parent (the section shows a notice), and
editing updates the common record of all the translations.

## Examples

- Add a translations column with only the missing locales: `TranslationsColumn::make('translations')->onlyMissing()`.
- Add the translations button to a view page: the same trait and `translationsActionGroup()` on a `ViewRecord`.
- Restrict the locales of a model: override `translatableConfig()` on the model; the field, the columns and the filter
  follow its `available_locales`.

## Anti-Patterns

- Extending Filament's `CreateRecord` instead of the package's one: the translation links open an empty form.
- Building the group with `TranslationsActionGroup::make([...])`: use `forModel()` or the trait, which add an action
  per locale.
- Forgetting `Hidden::make('locale_parent_id')`: the new record is not linked to its group of translations.
- Copying unique attributes (slug) of the parent as is: leave them out of `copiedOnTranslate()`, or compute them in
  `getDataFromTranslate()`.
- Overriding `getDataFromTranslate()` without calling `parent::getDataFromTranslate()`: `copiedOnTranslate()` and the
  draft of a publishable model are ignored.
- Putting the fields of the common record in a plain `Section::make()->relationship(...)`: Filament saves it after the
  translation, too late for a required column of the common table.
- Adding a `CommonRecordSection` while groups of translations created before `HasCommonRecord` have no common record:
  editing one of their translations creates a common record for it only, refused (`common_record_mismatch`). Migrate
  the existing groups first.
- Declaring `commonRecord()` as `belongsTo(ProductCommon::class)` without the foreign key: guessed from the name of the
  method, it would be `common_record_id`.
