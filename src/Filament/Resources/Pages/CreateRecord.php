<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Resources\Pages;

use Exception;
use Filament\Resources\Pages\CreateRecord as BaseCreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;

class CreateRecord extends BaseCreateRecord
{
    use IsTranslatable;

    /**
     * @throws Exception
     */
    public function mount(): void
    {
        $parent = Request::get('parent');
        $locale = Request::get('locale');
        if ($parent && $locale) {
            $model = $this->translatableModel($this->getModel());
            if ($model !== null && in_array($locale, $model->translatableConfig()->available_locales, true)) {
                $parent = $model->resolveRouteBinding($parent);
                if ($parent) {
                    $this->data = [
                        ...$this->getDataFromTranslate($parent, $locale),
                        $model->translatableConfig()->locale_column => $locale,
                        $model->translatableConfig()->locale_parent_id_column => $parent->getKey(),
                    ];
                }
            }
        }

        parent::mount();
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        if (! empty($this->data)) {
            $this->form->fill($this->data);
        } else {
            $this->form->fill();
        }

        $this->callHook('afterFill');
    }

    protected function getDataFromTranslate(Model $parent, string $locale): array
    {
        return $parent->attributesToArray();
    }
}
