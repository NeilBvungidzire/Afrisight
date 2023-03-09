<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SampleProvider extends Model {

    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'project_codes',
        'source',
        'end_redirects',
    ];

    protected $casts = [
        'project_codes' => 'array',
        'end_redirects' => 'array',
    ];

    // ------------------------------------------------------------------------
    // Custom methods
    //

    public static function generateRandomProjectId(): string
    {
        return Str::random(12);
    }

    /**
     * @param string      $status
     * @param string|null $externalRespondentId
     * @return string|null
     */
    public function generateEndResultUrl(string $status, string $externalRespondentId = null): ?string
    {
        $endRedirects = $this->end_redirects;
        if ( ! $endRedirects) {
            return null;
        }

        if ( ! $link = $endRedirects[$status] ?? null) {
            return null;
        }

        return Str::replaceFirst('{RID}', $externalRespondentId, $link);
    }
}
