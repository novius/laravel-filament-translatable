<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Traits;

use Closure;
use Exception;
use Filament\Forms\Components\Field;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaravelLang\Locales\Facades\Locales;
use Novius\LaravelTranslatable\Support\TranslatableModelConfig;
use Novius\LaravelTranslatable\Traits\Translatable;

trait IsTranslatable
{
    /**
     * A new instance of the model of the component (a field, a column, a filter), or of the given class, when it uses
     * the Translatable trait.
     *
     * @param  class-string<Model>|null  $modelClass
     *
     * @throws Exception
     */
    protected function translatableModel(?string $modelClass = null): ?Model
    {
        if ($this instanceof Field) {
            $modelClass = $this->getModel();
        } elseif ($this instanceof Column || $this instanceof BaseFilter) {
            $modelClass = $this->getTable()->getModel();
        }
        if ($modelClass !== null && is_a($modelClass, Model::class, true)
            && in_array(Translatable::class, class_uses_recursive($modelClass), true)) {
            return new $modelClass;
        }

        return null;
    }

    /**
     * The translatable config (available locales, columns) of the model of the component, or of the given class, when
     * it uses the Translatable trait.
     *
     * @param  class-string<Model>|null  $modelClass
     *
     * @throws Exception
     */
    protected function modelTranslatableConfig(?string $modelClass = null): ?TranslatableModelConfig
    {
        $model = $this->translatableModel($modelClass);

        return $model !== null && method_exists($model, 'translatableConfig') ? $model->translatableConfig() : null;
    }

    /**
     * The name of the locale in the language of the application, or its code when laravel-lang does not know it.
     */
    protected function localeLabel(string $locale): string
    {
        return Locales::isAvailable($locale) ? Locales::get($locale)->localized : $locale;
    }

    /**
     * The flag of the locale, published with the `public` tag.
     */
    protected function flagUrl(string $locale): string
    {
        return asset('vendor/laravel-filament-translatable/images/flags/'.$locale.'.svg');
    }

    /**
     * The translation of the record in the locale, deleted ones included (soft deletes), the record itself for its
     * locale: null when missing, or for a record of a model not translatable.
     */
    protected function translationOf(Model $record, string $locale): ?Model
    {
        if (! method_exists($record, 'getTranslation')) {
            return null;
        }

        $translation = $record->getTranslation($locale, withDeleted: true);

        return $translation instanceof Model ? $translation : null;
    }

    protected function isDeletedTranslation(?Model $translation): bool
    {
        return $translation !== null
            && in_array(SoftDeletes::class, class_uses_recursive($translation), true)
            && method_exists($translation, 'trashed')
            && $translation->trashed();
    }

    /**
     * The URL of the translation on the edit page of the resource, or on its view page when the edit page is missing
     * or not authorized (the other way round with $preferView): null without the resource (a component outside of a
     * resource page) or any of these pages.
     */
    protected function translationUrl(?string $resource, Model $translation, bool $preferView = false): ?string
    {
        if ($resource === null || ! is_a($resource, Resource::class, true)) {
            return null;
        }

        foreach ($preferView ? ['view', 'edit'] : ['edit', 'view'] as $page) {
            if ($resource::hasPage($page)
                && ($page === 'view' ? $resource::canView($translation) : $resource::canEdit($translation))) {
                // The parent records of a nested resource are guessed from the current route
                return $resource::getUrl($page, ['record' => $translation], shouldGuessMissingParameters: true);
            }
        }

        return null;
    }

    /**
     * The URL of the create page of the resource creating the translation of the record in the locale (with the
     * CreateRecord page of the package): null without the resource, its create page or its authorization.
     */
    protected function createTranslationUrl(?string $resource, Model $record, string $locale): ?string
    {
        if ($resource === null || ! is_a($resource, Resource::class, true)
            || ! $resource::hasPage('create') || ! $resource::canCreate()) {
            return null;
        }

        return $resource::getUrl('create', ['locale_parent' => $record, 'locale' => $locale], shouldGuessMissingParameters: true);
    }

    /**
     * @param  array<int, string>|null  $locales
     */
    protected function getHiddenClosure(?array $locales = null): Closure
    {
        return function () use ($locales) {
            $config = $this->modelTranslatableConfig();
            if ($config !== null && $locales === null) {
                $locales = $config->available_locales;
            }

            return count($locales ?? []) < 2;
        };
    }
}
