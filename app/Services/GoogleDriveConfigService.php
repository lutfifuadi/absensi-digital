<?php

namespace App\Services;

use App\Models\GoogleDriveSetting;

class GoogleDriveConfigService
{
    public static function getConfig(): array
    {
        $setting = GoogleDriveSetting::first();

        try {
            $redirectUri = $setting?->google_redirect_uri ?: route('admin.google.callback');
        } catch (\Exception $e) {
            $redirectUri = url('/admin/pengaturan/google-drive/callback');
        }

        return [
            'client_id' => $setting?->google_client_id ?? config('services.google.client_id'),
            'client_secret' => $setting?->google_client_secret ?? config('services.google.client_secret'),
            'redirect_uri' => $redirectUri,
            'root_folder_id' => $setting?->google_root_folder_id ?? '',
            'access_token' => $setting?->google_access_token,
            'refresh_token' => $setting?->google_refresh_token,
            'token_expires_at' => $setting?->google_token_expires_at,
            'is_connected' => $setting?->is_connected ?? false,
        ];
    }

    public static function clearCache(): void
    {
        // Cache is not used, keeping for compatibility
    }
}
