<?php

namespace Workbench\App\Filament\Resources\Categories\Resources\Products\Pages;

use Novius\LaravelFilamentTranslatable\Filament\Resources\Pages\CreateRecord;
use Workbench\App\Filament\Resources\Categories\Resources\Products\CategoryProductResource;

class CreateCategoryProduct extends CreateRecord
{
    protected static string $resource = CategoryProductResource::class;
}
