<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Tables\Columns;

use Exception;
use Filament\Tables\Columns\ViewColumn;
use LaravelLang\Locales\Facades\Locales;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;

class LocaleColumn extends ViewColumn
{
    use IsTranslatable;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(trans('laravel-filament-translatable::messages.language'));
        $this->view('laravel-filament-translatable::locale', function ($state) {
            return [
                'locale' => $state,
                'locales' => Locales::installed(),
            ];
        });
        $this->hidden(Locales::installed()->count() < 2 ? true : $this->getHiddenClosure());
    }
}
