<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Pengaturan;
use App\Support\PengaturanDefaults;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingsManager
{
    public const CACHE_KEY_ALL = 'pengaturan:all';
    public const CACHE_TTL_SECONDS = 3600;

    /**
     * Ambil seluruh data pengaturan (dari cache atau DB).
     */
    public function all(?string $group = null): array
    {
        try {
            $all = Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL_SECONDS, function () {
                $rows = Pengaturan::select('key', 'value')->get();
                $data = [];
                foreach ($rows as $row) {
                    $data[$row->key] = $row->value;
                }
                return $data;
            });
        } catch (\Exception $e) {
            Log::warning('SettingsManager: Gagal membaca cache, fallback ke DB: ' . $e->getMessage());
            $rows = Pengaturan::select('key', 'value')->get();
            $all = [];
            foreach ($rows as $row) {
                $all[$row->key] = $row->value;
            }
        }

        if ($group === null) {
            return $all;
        }

        $filtered = [];
        $defs = PengaturanDefaults::definitions();
        foreach ($all as $key => $val) {
            if (isset($defs[$key]) && $defs[$key]['group'] === $group) {
                $filtered[$key] = $val;
            }
        }
        return $filtered;
    }

    /**
     * Baca nilai pengaturan berdasarkan key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (array_key_exists($key, $all)) {
            $val = $all[$key];
            if ($val !== null) {
                return $val;
            }
        }

        if ($default !== null) {
            return $default;
        }

        $meta = PengaturanDefaults::get($key);
        return $meta !== null ? $meta['default'] : null;
    }

    /**
     * Baca nilai string.
     */
    public function getString(string $key, ?string $default = null): ?string
    {
        $val = $this->get($key, $default);
        return $val !== null ? (string) $val : null;
    }

    /**
     * Baca nilai boolean secara normatif.
     */
    public function getBool(string $key): bool
    {
        $val = $this->get($key);

        if ($val === null) {
            $meta = PengaturanDefaults::get($key);
            $val = $meta !== null ? $meta['default'] : '0';
        }

        if (is_bool($val)) {
            return $val;
        }

        $str = strtolower(trim((string) $val));
        return in_array($str, ['1', 'true', 'ya', 'yes', 'on'], true);
    }

    /**
     * Baca nilai integer.
     */
    public function getInt(string $key, int $default = 0): int
    {
        $val = $this->get($key, $default);
        return (int) $val;
    }

    /**
     * Baca nilai float.
     */
    public function getFloat(string $key, float $default = 0.0): float
    {
        $val = $this->get($key, $default);
        return (float) $val;
    }

    /**
     * Baca nilai array / json aman.
     */
    public function getArray(string $key, array $default = []): array
    {
        $val = $this->get($key);
        if (is_array($val)) {
            return $val;
        }
        if (is_string($val) && $val !== '') {
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        return $default;
    }

    /**
     * Cek apakah key memiliki nilai (di DB atau SOT).
     */
    public function has(string $key): bool
    {
        $all = $this->all();
        if (array_key_exists($key, $all)) {
            return true;
        }
        return PengaturanDefaults::has($key);
    }

    /**
     * Simpan nilai pengaturan tunggal.
     */
    public function set(string $key, mixed $value, ?string $group = null): void
    {
        $meta = PengaturanDefaults::get($key);
        $finalGroup = $group ?? ($meta['group'] ?? 'umum');

        // Jika tipe boolean, pastikan disimpan '1' / '0'
        if ($meta && $meta['type'] === 'boolean') {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif (in_array(strtolower(trim((string) $value)), ['1', 'true', 'ya', 'on'], true)) {
                $value = '1';
            } else {
                $value = '0';
            }
        }

        DB::transaction(function () use ($key, $value, $finalGroup, $meta) {
            $oldRow = Pengaturan::where('key', $key)->first();
            $oldValue = $oldRow?->value ?? null;

            Pengaturan::updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) $value,
                    'group' => $finalGroup,
                ]
            );

            // Log perubahan
            $label = $meta['label'] ?? $key;
            ActivityLog::record(
                action: 'toggle',
                module: 'pengaturan',
                description: "Mengubah pengaturan \"{$label}\" ({$key}) dari \"{$oldValue}\" menjadi \"{$value}\"",
                oldData: ['key' => $key, 'value' => $oldValue],
                newData: ['key' => $key, 'value' => $value]
            );

            $this->forget($key);
        });
    }

    /**
     * Simpan nilai boolean.
     */
    public function setBool(string $key, bool $value, ?string $group = null): void
    {
        $this->set($key, $value ? '1' : '0', $group);
    }

    /**
     * Batch update dalam 1 transaksi.
     */
    public function setMany(array $values): void
    {
        DB::transaction(function () use ($values) {
            foreach ($values as $key => $val) {
                $meta = PengaturanDefaults::get($key);
                $group = $meta['group'] ?? 'umum';

                if ($meta && $meta['type'] === 'boolean') {
                    if (is_bool($val)) {
                        $val = $val ? '1' : '0';
                    } elseif (in_array(strtolower(trim((string) $val)), ['1', 'true', 'ya', 'on'], true)) {
                        $val = '1';
                    } else {
                        $val = '0';
                    }
                }

                Pengaturan::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => (string) $val,
                        'group' => $group,
                    ]
                );
            }

            $this->flush();
        });
    }

    /**
     * Hapus cache key spesifik & cache utama.
     */
    public function forget(string $key): void
    {
        try {
            Cache::forget(self::CACHE_KEY_ALL);
            Cache::forget("pengaturan:{$key}");
            Cache::forget('pengaturan');
            Cache::forget('absensi_settings');
            Cache::forget('absensi_settings_piket');
        } catch (\Exception $e) {
            Log::warning("SettingsManager: Invalidation cache gagal untuk {$key}: " . $e->getMessage());
        }
    }

    /**
     * Flush seluruh cache pengaturan.
     */
    public function flush(): void
    {
        try {
            Cache::forget(self::CACHE_KEY_ALL);
            Cache::forget('pengaturan');
            Cache::forget('absensi_settings');
            Cache::forget('absensi_settings_piket');

            foreach (array_keys(PengaturanDefaults::definitions()) as $key) {
                Cache::forget("pengaturan:{$key}");
            }
        } catch (\Exception $e) {
            Log::warning('SettingsManager: Flush cache gagal: ' . $e->getMessage());
        }
    }

    /**
     * Dapatkan tipe data key dari SOT.
     */
    public function keyType(string $key): string
    {
        $meta = PengaturanDefaults::get($key);
        return $meta['type'] ?? 'string';
    }

    /**
     * Cek apakah key merupakan feature toggle.
     */
    public function isToggle(string $key): bool
    {
        return PengaturanDefaults::isToggle($key);
    }

    /**
     * Dapatkan daftar defaults SOT.
     */
    public function defaults(): array
    {
        return PengaturanDefaults::defaults();
    }
}
