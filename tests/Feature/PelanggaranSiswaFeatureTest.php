<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\KategoriPelanggaran;
use App\Models\JenisPelanggaran;
use App\Models\PelanggaranSiswa;
use App\Models\PelanggaranFoto;
use App\Models\PelanggaranSp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendPelanggaranWhatsAppNotification;

class PelanggaranSiswaFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $guru;
    private Siswa $siswa;
    private Kelas $kelas;
    private TahunAkademik $tahunAkademik;
    private JenisPelanggaran $jenis;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Queue::fake();

        // Buat Tahun Akademik
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'is_aktif' => true,
        ]);

        // Buat Kelas
        $this->kelas = Kelas::create([
            'nama' => 'X-RPL',
            'tingkat' => '10',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        // Buat Users & Siswa
        $this->superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->guru = User::factory()->create(['role' => User::ROLE_GURU]);

        $this->siswa = Siswa::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_SISWA])->id,
            'nis' => '10001',
            'nisn' => '1000100010',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-05-05',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'no_hp_ortu' => '081234567890',
        ]);

        // Buat Kategori & Jenis Pelanggaran
        $kategori = KategoriPelanggaran::create([
            'nama' => 'Kerapian',
            'keterangan' => 'Pelanggaran terkait kerapian'
        ]);

        $this->jenis = JenisPelanggaran::create([
            'kategori_id' => $kategori->id,
            'nama' => 'Rambut Panjang',
            'bobot_poin' => 10,
            'deskripsi' => 'Rambut melebihi kerah baju'
        ]);
    }

    /** @test */
    public function super_admin_can_access_pelanggaran_index()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.pelanggaran.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.pelanggaran.index');
    }

    /** @test */
    public function super_admin_can_create_pelanggaran_and_trigger_whatsapp_job()
    {
        $file = UploadedFile::fake()->image('bukti.jpg');

        $response = $this->actingAs($this->superAdmin)->post(route('admin.pelanggaran.store'), [
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $this->jenis->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tanggal_kejadian' => '2026-07-21',
            'keterangan' => 'Rambut tidak rapi saat upacara bendera.',
            'foto' => $file,
        ]);

        if (session('error')) {
            $this->fail('Store failed with error: ' . session('error'));
        }

        $response->assertRedirect(route('admin.pelanggaran.index'));
        $response->assertSessionHas('success');

        // Pastikan masuk ke database
        $this->assertDatabaseHas('pelanggaran_siswa', [
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $this->jenis->id,
            'poin_saat_itu' => 10,
            'dicatat_oleh' => $this->superAdmin->id,
        ]);

        // Pastikan foto tersimpan di private storage
        $pelanggaran = PelanggaranSiswa::first();
        $fotoRecord = PelanggaranFoto::where('pelanggaran_id', $pelanggaran->id)->first();
        $this->assertNotNull($fotoRecord);
        Storage::disk('local')->assertExists($fotoRecord->path_foto);

        // Pastikan WhatsApp Job di-dispatch
        Queue::assertPushed(SendPelanggaranWhatsAppNotification::class, function ($job) {
            return $job->tipeNotif === 'pelanggaran_baru';
        });
    }

    /** @test */
    public function guru_can_only_edit_own_pelanggaran_within_24_hours()
    {
        // 1. Pelanggaran dibuat oleh guru sendiri
        $pelanggaranSendiri = PelanggaranSiswa::create([
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $this->jenis->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tanggal_kejadian' => '2026-07-21',
            'keterangan' => 'Catatan 1',
            'poin_saat_itu' => 10,
            'dicatat_oleh' => $this->guru->id,
            'created_at' => now(), // Saat ini
        ]);

        $response = $this->actingAs($this->guru)->get(route('admin.pelanggaran.edit', $pelanggaranSendiri->id));
        $response->assertStatus(200);

        // 2. Pelanggaran dibuat oleh guru sendiri tapi > 24 jam yang lalu
        $pelanggaranLama = PelanggaranSiswa::create([
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $this->jenis->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tanggal_kejadian' => '2026-07-20',
            'keterangan' => 'Catatan Lama',
            'poin_saat_itu' => 10,
            'dicatat_oleh' => $this->guru->id,
        ]);
        // Set created_at ke 25 jam yang lalu
        $pelanggaranLama->created_at = now()->subHours(25);
        $pelanggaranLama->save();

        $responseLama = $this->actingAs($this->guru)->get(route('admin.pelanggaran.edit', $pelanggaranLama->id));
        $responseLama->assertStatus(403); // Forbidden

        // 3. Pelanggaran dicatat oleh Super Admin
        $pelanggaranAdmin = PelanggaranSiswa::create([
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $this->jenis->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tanggal_kejadian' => '2026-07-21',
            'keterangan' => 'Catatan Admin',
            'poin_saat_itu' => 10,
            'dicatat_oleh' => $this->superAdmin->id,
        ]);

        $responseAdmin = $this->actingAs($this->guru)->get(route('admin.pelanggaran.edit', $pelanggaranAdmin->id));
        $responseAdmin->assertStatus(403); // Forbidden
    }

    /** @test */
    public function super_admin_can_delete_pelanggaran_but_guru_cannot()
    {
        $pelanggaran = PelanggaranSiswa::create([
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $this->jenis->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tanggal_kejadian' => '2026-07-21',
            'keterangan' => 'Catatan Budi',
            'poin_saat_itu' => 10,
            'dicatat_oleh' => $this->superAdmin->id,
        ]);

        // Guru coba hapus
        $responseGuru = $this->actingAs($this->guru)->delete(route('admin.pelanggaran.destroy', $pelanggaran->id), [
            'alasan_penghapusan' => 'Bukan wewenang guru'
        ]);
        $responseGuru->assertStatus(403);

        // Super admin hapus
        $responseAdmin = $this->actingAs($this->superAdmin)->delete(route('admin.pelanggaran.destroy', $pelanggaran->id), [
            'alasan_penghapusan' => 'Salah input'
        ]);
        $responseAdmin->assertRedirect(route('admin.pelanggaran.index'));
        $this->assertSoftDeleted('pelanggaran_siswa', ['id' => $pelanggaran->id]);

        // Cek activity log alasan tercatat
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->superAdmin->id,
            'action' => 'delete',
            'module' => 'pelanggaran_siswa',
        ]);
    }
}
