<?php
$models = ['User', 'Siswa', 'Guru', 'StaffTataUsaha', 'Kelas', 'AbsensiSiswa', 'AbsensiGuru', 'AbsensiStaff', 'IzinSakit', 'Kegiatan', 'JadwalPelajaran', 'TahunAkademik', 'Pengaturan', 'ReminderSettings', 'NotificationTemplate', 'Holiday', 'AuthorizedDevice', 'Badge', 'ActivityAttendance'];

foreach ($models as $m) {
    $f = __DIR__ . '/../app/Models/' . $m . '.php';
    if (file_exists($f)) {
        $c = file_get_contents($f);
        if (!str_contains($c, 'HasTenant')) {
            $c = preg_replace('/class\s+'.$m.'\s+extends\s+[^{]+{/s', "$0\n    use \App\Traits\HasTenant;\n", $c);
            file_put_contents($f, $c);
            echo "Updated $m\n";
        }
    }
}
