<?php

namespace App;

use App\Scopes\SortScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Class ProfilingQuestion
 *
 * @package App
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string $type
 * @property bool $is_published
 * @property bool $is_definitive
 * @property array $settings
 * @property array $answer_params
 * @property array $conditions
 * @property string $datapoint_identifier
 * @property int $sort
 */
class ProfilingQuestion extends Model {

    use SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'is_published',
        'is_definitive',
        'settings',
        'answer_params',
        'conditions',
        'datapoint_identifier',
        'sort',
    ];

    protected $casts = [
        'settings'      => 'array',
        'answer_params' => 'array',
        'conditions'    => 'array',
        'is_published'  => 'boolean',
        'is_definitive' => 'boolean',
    ];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope(new SortScope());

        static::addGlobalScope('is_published', static function (Builder $builder) {
            $builder->where('is_published', true);
        });

        static::creating(static function (ProfilingQuestion $profilingQuestion) {
            // Add UUID on creating
            $profilingQuestion->uuid = Str::uuid();

            // A published question must be definitive, otherwise it shouldn't be published.
            if ($profilingQuestion->is_published) {
                $profilingQuestion->is_definitive = true;
            }
        });

        static::updating(static function (ProfilingQuestion $profilingQuestion) {
            // A published question must be definitive, otherwise it shouldn't be published.
            if ($profilingQuestion->is_published) {
                $profilingQuestion->is_definitive = true;
            }
        });
    }

    // ------------------------------------------------------------------------
    // Mutators
    //

    /**
     * @param  string  $value
     *
     * @return array
     */
    public function getSettingsAttribute(string $value): array {
        $settings = json_decode($value, true);

        return [
            'isRequired' => (isset($settings['isRequired']) && ! empty($settings['isRequired'])),
        ];
    }
}
