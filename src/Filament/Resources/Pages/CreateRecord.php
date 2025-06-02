<?php

namespace Novius\LaravelFilamentTranslatable\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord as BaseCreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Novius\LaravelFilamentTranslatable\Filament\Traits\IsTranslatable;

class CreateRecord extends BaseCreateRecord
{
    use IsTranslatable;

    public function mount(): void
    {
        $parent = Request::get('parent');
        $locale = Request::get('locale');
        $data = [];
        if ($parent && $locale) {
            $parent = static::getResource()::resolveRecordRouteBinding($parent);
            $model = $this->translatableModel($parent);
            if ($model !== null && in_array($locale, $model->translatableConfig()->available_locales, true)) {
                $data = [
                    ...$this->getDataFromTranslate($parent, $locale),
                    $model->translatableConfig()->locale_column => $locale,
                    $model->translatableConfig()->locale_parent_id_column => $parent->getKey(),
                ];
            }
        }

        parent::mount();

        $this->form->fill($data);
    }

    protected function getDataFromTranslate(Model $parent, string $locale): array
    {
        return $parent->attributesToArray();
    }
}
