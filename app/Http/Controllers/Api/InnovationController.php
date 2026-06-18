<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\IzinSakit;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\AbsensiSiswa;
use App\Models\TahunAkademik;
use App\Models\AttendanceAnalytics;
use App\Models\Badge;
use App\Models\StudentBadge;
use App\Models\ClassLeaderboard;
use App\Models\OfflineQueue;
use App\Models\AuthorizedDevice;
use App\Models\AbsensiKegiatan;
use App\Models\ActivityNotificationQueue;
use App\Models\ReminderSettings;
use App\Models\Pengaturan;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InnovationController extends Controller
{
    public function getNotificationTemplates()
    {
        $templates = NotificationTemplate::all();
        return response()->json(['data' => $templates]);
    }

    public function updateNotificationTemplate(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string',
            'content' => 'required|string',
        ]);

        $template = NotificationTemplate::findOrFail($id);
        $template->update($request->only(['type', 'content']));

        return response()->json(['success' => true, 'data' => $template]);
    }

    public function getAttendanceAnalytics(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $kelasId = $request->get('kelas_id');

        $query = AttendanceAnalytics::with(['kelas', 'tahunAkademik'])
            ->where('date', $date);

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        $analytics = $query->get();
        return response()->json(['data' => $analytics]);
    }

    public function analyzeAttendance(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'date' => 'required|date',
        ]);

        $kelasId = $request->kelas_id;
        $date = $request->date;
        $taId = TahunAkademik::where('is_aktif', true)->first()?->id;

        $absensi = AbsensiSiswa::where('kelas_id', $kelasId)
            ->where('tanggal', $date)
            ->get();

        $total = $absensi->count();
        $hadir = $absensi->where('status', 'Hadir')->count();
        $terlambat = $absensi->where('status', 'Terlambat')->count();
        $sakit = $absensi->where('status', 'Sakit')->count();
        $izin = $absensi->where('status', 'Izin')->count();
        $alpha = $total - $hadir - $terlambat - $sakit - $izin;

        $percentageKehadiran = $total > 0 ? ($hadir / $total) * 100 : 0;
        $percentageKeterlambatan = $total > 0 ? ($terlambat / $total) * 100 : 0;

        $alertTriggered = false;
        if ($percentageKehadiran < 75) {
            $alertTriggered = true;
        }

        $analytics = AttendanceAnalytics::updateOrCreate(
            ['kelas_id' => $kelasId, 'tahun_akademik_id' => $taId, 'date' => $date],
            [
                'total_students' => $total,
                'hadir_tepat_waktu' => $hadir,
                'terlambat' => $terlambat,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpha' => $alpha,
                'persentase_kehadiran' => $percentageKehadiran,
                'persentase_keterlambatan' => $percentageKeterlambatan,
                'alert_triggered' => $alertTriggered,
                'alert_note' => $alertTriggered ? 'Tingkat kehadiran di bawah 75%' : null,
            ]
        );

        return response()->json(['success' => true, 'data' => $analytics]);
    }

    public function getBadges()
    {
        $badges = Badge::where('is_active', true)->get();
        return response()->json(['data' => $badges]);
    }

    public function assignBadge(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'badge_id' => 'required|exists:badges,id',
        ]);

        $studentBadge = StudentBadge::firstOrCreate([
            'siswa_id' => $request->siswa_id,
            'badge_id' => $request->badge_id,
        ], ['earned_at' => now()]);

        return response()->json(['success' => true, 'data' => $studentBadge]);
    }

    public function getLeaderboard(Request $request)
    {
        $taId = $request->get('tahun_akademik_id', 
            TahunAkademik::where('is_aktif', true)->first()?->id);

        $leaderboard = ClassLeaderboard::with('kelas')
            ->where('tahun_akademik_id', $taId)
            ->orderBy('rank')
            ->get();

        return response()->json(['data' => $leaderboard]);
    }

    public function calculateLeaderboard(Request $request)
    {
        $taId = $request->get('tahun_akademik_id',
            TahunAkademik::where('is_aktif', true)->first()?->id);

        $kelasList = Kelas::all();

        $results = [];
        foreach ($kelasList as $kelas) {
            $absensi = AbsensiSiswa::where('kelas_id', $kelas->id)
                ->where('tahun_akademik_id', $taId)
                ->get();

            $total = $absensi->count();
            $present = $absensi->whereIn('status', ['Hadir', 'Terlambat'])->count();
            $percentage = $total > 0 ? ($present / $total) * 100 : 0;

            $results[] = [
                'kelas_id' => $kelas->id,
                'tahun_akademik_id' => $taId,
                'total_attendance' => $total,
                'total_present' => $present,
                'percentage' => $percentage,
            ];
        }

        usort($results, fn($a, $b) => $b['percentage'] <=> $a['percentage']);

        ClassLeaderboard::where('tahun_akademik_id', $taId)->delete();
        foreach ($results as $index => $result) {
            ClassLeaderboard::create([
                'kelas_id' => $result['kelas_id'],
                'tahun_akademik_id' => $result['tahun_akademik_id'],
                'rank' => $index + 1,
                'total_attendance' => $result['total_attendance'],
                'total_present' => $result['total_present'],
                'percentage' => $result['percentage'],
                'calculated_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'data' => $results]);
    }

    public function queueOfflineEvent(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'payload' => 'required|array',
            'device_uuid' => 'required|string',
        ]);

        $queue = OfflineQueue::create([
            'event_type' => $request->event_type,
            'payload' => $request->payload,
            'device_uuid' => $request->device_uuid,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'data' => $queue]);
    }

    public function syncOfflineEvents(Request $request)
    {
        $deviceUuid = $request->get('device_uuid');
        
        $queues = OfflineQueue::where('device_uuid', $deviceUuid)
            ->where('status', 'pending')
            ->where('retry_count', '<', 3)
            ->get();

        $synced = [];
        foreach ($queues as $queue) {
            try {
                DB::beginTransaction();
                
                switch ($queue->event_type) {
                    case 'absensi':
                        AbsensiSiswa::create($queue->payload);
                        break;
                }

                $queue->update(['status' => 'synced', 'synced_at' => now()]);
                $synced[] = $queue->id;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $queue->increment('retry_count');
                $queue->update(['error_message' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => true, 'synced' => $synced]);
    }

    public function getReminderSettings()
    {
        $settings = ReminderSettings::all();
        return response()->json(['data' => $settings]);
    }

    public function updateReminderSettings(Request $request, $id)
    {
        $request->validate([
            'is_enabled' => 'boolean',
            'channel' => 'in:whatsapp,sms,both',
            'send_before_minutes' => 'integer|min:0',
            'custom_message' => 'nullable|string',
            'notify_parent' => 'boolean',
        ]);

        $settings = ReminderSettings::findOrFail($id);
        $settings->update($request->only([
            'is_enabled', 'channel', 'send_before_minutes', 
            'custom_message', 'notify_parent'
        ]));

        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function sendReminder(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'reminder_type' => 'required|string',
        ]);

        $siswa = Siswa::findOrFail($request->siswa_id);
        $waService = new WhatsAppService();

        $template = NotificationTemplate::where('type', $request->reminder_type)->first();
        $message = $template?->content ?? 'Reminder absensi';

        $number = $siswa->no_hp_ortu ?? $siswa->user?->no_hp;
        
        if ($number) {
            $waService->sendMessage($number, $message);
        }

        return response()->json(['success' => true]);
    }

    public function getActivityAttendance(Request $request)
    {
        $kegiatanId = $request->get('kegiatan_id');
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);

        $kegiatan = \App\Models\Kegiatan::find($kegiatanId);

        if (!$kegiatan) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $perPage,
                'total' => 0,
                'from' => null,
                'to' => null,
            ]);
        }

        // Ambil data yang sudah ada di tabel absensi_kegiatan
        $attendance = AbsensiKegiatan::with(['siswa.kelas', 'kegiatan'])
            ->where('kegiatan_id', $kegiatanId)
            ->get();

        $existingSiswaIds = $attendance->pluck('siswa_id')->toArray();

        // Cari siswa yang seharusnya ikut kegiatan ini tapi belum ada datanya di absensi_kegiatan
        $querySiswa = Siswa::with('kelas');

        // Filter berdasarkan target_tingkat
        if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
            $targetTingkats = $kegiatan->target_tingkat;
            $querySiswa->whereHas('kelas', function($q) use ($targetTingkats) {
                $q->whereIn('tingkat', $targetTingkats);
            });
        }

        // Filter berdasarkan target_peserta (ID Kelas)
        if ($kegiatan->target_peserta && count($kegiatan->target_peserta) > 0) {
            $targetKelasIds = $kegiatan->target_peserta;
            // Jika target_tingkat juga diisi, gunakan OR
            if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
                $querySiswa->orWhereIn('kelas_id', $targetKelasIds);
            } else {
                $querySiswa->whereIn('kelas_id', $targetKelasIds);
            }
        }

        $siswaLainnya = $querySiswa->whereNotIn('id', $existingSiswaIds)->get();

        // Gabungkan data
        $resultData = $attendance->toArray();
        foreach ($siswaLainnya as $siswa) {
            $resultData[] = [
                'id' => null,
                'kegiatan_id' => $kegiatanId,
                'siswa_id' => $siswa->id,
                'status' => null,
                'keterangan' => null,
                'jam_absen' => null,
                'siswa' => $siswa->toArray(),
                'kegiatan' => $kegiatan->toArray(),
            ];
        }

        // Manual pagination pada merged collection
        $total = count($resultData);
        $lastPage = max(1, (int) ceil($total / $perPage));

        // Pastikan page tidak melebihi lastPage
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $offset = ($page - 1) * $perPage;
        $items = array_slice($resultData, $offset, $perPage);

        $from = $total > 0 ? $offset + 1 : null;
        $to = $total > 0 ? min($offset + $perPage, $total) : null;

        return response()->json([
            'data' => $items,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function recordActivityAttendance(Request $request)
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:kegiatan,id',
            'siswa_id' => 'required|exists:siswa,id',
            'status' => 'required|in:hadir,tidak_hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string',
        ]);

        $kegiatan = \App\Models\Kegiatan::findOrFail($request->kegiatan_id);
        $siswa = Siswa::findOrFail($request->siswa_id);

        // Validasi target peserta
        $isTarget = false;
        if (!$kegiatan->target_tingkat && !$kegiatan->target_peserta) {
            $isTarget = true;
        } else {
            if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
                if ($siswa->kelas && in_array($siswa->kelas->tingkat, $kegiatan->target_tingkat)) {
                    $isTarget = true;
                }
            }
            if (!$isTarget && $kegiatan->target_peserta && count($kegiatan->target_peserta) > 0) {
                if (in_array($siswa->kelas_id, $kegiatan->target_peserta)) {
                    $isTarget = true;
                }
            }
        }

        if (!$isTarget) {
            return response()->json([
                'success' => false, 
                'message' => 'Siswa tidak termasuk dalam target peserta kegiatan ini.'
            ], 403);
        }

        $status = $request->status;
        if ($status === 'tidak_hadir') {
            $status = 'alpha';
        }

        $jamAbsen = $status === 'hadir' ? now() : null;

        $attendance = AbsensiKegiatan::updateOrCreate(
            ['kegiatan_id' => $request->kegiatan_id, 'siswa_id' => $request->siswa_id],
            [
                'status' => $status,
                'keterangan' => $request->keterangan,
                'jam_absen' => $jamAbsen,
            ]
        );

        return response()->json(['success' => true, 'data' => $attendance, 'message' => 'Absensi berhasil dicatat']);
    }

    public function updateDeviceOfflineMode(Request $request, $id)
    {
        $request->validate([
            'offline_mode_enabled' => 'required|boolean',
            'max_retry_attempts' => 'integer|min:1|max:10',
        ]);

        $device = AuthorizedDevice::findOrFail($id);
        $device->update($request->only(['offline_mode_enabled', 'max_retry_attempts']));

        return response()->json(['success' => true, 'data' => $device]);
    }
}