<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaAutoreplyKeyword;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class WaAutoreplyKeywordController extends Controller
{
    public function index()
    {
        $keywords = WaAutoreplyKeyword::with('template')->latest()->paginate(15);
        
        // Ambil template notifikasi yang bertipe autoreply (dimulai dengan autoreply_)
        $templates = NotificationTemplate::where('type', 'like', 'autoreply_%')->get();

        return view('admin.wa-gateway.keywords', compact('keywords', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'keyword'                     => 'required|string|max:255',
            'match_type'                  => 'required|in:Exact,Contains',
            'is_validation_required'      => 'required|boolean',
            'is_active'                   => 'required|boolean',
            'notification_template_type'  => 'required|string|exists:notification_templates,type',
        ], [
            'keyword.required'                     => 'Kata kunci wajib diisi.',
            'match_type.required'                  => 'Tipe kecocokan wajib dipilih.',
            'is_validation_required.required'      => 'Status validasi nomor wajib dipilih.',
            'is_active.required'                   => 'Status aktif wajib dipilih.',
            'notification_template_type.required'  => 'Template notifikasi wajib dipilih.',
            'notification_template_type.exists'    => 'Template notifikasi tidak valid.',
        ]);

        WaAutoreplyKeyword::create([
            'keyword'                    => $request->keyword,
            'match_type'                 => $request->match_type,
            'is_validation_required'     => $request->is_validation_required,
            'is_active'                  => $request->is_active,
            'notification_template_type' => $request->notification_template_type,
        ]);

        return redirect()->route('admin.wa-gateway.keywords.index')->with('success', 'Kata kunci autoreply berhasil ditambahkan.');
    }

    public function update(Request $request, WaAutoreplyKeyword $keyword)
    {
        $request->validate([
            'keyword'                     => 'required|string|max:255',
            'match_type'                  => 'required|in:Exact,Contains',
            'is_validation_required'      => 'required|boolean',
            'is_active'                   => 'required|boolean',
            'notification_template_type'  => 'required|string|exists:notification_templates,type',
        ], [
            'keyword.required'                     => 'Kata kunci wajib diisi.',
            'match_type.required'                  => 'Tipe kecocokan wajib dipilih.',
            'is_validation_required.required'      => 'Status validasi nomor wajib dipilih.',
            'is_active.required'                   => 'Status aktif wajib dipilih.',
            'notification_template_type.required'  => 'Template notifikasi wajib dipilih.',
            'notification_template_type.exists'    => 'Template notifikasi tidak valid.',
        ]);

        $keyword->update([
            'keyword'                    => $request->keyword,
            'match_type'                 => $request->match_type,
            'is_validation_required'     => $request->is_validation_required,
            'is_active'                  => $request->is_active,
            'notification_template_type' => $request->notification_template_type,
        ]);

        return redirect()->route('admin.wa-gateway.keywords.index')->with('success', 'Kata kunci autoreply berhasil diupdate.');
    }

    public function destroy(WaAutoreplyKeyword $keyword)
    {
        $keyword->delete();

        return redirect()->route('admin.wa-gateway.keywords.index')->with('success', 'Kata kunci autoreply berhasil dihapus.');
    }

    public function export()
    {
        $keywords = WaAutoreplyKeyword::all(['keyword', 'match_type', 'is_validation_required', 'is_active', 'notification_template_type']);
        
        $templateTypes = $keywords->pluck('notification_template_type')->unique()->filter()->values();
        $templates = NotificationTemplate::whereIn('type', $templateTypes)
            ->orWhere('type', 'like', 'autoreply_%')
            ->get(['type', 'content']);

        $exportData = [
            'app'          => 'Aplikasi-Presensi',
            'feature'      => 'wa_autoreply_keywords',
            'version'      => 1,
            'exported_at'  => now()->toDateTimeString(),
            'templates'    => $templates,
            'keywords'     => $keywords,
        ];

        $filename = 'wa_autoreply_keywords_' . date('Y-m-d_H-i-s') . '.json';

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'json_file' => 'required|file|mimes:json,txt|max:2048',
        ], [
            'json_file.required' => 'File JSON wajib diunggah.',
            'json_file.file'     => 'File yang diunggah harus berupa file.',
            'json_file.mimes'    => 'Format file harus berupa JSON.',
            'json_file.max'      => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            $content = file_get_contents($request->file('json_file')->getRealPath());
            $data = json_decode($content, true);

            if (!is_array($data) || !isset($data['keywords']) || !is_array($data['keywords'])) {
                return redirect()->back()->withErrors(['json_file' => 'Format file JSON tidak valid. Data keywords tidak ditemukan.']);
            }

            $importedTemplatesCount = 0;
            if (isset($data['templates']) && is_array($data['templates'])) {
                foreach ($data['templates'] as $t) {
                    if (isset($t['type']) && isset($t['content'])) {
                        NotificationTemplate::updateOrCreate(
                            ['type' => $t['type']],
                            [
                                'content' => $t['content'],
                            ]
                        );
                        $importedTemplatesCount++;
                    }
                }
            }

            $importedKeywordsCount = 0;
            foreach ($data['keywords'] as $kw) {
                if (isset($kw['keyword']) && isset($kw['notification_template_type'])) {
                    WaAutoreplyKeyword::updateOrCreate(
                        ['keyword' => $kw['keyword']],
                        [
                            'match_type'                 => $kw['match_type'] ?? 'Contains',
                            'is_validation_required'     => $kw['is_validation_required'] ?? 0,
                            'is_active'                  => $kw['is_active'] ?? 1,
                            'notification_template_type' => $kw['notification_template_type'],
                        ]
                    );
                    $importedKeywordsCount++;
                }
            }

            return redirect()->route('admin.wa-gateway.keywords.index')
                ->with('success', "Berhasil mengimpor {$importedKeywordsCount} kata kunci dan {$importedTemplatesCount} template notifikasi.");

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['json_file' => 'Gagal mengimpor file: ' . $e->getMessage()]);
        }
    }
}
