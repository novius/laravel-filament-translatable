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
        $parent = Request::input('locale_parent');
        $locale = Request::input('locale');
        if ($parent && $locale) {
            $model = $this->translatableModel($this->getModel());
            $config = $this->modelTranslatableConfig($this->getModel());
            if ($model !== null && $config !== null && in_array($locale, $config->available_locales, true)) {
                $parent = $model->resolveRouteBinding($parent);
                if ($parent) {
                    $this->data = [
                        ...$this->getDataFromTranslate($parent, $locale),
                        $config->locale_column => $locale,
                        $config->locale_parent_id_column => $parent->getKey(),
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

    /**
     * @return array<string, mixed>
     */
    protected function getDataFromTranslate(Model $parent, string $locale): array
    {
        return $parent->attributesToArray();
    }
}
