<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Novius\LaravelPublishable\Traits\Publishable;
use Novius\LaravelTranslatable\Support\TranslatableModelConfig;
use Novius\LaravelTranslatable\Traits\Translatable;

/**
 * A post in one locale (one row per locale), without common record, publishable (laravel-publishable).
 *
 * @property int $id
 * @property string $title
 * @property string $locale
 * @property int|null $locale_parent_id
 */
class Post extends Model
{
    use Publishable;
    use Translatable;

    protected $guarded = [];

    public function translatableConfig(): TranslatableModelConfig
    {
        return new TranslatableModelConfig(['fr', 'en']);
    }
}
