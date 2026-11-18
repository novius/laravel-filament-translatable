<?php

namespace Workbench\App\Filament\Resources\Posts\Pages;

use Filament\Resources\Pages\ViewRecord;
use Novius\LaravelFilamentTranslatable\Filament\Resources\Pages\Concerns\HasTranslationsActionGroup;
use Workbench\App\Filament\Resources\Posts\PostResource;

class ViewPost extends ViewRecord
{
    use HasTranslationsActionGroup;

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->translationsActionGroup(),
        ];
    }
}
