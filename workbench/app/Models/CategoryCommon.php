<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Novius\LaravelTranslatable\Traits\IsCommonRecord;

/**
 * The data of a category shared by all the locales.
 *
 * @property int $id
 * @property int $position
 */
class CategoryCommon extends Model
{
    /** @use IsCommonRecord<Category> */
    use IsCommonRecord;

    protected $guarded = [];

    /**
     * @return HasMany<Category, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function commonRecordLabelAttribute(): string
    {
        return 'title';
    }
}
