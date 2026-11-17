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
            $config = $this->modelTranslatableConfig();

            return Locales::installed()
                ->filter(fn (LocaleData $locale) => $config === null || in_array($locale->code, $config->available_locales, true))
                ->mapWithKeys(fn (LocaleData $locale) => [$locale->code => $locale->localized]);
        });

        $this->label(trans('laravel-filament-translatable::messages.language'));
        $this->multiple();
        $this->hidden(Locales::installed()->count() < 2 ? true : $this->getHiddenClosure());
    }
}
