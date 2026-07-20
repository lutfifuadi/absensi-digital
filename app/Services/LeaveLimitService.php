<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveLimit;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveLimitService
{
    /**
     * Prefix cache untuk leave balances.
     */
    private const CACHE_KEY_BALANCE = 'leave_balance_';

    /**
     * Ambil semua aturan limit yang berlaku untuk user tertentu
     * berdasarkan role & grade.
     *
     * @param  User  $user
     * @return Collection<int, LeaveLimit>
     */
    public function getApplicableLimits(User $user): Collection
    {
        $userRole = $user->role;
        $userGrade = $this->getUserGrade($user);

        $query = LeaveLimit::where('is_active', true);

        // Filter role: cari rules yang target_roles-nya mengandung role user
        $query->where(function ($q) use ($userRole) {
            // Gunakan JSON_CONTAINS atau pendekatan collection
            $q->whereJsonContains('target_roles', $userRole);
        });

        // Filter grade: jika user punya grade dan rule punya target_grades
        if ($userGrade !== null) {
            $query->where(function ($q) use ($userGrade) {
                $q->whereNull('target_grades')
                  ->orWhereJsonContains('target_grades', $userGrade);
            });
        } else {
            // Jika user tidak punya grade, hanya rules tanpa target_grades
            $query->whereNull('target_grades');
        }

        return $query->orderBy('max_days')->get();
    }

    /**
     * Hitung sisa kuota user untuk aturan limit tertentu.
     *
     * @param  User       $user
     * @param  LeaveLimit $limit
     * @return int  Sisa hari
     */
    public function getUserBalance(User $user, LeaveLimit $limit): int
    {
        $periodCode = $this->getPeriodCode($limit);

        $balance = $this->getCachedBalance($user->id, $limit->id, $periodCode);

        $totalQuota = $limit->max_days + ($balance?->extra_days ?? 0);
        $usedDays   = $balance?->used_days ?? 0;

        return max(0, $totalQuota - $usedDays);
    }

    /**
     * Generate period_code berdasarkan period pada limit.
     *
     * - monthly  => "YYYY-MM"        (contoh: 2026-07)
     * - semester => "YYYY-ganjil"    atau "YYYY-genap"
     * - yearly   => "YYYY-YYYY"      (contoh: 2025-2026)
     *
     * @param  LeaveLimit $limit
     * @return string
     */
    public function getPeriodCode(LeaveLimit $limit): string
    {
        $now = now();

        return match ($limit->period) {
            'monthly' => $now->format('Y-m'),
            'semester' => $this->getSemesterCode($now),
            'yearly' => $this->getYearlyCode($now),
            default => $now->format('Y-m'),
        };
    }

    /**
     * Validasi kuota user sebelum mengajukan izin.
     *
     * Mencari aturan limit yang paling ketat (max_days terkecil)
     * untuk user tersebut berdasarkan role & leave_type.
     *
     * @param  User    $user
     * @param  string  $leaveType  'sick' | 'permission' | 'all'
     * @param  int     $requestDays  Jumlah hari yang diajukan
     * @return array  ['allowed' => bool, 'is_overlimit' => bool, 'action_type' => string|null, 'balances' => array]
     */
    public function validateQuota(User $user, string $leaveType, int $requestDays): array
    {
        // Cari aturan yang applicable
        $limits = $this->getApplicableLimits($user);

        // Filter by leave_type
        $limits = $limits->filter(function (LeaveLimit $limit) use ($leaveType) {
            return $limit->leave_type === 'all' || $limit->leave_type === $leaveType;
        });

        $balances = [];
        $overLimit = false;
        $actionType = null;

        // Jika tidak ada aturan yang cocok, izinkan (allowed = true)
        if ($limits->isEmpty()) {
            return [
                'allowed'     => true,
                'is_overlimit' => false,
                'action_type' => null,
                'balances'    => [],
            ];
        }

        foreach ($limits as $limit) {
            $periodCode  = $this->getPeriodCode($limit);
            $balance     = $this->getCachedBalance($user->id, $limit->id, $periodCode);
            $totalQuota  = $limit->max_days + ($balance?->extra_days ?? 0);
            $usedDays    = $balance?->used_days ?? 0;
            $remaining   = max(0, $totalQuota - $usedDays);

            $balances[] = [
                'limit_id'    => $limit->id,
                'name'        => $limit->name,
                'period_code' => $periodCode,
                'max_days'    => $limit->max_days,
                'extra_days'  => $balance?->extra_days ?? 0,
                'used_days'   => $usedDays,
                'remaining'   => $remaining,
                'action_type' => $limit->action_type,
            ];

            // Jika sisa tidak mencukupi
            if ($remaining < $requestDays) {
                $overLimit = true;
                // action_type diambil dari aturan yang paling ketat (terakhir iterasi)
                $actionType = $limit->action_type;
            }
        }

        return [
            'allowed'      => !$overLimit,
            'is_overlimit' => $overLimit,
            'action_type'  => $actionType,
            'balances'     => $balances,
        ];
    }

    /**
     * Update tabel leave_balances setelah pengajuan disetujui (approved).
     * Menambah used_days sesuai jumlah hari izin.
     *
     * @param  User   $user
     * @param  string $leaveType
     * @param  int    $usedDays  Jumlah hari yang akan ditambahkan
     * @return void
     */
    public function deductQuota(User $user, string $leaveType, int $usedDays): void
    {
        $limits = $this->getApplicableLimits($user);

        $limits = $limits->filter(function (LeaveLimit $limit) use ($leaveType) {
            return $limit->leave_type === 'all' || $limit->leave_type === $leaveType;
        });

        DB::transaction(function () use ($user, $limits, $usedDays) {
            foreach ($limits as $limit) {
                $periodCode = $this->getPeriodCode($limit);

                $balance = LeaveBalance::firstOrNew([
                    'user_id'       => $user->id,
                    'leave_limit_id' => $limit->id,
                    'period_code'   => $periodCode,
                ]);

                $balance->used_days = ($balance->used_days ?? 0) + $usedDays;
                $balance->save();

                // Hapus cache
                $this->forgetBalanceCache($user->id, $limit->id, $periodCode);
            }
        });
    }

    /**
     * Tambah kuota ekstra (dispensasi) untuk user pada limit tertentu.
     *
     * @param  User       $user
     * @param  LeaveLimit $limit
     * @param  int        $extraDays
     * @param  string     $reason
     * @return LeaveBalance
     */
    public function addDispensation(User $user, LeaveLimit $limit, int $extraDays, string $reason): LeaveBalance
    {
        $periodCode = $this->getPeriodCode($limit);

        $balance = LeaveBalance::firstOrNew([
            'user_id'       => $user->id,
            'leave_limit_id' => $limit->id,
            'period_code'   => $periodCode,
        ]);

        $balance->extra_days = ($balance->extra_days ?? 0) + $extraDays;
        $balance->dispensation_reason = $reason;
        $balance->save();

        // Hapus cache
        $this->forgetBalanceCache($user->id, $limit->id, $periodCode);

        return $balance;
    }

    /**
     * Ambil ringkasan semua limit & sisa kuota user.
     *
     * @param  User $user
     * @return array
     */
    public function getUserSummary(User $user): array
    {
        $limits = $this->getApplicableLimits($user);

        $summary = [];
        foreach ($limits as $limit) {
            $periodCode = $this->getPeriodCode($limit);
            $balance    = $this->getCachedBalance($user->id, $limit->id, $periodCode);
            $remaining  = $this->getUserBalance($user, $limit);

            $summary[] = [
                'limit_id'     => $limit->id,
                'name'         => $limit->name,
                'leave_type'   => $limit->leave_type,
                'period'       => $limit->period,
                'period_code'  => $periodCode,
                'max_days'     => $limit->max_days,
                'extra_days'   => $balance?->extra_days ?? 0,
                'used_days'    => $balance?->used_days ?? 0,
                'remaining'    => $remaining,
                'action_type'  => $limit->action_type,
            ];
        }

        return $summary;
    }

    // ─── Helper Methods ─────────────────────────────────────────────────────

    /**
     * Ambil grade user (misal: kelas/tingkat).
     * Bisa di-custom sesuai kebutuhan aplikasi.
     */
    private function getUserGrade(User $user): ?string
    {
        // Jika user adalah siswa, cari dari relasi siswa -> kelas -> tingkat
        if ($user->isRole(User::ROLE_SISWA) && $user->siswa && $user->siswa->kelas) {
            return (string) $user->siswa->kelas->tingkat ?? null;
        }

        // Untuk role lain, bisa return null
        return null;
    }

    /**
     * Ambil balance dari cache, jika tidak ada ambil dari DB.
     */
    private function getCachedBalance(int $userId, int $limitId, string $periodCode): ?LeaveBalance
    {
        $cacheKey = self::CACHE_KEY_BALANCE . "{$userId}_{$limitId}_{$periodCode}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId, $limitId, $periodCode) {
            return LeaveBalance::where([
                'user_id'       => $userId,
                'leave_limit_id' => $limitId,
                'period_code'   => $periodCode,
            ])->first();
        });
    }

    /**
     * Hapus cache balance.
     */
    private function forgetBalanceCache(int $userId, int $limitId, string $periodCode): void
    {
        $cacheKey = self::CACHE_KEY_BALANCE . "{$userId}_{$limitId}_{$periodCode}";
        Cache::forget($cacheKey);
    }

    /**
     * Generate kode semester berdasarkan tanggal.
     */
    private function getSemesterCode(\DateTimeInterface $date): string
    {
        $year = $date->format('Y');
        $month = (int) $date->format('m');

        // Semester ganjil: Juli - Desember (bulan 7-12)
        // Semester genap: Januari - Juni (bulan 1-6)
        if ($month >= 7) {
            return "{$year}-ganjil";
        }

        // Untuk semester genap, tahun akademik biasanya tahun sebelumnya
        return ($year - 1) . "-genap";
    }

    /**
     * Generate kode tahunan (tahun akademik).
     * Contoh: 2025-2026
     */
    private function getYearlyCode(\DateTimeInterface $date): string
    {
        $year = (int) $date->format('Y');
        $month = (int) $date->format('m');

        // Jika bulan >= 7 (Juli), tahun akademik dimulai
        if ($month >= 7) {
            return "{$year}-" . ($year + 1);
        }

        return ($year - 1) . "-{$year}";
    }
}
