<?php

namespace Workbench\App\Filament\Resources\Products\Pages;

use Novius\LaravelFilamentTranslatable\Filament\Resources\Pages\CreateRecord;
use Workbench\App\Filament\Resources\Products\ProductResource;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function copiedOnTranslate(): ?array
    {
        return ['title'];
    }
}
