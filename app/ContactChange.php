<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ContactChange extends Model {

    public const EMAIL = 'EMAIL';
    public const MOBILE_NUMBER = 'MOBILE_NUMBER';

    protected $fillable = [
        'person_id',
        'contact_reference',
        'from',
        'to',
    ];

    // ------------------------------------------------------------------------
    // Relations
    //

    public function person(): BelongsTo {
        return $this->belongsTo(Person::class);
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * Check if the person is allowed to change certain contact details based on the allowed time between.
     *
     * @param $personId
     * @param  string  $contactReference  See constants
     * @return bool Whether the person is allowed to change the contact detail.
     */
    public static function canChange($personId, string $contactReference): bool {
        return ! self::query()
            ->where('person_id', $personId)
            ->where('contact_reference', $contactReference)
            ->where('updated_at', '>', now()->subDays(30))
            ->exists();
    }
}
