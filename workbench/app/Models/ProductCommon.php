<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Novius\LaravelTranslatable\Traits\IsCommonRecord;

/**
 * The data of a product shared by all the locales.
 *
 * @property int $id
 * @property string $reference
 * @property int|null $weight
 */
class ProductCommon extends Model
{
    /** @use IsCommonRecord<Product> */
    use IsCommonRecord;

    protected $guarded = [];

    /**
     * @return HasMany<Product, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function commonRecordLabelAttribute(): string
    {
        return 'title';
    }
}
