<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;
use Novius\LaravelTranslatable\Support\TranslatableModelConfig;
use Novius\LaravelTranslatable\Traits\HasCommonRecord;
use Novius\LaravelTranslatable\Traits\Translatable;

/**
 * A product in one locale, its data shared by all the locales in ProductCommon.
 *
 * @property int $id
 * @property int|null $product_common_id
 * @property int|null $category_id
 * @property string $title
 * @property string|null $subtitle
 * @property string $locale
 * @property int|null $locale_parent_id
 * @property-read ProductCommon|null $commonRecord
 */
class Product extends Model
{
    /** @use HasCommonRecord<ProductCommon> */
    use HasCommonRecord;

    use Translatable;

    protected $guarded = [];

    public function translatableConfig(): TranslatableModelConfig
    {
        return new TranslatableModelConfig(['fr', 'en']);
    }

    /**
     * @return BelongsTo<ProductCommon, $this>
     */
    public function commonRecord(): BelongsTo
    {
        // The foreign key is named: guessed from the name of the method, it would be common_record_id
        return $this->belongsTo(ProductCommon::class, 'product_common_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The reference of the common record is required and known only by the form: the back office saves the common
     * record before the product, this is never called there.
     */
    protected function createCommonRecord(): Model
    {
        throw new LogicException('A product is created with its common record.');
    }
}
