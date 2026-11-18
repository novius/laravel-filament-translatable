<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Resources\Pages\Concerns;

use Exception;
use Novius\LaravelFilamentTranslatable\Filament\Actions\TranslationsActionGroup;

/**
 * For the view or edit page of a translatable model: translationsActionGroup() gives the group of the model of the
 * page, to place in its header actions.
 */
trait HasTranslationsActionGroup
{
    /**
     * @throws Exception
     */
    protected function translationsActionGroup(): TranslationsActionGroup
    {
        return TranslationsActionGroup::forModel($this->getModel());
    }
}
