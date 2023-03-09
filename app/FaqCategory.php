<?php

namespace App;

use App\Scopes\IsActiveScope;
use App\Scopes\SortScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FaqCategory extends Model {

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
     * @return HasMany
     */
    public function questions()
    {
        return $this->hasMany(FaqAnswer::class, 'faq_category_id');
    }

    // ------------------------------------
    // Attributes
    //

    /**
     * @return string
     */
    public function getSlugAttribute()
    {
        return Str::slug($this->name);
    }
}
