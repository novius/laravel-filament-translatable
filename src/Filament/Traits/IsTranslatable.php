<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Traits;

use Closure;
use Exception;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Model;
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
