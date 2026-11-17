<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Novius\LaravelTranslatable\Support\TranslatableModelConfig;
use Novius\LaravelTranslatable\Traits\HasCommonRecord;
use Novius\LaravelTranslatable\Traits\Translatable;

/**
 * A category in one locale, its data shared by all the locales in CategoryCommon, created by the model: the back
 * office edits the common record once the category is created.
 *
 * @property int $id
 * @property int|null $category_common_id
 * @property string $title
 * @property string $locale
 * @property int|null $locale_parent_id
 * @property-read CategoryCommon|null $commonRecord
 */
class Category extends Model
{
    /** @use HasCommonRecord<CategoryCommon> */
    use HasCommonRecord;

    use Translatable;

    protected $guarded = [];

    public function translatableConfig(): TranslatableModelConfig
    {
        return new TranslatableModelConfig(['fr', 'en']);
    }

    /**
     * @return BelongsTo<CategoryCommon, $this>
     */
    public function commonRecord(): BelongsTo
    {
        return $this->belongsTo(CategoryCommon::class, 'category_common_id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected function createCommonRecord(): Model
    {
        return new CategoryCommon(['position' => 0]);
    }
}
