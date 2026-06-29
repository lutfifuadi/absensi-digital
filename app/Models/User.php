<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN_SEKOLAH = 'admin_sekolah';
    public const ROLE_OPERATOR = 'operator';
    public const ROLE_GURU = 'guru';
    public const ROLE_WALI_KELAS = 'wali_kelas';
    public const ROLE_STAFF_TU = 'staff_tu';
    public const ROLE_SISWA = 'siswa';
    public const ROLE_ORANG_TUA = 'orang_tua';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'roles',
        'no_hp',
        'hubungan',
        'status',
        'alamat',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
        ];
    }

    public function getRolesAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public function isRole(string $role): bool
    {
        return $this->role === $role || in_array($role, $this->roles, true);
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true) || count(array_intersect($this->roles, $roles)) > 0;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isRole(self::ROLE_SUPER_ADMIN);
    }

    public function scopeWithRole($query, string $role)
    {
        return $query->where(function ($query) use ($role) {
            $query->where('role', $role);

            if (Schema::hasColumn('users', 'roles')) {
                $query->orWhereJsonContains('roles', $role);
            }
        });
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->siswa) {
                $user->siswa->delete();
            }

            if ($user->activityLogs()->exists()) {
                $user->activityLogs()->delete();
            }
        });
    }

    public function guru()
    {
        return $this->hasOne(Guru::class);
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function staff()
    {
        return $this->hasOne(StaffTataUsaha::class);
    }

    public function children()
    {
        return $this->belongsToMany(Siswa::class, 'siswa_ortu', 'ortu_user_id', 'siswa_id')->withTimestamps();
    }
}
