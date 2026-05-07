<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LicenseDeliveryMail;
use App\Models\PembelianLisensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class PembelianLisensiController extends Controller
{
    public function index(Request $request)
    {
        $query = PembelianLisensi::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_klien', 'like', "%{$search}%")
                  ->orWhere('email_klien', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%")
                  ->orWhere('license_key', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $pembelian = $query->paginate(20)->withQueryString();

        return view('admin.pembelian-lisensi.index', compact('pembelian'));
    }

    public function create()
    {
        return view('admin.pembelian-lisensi.form', [
            'pembelian' => new PembelianLisensi(),
            'isEdit'    => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_klien'     => 'required|string|max:255',
            'email_klien'    => 'required|email|max:255',
            'domain'         => 'nullable|string|max:255',
            'payment_status' => 'required|in:menunggu,lunas',
            'catatan'        => 'nullable|string|max:1000',
            'expires_at'     => 'nullable|date|after:today',
        ]);

        $pembelian = PembelianLisensi::create($validated);

        // Jika langsung lunas saat dibuat, otomatis generate lisensi & kirim email
        if ($validated['payment_status'] === 'lunas') {
            $this->processPembayaranLunas($pembelian);
        }

        Log::info('PembelianLisensi dibuat', ['id' => $pembelian->id, 'email' => $pembelian->email_klien]);

        return redirect()->route('admin.pembelian-lisensi.index')
            ->with('success', 'Data pembelian berhasil disimpan.');
    }

    public function show(PembelianLisensi $pembelianLisensi)
    {
        return view('admin.pembelian-lisensi.show', [
            'pembelian' => $pembelianLisensi,
        ]);
    }

    public function edit(PembelianLisensi $pembelianLisensi)
    {
        return view('admin.pembelian-lisensi.form', [
            'pembelian' => $pembelianLisensi,
            'isEdit'    => true,
        ]);
    }

    public function update(Request $request, PembelianLisensi $pembelianLisensi)
    {
        $validated = $request->validate([
            'nama_klien'     => 'required|string|max:255',
            'email_klien'    => 'required|email|max:255',
            'domain'         => 'nullable|string|max:255',
            'payment_status' => 'required|in:menunggu,lunas',
            'catatan'        => 'nullable|string|max:1000',
            'expires_at'     => 'nullable|date',
        ]);

        $wasNotLunas = $pembelianLisensi->payment_status !== 'lunas';
        $pembelianLisensi->update($validated);

        // Jika baru saja diubah menjadi lunas dan belum punya license_key
        if ($validated['payment_status'] === 'lunas' && $wasNotLunas && empty($pembelianLisensi->license_key)) {
            $this->processPembayaranLunas($pembelianLisensi);
        }

        return redirect()->route('admin.pembelian-lisensi.index')
            ->with('success', 'Data pembelian berhasil diperbarui.');
    }

    /**
     * Konfirmasi pembayaran lunas: generate license key dan kirim email ke klien.
     */
    public function konfirmasiPembayaran(PembelianLisensi $pembelianLisensi)
    {
        if ($pembelianLisensi->payment_status === 'lunas' && !empty($pembelianLisensi->license_key)) {
            return back()->with('info', 'Pembayaran sudah dikonfirmasi sebelumnya. Gunakan tombol "Kirim Ulang Email" jika perlu.');
        }

        $this->processPembayaranLunas($pembelianLisensi);

        return back()->with('success', 'Pembayaran dikonfirmasi. License key berhasil digenerate dan email telah dikirim ke ' . $pembelianLisensi->email_klien);
    }

    /**
     * Kirim ulang email lisensi ke klien.
     */
    public function kirimUlangEmail(PembelianLisensi $pembelianLisensi)
    {
        if (empty($pembelianLisensi->license_key)) {
            return back()->with('error', 'License key belum digenerate. Konfirmasi pembayaran terlebih dahulu.');
        }

        try {
            $downloadUrl = $this->getDownloadUrl($pembelianLisensi);
            Mail::to($pembelianLisensi->email_klien)->send(new LicenseDeliveryMail($pembelianLisensi, $downloadUrl));
            Log::info('Email lisensi dikirim ulang', ['id' => $pembelianLisensi->id]);
            return back()->with('success', 'Email lisensi berhasil dikirim ulang ke ' . $pembelianLisensi->email_klien);
        } catch (\Exception $e) {
            Log::error('Gagal kirim email lisensi ulang', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    /**
     * Cabut (revoke) lisensi.
     */
    public function revokeLisensi(PembelianLisensi $pembelianLisensi)
    {
        $pembelianLisensi->update(['status' => 'revoked']);
        Log::warning('Lisensi dicabut', ['id' => $pembelianLisensi->id, 'license_key' => $pembelianLisensi->license_key]);
        return back()->with('success', 'Lisensi berhasil dicabut.');
    }

    public function destroy(PembelianLisensi $pembelianLisensi)
    {
        $pembelianLisensi->delete();
        return redirect()->route('admin.pembelian-lisensi.index')
            ->with('success', 'Data pembelian berhasil dihapus.');
    }

    /**
     * Proses pembayaran lunas: generate license, token download, update status, kirim email.
     */
    private function processPembayaranLunas(PembelianLisensi $pembelian): void
    {
        // Generate license key jika belum ada
        if (empty($pembelian->license_key)) {
            $pembelian->license_key = PembelianLisensi::generateLicenseKey();
        }

        $pembelian->download_token   = PembelianLisensi::generateDownloadToken();
        $pembelian->status           = 'active';
        $pembelian->payment_status   = 'lunas';
        $pembelian->activated_at     = now();
        $pembelian->save();

        // Kirim email ke klien
        try {
            $downloadUrl = $this->getDownloadUrl($pembelian);
            Mail::to($pembelian->email_klien)->send(new LicenseDeliveryMail($pembelian, $downloadUrl));
            Log::info('Email lisensi dikirim', ['id' => $pembelian->id, 'email' => $pembelian->email_klien]);
        } catch (\Exception $e) {
            Log::error('Gagal kirim email lisensi', ['id' => $pembelian->id, 'error' => $e->getMessage()]);
            // Tidak throw — license tetap disimpan meski email gagal
        }
    }

    /**
     * Buat signed download URL (berlaku 7 hari).
     */
    private function getDownloadUrl(PembelianLisensi $pembelian): string
    {
        return URL::temporarySignedRoute(
            'download.app',
            now()->addDays(7),
            ['token' => $pembelian->download_token]
        );
    }
}
