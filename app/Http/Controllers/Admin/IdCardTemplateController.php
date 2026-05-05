<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdCardTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IdCardTemplateController extends Controller
{
    public function index()
    {
        $templates = IdCardTemplate::latest()->paginate(10);
        return view('admin.id-card-templates.index', compact('templates'));
    }

    public function create()
    {
        $defaultConfig = [
            'canvas' => ['width' => 350, 'height' => 500],
            'elements' => [
                'photo' => ['x' => 100, 'y' => 120, 'w' => 150, 'h' => 200, 'show' => true],
                'qr' => ['x' => 100, 'y' => 340, 'w' => 150, 'h' => 150, 'show' => true],
                'name' => ['x' => 175, 'y' => 80, 'size' => 20, 'color' => '#000000', 'show' => true, 'align' => 'center'],
                'id_number' => ['x' => 175, 'y' => 105, 'size' => 14, 'color' => '#555555', 'show' => true, 'align' => 'center'],
                'class' => ['x' => 175, 'y' => 325, 'size' => 14, 'color' => '#555555', 'show' => true, 'align' => 'center'],
            ]
        ];
        return view('admin.id-card-templates.form', [
            'template' => new IdCardTemplate(['config' => $defaultConfig]),
            'isEdit' => false
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:siswa,guru,staff',
            'background' => 'nullable|image|max:2048',
            'config' => 'required|json',
        ]);

        $data = $request->only(['name', 'type', 'is_active']);
        $data['config'] = json_decode($request->config, true);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('background')) {
            $data['background_path'] = $request->file('background')->store('id_cards', 'public');
        }

        // Deactivate others of same type if this is active
        if ($data['is_active']) {
            IdCardTemplate::where('type', $data['type'])->update(['is_active' => false]);
        }

        IdCardTemplate::create($data);

        return redirect()->route('admin.id-card-templates.index')->with('success', 'Template berhasil dibuat.');
    }

    public function edit(IdCardTemplate $idCardTemplate)
    {
        return view('admin.id-card-templates.form', [
            'template' => $idCardTemplate,
            'isEdit' => true
        ]);
    }

    public function update(Request $request, IdCardTemplate $idCardTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:siswa,guru,staff',
            'background' => 'nullable|image|max:2048',
            'config' => 'required|json',
        ]);

        $data = $request->only(['name', 'type', 'is_active']);
        $data['config'] = json_decode($request->config, true);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('background')) {
            if ($idCardTemplate->background_path) {
                Storage::disk('public')->delete($idCardTemplate->background_path);
            }
            $data['background_path'] = $request->file('background')->store('id_cards', 'public');
        }

        if ($data['is_active']) {
            IdCardTemplate::where('type', $data['type'])
                ->where('id', '!=', $idCardTemplate->id)
                ->update(['is_active' => false]);
        }

        $idCardTemplate->update($data);

        return redirect()->route('admin.id-card-templates.index')->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(IdCardTemplate $idCardTemplate)
    {
        if ($idCardTemplate->background_path) {
            Storage::disk('public')->delete($idCardTemplate->background_path);
        }
        $idCardTemplate->delete();
        return back()->with('success', 'Template berhasil dihapus.');
    }
}
