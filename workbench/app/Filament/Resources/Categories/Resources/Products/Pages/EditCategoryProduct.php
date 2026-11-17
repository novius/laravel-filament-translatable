<?php

namespace Workbench\App\Filament\Resources\Categories\Resources\Products\Pages;

use Filament\Resources\Pages\EditRecord;
use Workbench\App\Filament\Resources\Categories\Resources\Products\CategoryProductResource;

class EditCategoryProduct extends EditRecord
{
    protected static string $resource = CategoryProductResource::class;
}
