<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Actions;

use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;
use Novius\LaravelTranslatable\Support\TranslatableModelConfig;

/**
 * A dropdown button for the header of the view or edit page of a translatable model: it opens the other translations
 * of the record, then creates the missing ones (with the create page of the package).
 *
 * - An existing translation opens on the view page from a view page, on the edit page otherwise, or on the other one
 *   when the first is missing or not authorized. A deleted translation (soft deletes) is marked as such.
 * - A missing translation is created when the resource has a create page and the user can create.
 * - The locale of the record is not listed; the group is hidden for a model with less than two locales.
 */
class TranslationsActionGroup extends ActionGroup
{
    use IsTranslatable;

    protected ?TranslatableModelConfig $translatableConfig = null;

    /**
     * The group of the given translatable model, as the HasTranslationsActionGroup trait of a page gives it.
     *
     * @param  class-string<Model>  $modelClass
     *
     * @throws Exception
     */
    public static function forModel(string $modelClass): static
    {
        return static::make([])->translationsOf($modelClass);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('laravel-filament-translatable::messages.translations'));
        $this->icon(Heroicon::OutlinedLanguage);
        $this->color('gray');
        $this->button();
    }

    /**
     * An action per locale of the model to open its translation, then another to create it.
     *
     * @param  class-string<Model>  $modelClass
     *
     * @throws Exception
     */
    protected function translationsOf(string $modelClass): static
    {
        $this->translatableConfig = $this->modelTranslatableConfig($modelClass);
        $locales = $this->translatableConfig->available_locales ?? [];

        $this->actions([
            ActionGroup::make(array_map($this->openTranslationAction(...), $locales))->dropdown(false),
            ActionGroup::make(array_map($this->createTranslationAction(...), $locales))->dropdown(false),
        ]);
        $this->hidden(count($locales) < 2);

        return $this;
    }

    protected function openTranslationAction(string $locale): Action
    {
        return Action::make('openTranslation_'.$this->localeKey($locale))
            ->label(fn (?Model $record): string => $this->isDeletedTranslation($this->otherTranslation($record, $locale)) ?
                trans('laravel-filament-translatable::messages.translation_deleted', ['locale' => $this->localeLabel($locale)]) :
                $this->localeLabel($locale))
            ->icon($this->flagIcon($locale))
            ->url(fn (?Model $record, mixed $livewire): ?string => $this->openTranslationUrl($record, $livewire, $locale))
            ->hidden(fn (?Model $record, mixed $livewire): bool => $this->openTranslationUrl($record, $livewire, $locale) === null);
    }

    protected function createTranslationAction(string $locale): Action
    {
        return Action::make('createTranslation_'.$this->localeKey($locale))
            ->label(trans('laravel-filament-translatable::messages.translate_into', ['locale' => $this->localeLabel($locale)]))
            ->icon($this->flagIcon($locale))
            ->color('gray')
            ->url(fn (?Model $record, mixed $livewire): ?string => $this->missingTranslationUrl($record, $livewire, $locale))
            ->hidden(fn (?Model $record, mixed $livewire): bool => $this->missingTranslationUrl($record, $livewire, $locale) === null);
    }

    /**
     * The URL of the translation of the record in another locale, on the view page from a view page.
     */
    protected function openTranslationUrl(?Model $record, mixed $livewire, string $locale): ?string
    {
        $translation = $this->otherTranslation($record, $locale);
        if ($translation === null || ! $livewire instanceof Page) {
            return null;
        }

        return $this->translationUrl($livewire::getResource(), $translation, preferView: $livewire instanceof ViewRecord);
    }

    /**
     * The URL creating the translation of the record in another locale, when it is missing (deleted ones included).
     */
    protected function missingTranslationUrl(?Model $record, mixed $livewire, string $locale): ?string
    {
        if ($record === null || ! $this->isOtherLocale($record, $locale) || ! $livewire instanceof Page
            || $this->translationOf($record, $locale) !== null) {
            return null;
        }

        return $this->createTranslationUrl($livewire::getResource(), $record, $locale);
    }

    /**
     * The translation of the record in another locale: null for the locale of the record.
     */
    protected function otherTranslation(?Model $record, string $locale): ?Model
    {
        return $record !== null && $this->isOtherLocale($record, $locale) ? $this->translationOf($record, $locale) : null;
    }

    protected function isOtherLocale(Model $record, string $locale): bool
    {
        return $this->translatableConfig !== null
            && $record->getAttribute($this->translatableConfig->locale_column) !== $locale;
    }

    protected function flagIcon(string $locale): Htmlable
    {
        return new HtmlString('<img src="'.e($this->flagUrl($locale)).'" alt="" style="width: 100%; height: 100%; object-fit: contain;" />');
    }

    /**
     * The locale in the name of an action: letters, digits and underscores.
     */
    protected function localeKey(string $locale): string
    {
        return (string) preg_replace('/\W/', '_', $locale);
    }
}
