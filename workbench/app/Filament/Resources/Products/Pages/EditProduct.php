<?php

namespace Workbench\App\Filament\Resources\Products\Pages;

use Filament\Resources\Pages\EditRecord;
use Novius\LaravelFilamentTranslatable\Filament\Resources\Pages\Concerns\HasTranslationsActionGroup;
use Workbench\App\Filament\Resources\Products\ProductResource;

class EditProduct extends EditRecord
{
    use HasTranslationsActionGroup;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->translationsActionGroup(),
        ];
    }
}
