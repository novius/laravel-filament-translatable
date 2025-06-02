<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Tables\Filters;

use Filament\Tables\Filters\SelectFilter;
use LaravelLang\Locales\Data\LocaleData;
use LaravelLang\Locales\Facades\Locales;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;

class LocaleFilter extends SelectFilter
{
    use IsTranslatable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->options(function () {
            $model = $this->translatableModel();

            return Locales::installed()
                ->filter(fn (LocaleData $locale) => $model === null || in_array($locale->code, $model->translatableConfig()->available_locales, true))
                ->mapWithKeys(fn (LocaleData $locale) => [$locale->code => $locale->localized]);
        });

        $this->label(trans('laravel-filament-translatable::messages.language'));
        $this->multiple();
        $this->hidden(Locales::installed()->count() < 2 ? true : $this->getHiddenClosure());
    }
}
