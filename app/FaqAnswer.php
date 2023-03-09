<?php

namespace App;

use App\Scopes\IsActiveScope;
use App\Scopes\SortScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FaqAnswer extends Model {

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new IsActiveScope());
        static::addGlobalScope(new SortScope());
    }

    // ------------------------------------
    // Relations
    //

    /**
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }

    // ------------------------------------
    // Attributes
    //

    /**
     * @return string
     */
    public function getSlugAttribute()
    {
        return Str::slug($this->question);
    }
}
