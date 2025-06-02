<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Traits;

use Closure;
use Exception;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Model;
use Novius\LaravelTranslatable\Traits\Translatable;

trait IsTranslatable
{
    /**
     * @return Model&Translatable|null
     *
     * @throws Exception
     */
    protected function translatableModel($modelClass = null): ?Model
    {
        if ($this instanceof Field) {
            $modelClass = $this->getModel();
        } elseif ($this instanceof Column || $this instanceof BaseFilter) {
            $modelClass = $this->getTable()->getModel();
        }
        if ($modelClass !== null && in_array(Translatable::class, class_uses_recursive($modelClass), true)) {
            return new $modelClass;
        }

        return null;
    }

    protected function getHiddenClosure(?array $locales = null): Closure
    {
        return function () use ($locales) {
            $model = $this->translatableModel();
            if ($model !== null && $locales === null) {
                $locales = $model->translatableConfig()->available_locales;
            }

            return count($locales ?? []) < 2;
        };
    }
}
