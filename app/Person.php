<?php

namespace App;

use App\Cint\HasCintRelationship;
use App\Constants\Gender;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class Person
 *
 * @package App
 * @property int    $country_id
 * @property int    $region_id
 * @property string $currency_code
 * @property string $language_code
 * @property string $gender_code
 * @property string $first_name
 * @property string $last_name
 * @property string $date_of_birth
 * @property string $email
 * @property string $mobile_number
 * @property array|null $account_params
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property BankAccount[] $bankAccounts
 */
class Person extends Model {

    use SoftDeletes, HasCintRelationship;

    protected $table = 'persons';

    protected $fillable = [
        'country_id',
        'region_id',

        'currency_code',
        'language_code',
        'gender_code',

        'first_name',
        'last_name',
        'date_of_birth',
        'email',
        'mobile_number',
        'reward_balance',
        'account_params',
    ];

    protected $casts = [
        'reward_balance' => 'decimal:2',
        'account_params' => 'array',
    ];

    // ------------------------------------------------------------------------
    // Relations
    //

    /**
     * @return HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return BelongsTo
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * @return HasMany
     */
    public function profilingAnswers()
    {
        return $this->hasMany(MemberProfilingAnswer::class, 'person_id');
    }

    /**
     * @return HasMany
     */
    public function respondent()
    {
        return $this->hasMany(Respondent::class);
    }

    /**
     * @return HasMany
     */
    public function dataPoints()
    {
        return $this->hasMany(DataPoint::class);
    }

    /**
     * @return HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany
     */
    public function bankAccounts(): HasMany {
        return $this->hasMany(BankAccount::class);
    }

    // ------------------------------------------------------------------------
    // Attributes
    //

    /**
     * Get full name.
     *
     * @return string|null
     */
    public function getFullNameAttribute()
    {
        if ( ! empty($this->first_name) && ! empty($this->last_name)) {
            return "{$this->first_name} {$this->last_name}";
        }

        return null;
    }

    /**
     * Get date of birth in d-m-Y format if set.
     *
     * @return string
     */
    public function getDateOfBirthAttribute()
    {
        if (empty($this->attributes['date_of_birth'])) {
            return '';
        }

        try {
            return (new Carbon($this->attributes['date_of_birth']))->format('d-m-Y');
        } catch (Exception $exception) {
            return '';
        }
    }

    /**
     * Set date of birth value in Y-m-d format before save.
     *
     * @param string $value
     *
     * @return string
     */
    public function setDateOfBirthAttribute(string $value)
    {
        try {
            return $this->attributes['date_of_birth'] = (new Carbon($value))->format('Y-m-d');
        } catch (Exception $exception) {
            Log::error('Could not format date of birth with the following value: ' . $value, $exception->getTrace());
        }

        return '';
    }

    /**
     * Get gender human readable name.
     *
     * @return string|null
     */
    public function getGenderAttribute()
    {
        $genders = Gender::getKeyWithLabel();

        return (isset($genders[$this->attributes['gender_code']])) ? $genders[$this->attributes['gender_code']] : null;
    }

    /**
     * Whether minimal member data is available.
     *
     * @return bool
     */
    public function getMinimalProfileDataIsAvailableAttribute()
    {
        if (empty($this->country_id)) {
            return false;
        }

        if (empty($this->gender)) {
            return false;
        }

        if (empty($this->date_of_birth)) {
            return false;
        }

        return true;
    }

    /**
     * @return int|null
     */
    public function getAgeAttribute()
    {
        if (empty($this->attributes['date_of_birth'])) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $this->attributes['date_of_birth'])->age;
    }

    /**
     * @param float $value
     *
     * @return float
     */
    public function getRewardBalanceAttribute(float $value)
    {
        return (float)$value;
    }

    /**
     * @return bool
     */
    public function getCanRequestPayoutAttribute(): bool
    {
        return ! empty($this->country_id);
    }

    public function getCountryCodeAttribute(): ?string
    {
        if ( ! $countryId = $this->country_id) {
            return null;
        }

        try {
            return cache()->remember("PERSON_{$this->id}_COUNTRY_ID", now()->addMonth(), static function() use ($countryId) {
                return Country::getCountryIso2Code($countryId);
            });
        } catch (Exception $exception) {
            return null;
        }
    }
}
