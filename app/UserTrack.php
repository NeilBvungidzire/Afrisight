<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTrack extends Model {

    protected $fillable = [
        'time',
        'user_id',

        'ip_address',
        'user_agent',
        'session_key',
        'uri',
        'referer',
        'meta_data',
    ];

    protected $casts = [
        'time'       => 'timestamp',
        'ip_address' => 'integer',
        'meta_data'  => 'array',
    ];

    public $timestamps = false;

    protected $primaryKey = null;
    public $incrementing = false;

    protected $connection = 'user_tracks';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $userTrack) {
            $userTrack->time = now();
        });
    }
}
