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
}
