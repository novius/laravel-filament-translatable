<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Tables\Columns;

use Exception;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\Column;
use Illuminate\Database\Eloquent\Model;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;

class TranslationsColumn extends Column
{
    use IsTranslatable;

    protected string $view = 'laravel-filament-translatable::translations';

    /**
     * @var array<int, string>|null
     */
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
            $config = $this->modelTranslatableConfig();
            $livewire = $this->getLivewire();
            $resource = $livewire instanceof Page ? $livewire::getResource() : Filament::getModelResource($record);
            $translations = [];
            if ($config !== null) {
                foreach ($this->locales ?? $config->available_locales as $locale) {
                    $translation = $this->translationOf($record, $locale);
                    if (($this->withoutMissing && $translation === null) || ($this->onlyMissing && $translation !== null)) {
                        continue;
                    }

                    $deleted = $this->isDeletedTranslation($translation);
                    $translations[$locale] = [
                        'exists' => $translation !== null,
                        'deleted' => $deleted,
                        'label' => $deleted ?
                            trans('laravel-filament-translatable::messages.translation_deleted', ['locale' => $this->localeLabel($locale)]) :
                            $this->localeLabel($locale),
                        'flag' => $this->flagUrl($locale),
                        'url' => $translation !== null ?
                            $this->translationUrl($resource, $translation) :
                            $this->createTranslationUrl($resource, $record, $locale),
                    ];
                }
            }

            return [
                'flagWidth' => $this->flagWidth,
                'translations' => $translations,
            ];
        });
        $this->hidden($this->getHiddenClosure($this->locales));
    }

    /**
     * @param  array<int, string>  $locales
     */
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
