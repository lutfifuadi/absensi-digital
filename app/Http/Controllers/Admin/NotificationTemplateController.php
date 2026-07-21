<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = NotificationTemplate::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $templates = $query->latest()->paginate(10)->withQueryString();
        $types = NotificationTemplate::TYPES;

        return view('admin.notification-templates.index', compact('templates', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.notification-templates.form', [
            'template' => new NotificationTemplate(),
            'isEdit' => false,
            'types' => NotificationTemplate::TYPES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(NotificationTemplate::TYPES)),
            'content' => 'required|string',
        ]);

        NotificationTemplate::create($request->only(['type', 'content']));

        return redirect()->route('admin.notification-templates.index')
            ->with('success', 'Template notifikasi berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        return view('admin.notification-templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        return view('admin.notification-templates.form', [
            'template' => $template,
            'isEdit' => true,
            'types' => NotificationTemplate::TYPES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(NotificationTemplate::TYPES)),
            'content' => 'required|string',
        ]);

        $template = NotificationTemplate::findOrFail($id);
        $template->update($request->only(['type', 'content']));

        return redirect()->route('admin.notification-templates.index')
            ->with('success', 'Template notifikasi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->delete();
        return back()->with('success', 'Template notifikasi berhasil dihapus.');
    }

    /**
     * Export all notification templates as JSON.
     */
    public function export()
    {
        $templates = NotificationTemplate::all(['type', 'content']);
        $filename = 'template-notifikasi-' . date('Y-m-d') . '.json';

        return response()->streamDownload(function () use ($templates) {
            echo $templates->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import notification templates from JSON file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:json|max:2048',
        ]);

        $file = $request->file('import_file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        // Validasi format JSON
        if (!is_array($data)) {
            return back()->with('error', 'Format file JSON tidak valid. Harus berupa array.');
        }

        $count = 0;
        $errors = [];

        foreach ($data as $index => $item) {
            if (!isset($item['type']) || !isset($item['content'])) {
                $errors[] = 'Baris ke-' . ($index + 1) . ': field "type" dan "content" wajib ada.';
                continue;
            }

            // Validasi type harus string
            if (!is_string($item['type']) || empty(trim($item['type']))) {
                $errors[] = 'Baris ke-' . ($index + 1) . ': "type" harus berupa string dan tidak kosong.';
                continue;
            }

            // Validasi content harus string
            if (!is_string($item['content'])) {
                $errors[] = 'Baris ke-' . ($index + 1) . ': "content" harus berupa string.';
                continue;
            }

            NotificationTemplate::updateOrCreate(
                ['type' => trim($item['type'])],
                ['content' => $item['content']]
            );
            $count++;
        }

        $message = "Berhasil mengimpor {$count} template notifikasi.";
        if (!empty($errors)) {
            $message .= ' Namun, ' . count($errors) . ' baris dilewati karena kesalahan: ' . implode('; ', $errors);
            return back()->with('error', $message);
        }

        return back()->with('success', $message);
    }
}
