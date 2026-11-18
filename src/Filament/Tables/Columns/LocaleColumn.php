<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Tables\Columns;

use Exception;
use Filament\Tables\Columns\Column;
use LaravelLang\Locales\Facades\Locales;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;

class LocaleColumn extends Column
{
    use IsTranslatable;

    protected string $view = 'laravel-filament-translatable::locale';

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('laravel-filament-translatable::messages.language'));
        $this->viewData(fn (): array => [
            'localeLabel' => $this->localeLabel(...),
            'flagUrl' => $this->flagUrl(...),
        ]);
        $this->hidden(Locales::installed()->count() < 2 ? true : $this->getHiddenClosure());
    }
}
