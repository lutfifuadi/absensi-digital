<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
    ];

    public function getGoogleClientSecretAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        try {
            return decrypt($value);
        } catch (\Throwable $e) {
            Log::warning('GoogleDriveSetting: Gagal mendekripsi google_client_secret. APP_KEY mungkin berubah.', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function setGoogleClientSecretAttribute($value)
    {
        $this->attributes['google_client_secret'] = !empty($value) ? encrypt($value) : null;
    }

    public function getGoogleAccessTokenAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        try {
            return decrypt($value);
        } catch (\Throwable $e) {
            Log::warning('GoogleDriveSetting: Gagal mendekripsi google_access_token. APP_KEY mungkin berubah.', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function setGoogleAccessTokenAttribute($value)
    {
        $this->attributes['google_access_token'] = !empty($value) ? encrypt($value) : null;
    }

    public function getGoogleRefreshTokenAttribute($value)
    {
        if (empty($value)) {
            return null;
        }
        try {
            return decrypt($value);
        } catch (\Throwable $e) {
            Log::warning('GoogleDriveSetting: Gagal mendekripsi google_refresh_token. APP_KEY mungkin berubah.', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function setGoogleRefreshTokenAttribute($value)
    {
        $this->attributes['google_refresh_token'] = !empty($value) ? encrypt($value) : null;
    }
}
