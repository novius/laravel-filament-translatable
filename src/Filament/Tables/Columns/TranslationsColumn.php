<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Tables\Columns;

use Exception;
use Filament\Facades\Filament;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\ViewColumn;
use Illuminate\Database\Eloquent\Model;
use LaravelLang\Locales\Facades\Locales;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;

class TranslationsColumn extends Column
{
    use IsTranslatable;

    protected string $view = 'laravel-filament-translatable::translations';

    protected ?array $locales = null;

    protected int $flagWidth = 18;

    protected bool $onlyMissing = false;

    protected bool $withoutMissing = false;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('laravel-filament-translatable::messages.translations'));
        $this->viewData(function (Model $record) {
            $model = $this->translatableModel();
            $translations = collect();
            if ($model !== null) {
                $translations = [];
                $locales = $this->locales ?? $model->translatableConfig()->available_locales;
                foreach ($locales as $locale) {
                    $translation = $locale === $record->{$model->translatableConfig()->locale_column} ?
                        $record :
                        $record->translationsWithDeleted->firstWhere($model->translatableConfig()->locale_column, $locale);

                    if (($this->withoutMissing && $translation) || ($this->onlyMissing && $translation === null) ||
                        (! $this->withoutMissing && ! $this->onlyMissing)
                    ) {
                        $translations[$locale] = $translation;
                    }
                }
            }

            return [
                'resource' => Filament::getModelResource($record),
                'record' => $record,
                'flagWidth' => $this->flagWidth,
                'translations' => $translations,
                'locales' => Locales::installed(),
            ];
        });
        $this->hidden($this->getHiddenClosure($this->locales));
    }

    public function locales(array $locales): static
    {
        $this->locales = $locales;

        return $this;
    }

    public function flagWidth(int $width): static
    {
        $this->flagWidth = $width;

        return $this;
    }

    public function onlyMissing(): static
    {
        $this->onlyMissing = true;

        return $this;
    }

    public function withoutMissing(): static
    {
        $this->withoutMissing = true;

        return $this;
    }
}
