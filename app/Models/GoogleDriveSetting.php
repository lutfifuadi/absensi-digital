<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleDriveSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_drive_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'google_client_id',
        'google_client_secret',
        'google_redirect_uri',
        'google_root_folder_id',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'is_connected',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_connected' => 'boolean',
        'google_token_expires_at' => 'datetime',
        'google_client_secret' => 'encrypted',
        'google_access_token' => 'encrypted',
        'google_refresh_token' => 'encrypted',
    ];
}
