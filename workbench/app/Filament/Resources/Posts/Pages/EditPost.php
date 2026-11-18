<?php

namespace Workbench\App\Filament\Resources\Posts\Pages;

use Filament\Resources\Pages\EditRecord;
use Novius\LaravelFilamentTranslatable\Filament\Resources\Pages\Concerns\HasTranslationsActionGroup;
use Workbench\App\Filament\Resources\Posts\PostResource;

class EditPost extends EditRecord
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
