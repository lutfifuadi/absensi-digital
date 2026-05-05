<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ApiIntegrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Tampilkan semua token API yang aktif
        $tokens = PersonalAccessToken::with('tokenable')->orderBy('created_at', 'desc')->get();
        // Karena sistem kita belum secara intens melacak logs API calls di table khusus,
        // kita minimal tampilkan token info dulu.
        return view('admin.api.index', compact('tokens'));
    }

    /**
     * Store a newly created token.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token_name' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        // Buat token (dikaitkan ke admin yang login)
        $token = $user->createToken($request->token_name);

        // Plain text token hanya bisa dilihat SEKALI saat pertama kali di generate
        return back()->with('success_token', $token->plainTextToken);
    }

    /**
     * Revoke (delete) a specific token.
     */
    public function destroy($id)
    {
        $token = PersonalAccessToken::findOrFail($id);
        $token->delete();

        return back()->with('success', 'Token API berhasil dicabut (revoke).');
    }
}
