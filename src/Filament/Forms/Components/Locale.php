<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Forms\Components;

use Filament\Forms\Components\Select;
use LaravelLang\Locales\Data\LocaleData;
use LaravelLang\Locales\Facades\Locales;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;

class Locale extends Select
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

        $this->default(function () {
            $config = $this->modelTranslatableConfig();

            if ($config !== null && count($config->available_locales) === 1) {
                return $config->available_locales[0];
            }

            return null;
        });
        $this->hidden(Locales::installed()->count() < 2 ? true : $this->getHiddenClosure());
    }
}
