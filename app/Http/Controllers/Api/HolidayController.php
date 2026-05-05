<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        if ($request->has('jenis') && in_array($request->jenis, ['national', 'school'])) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->has('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->has('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        $holidays = $query->orderBy('tanggal')->get();

        return response()->json([
            'success' => true,
            'data' => $holidays,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'nama' => 'required|string|max:255',
            'jenis' => 'required|in:national,school',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $holiday = Holiday::create([
            'tanggal' => $request->tanggal,
            'nama' => $request->nama,
            'jenis' => $request->jenis,
            'is_national_holiday' => $request->jenis === 'national' ? true : false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hari libur berhasil ditambahkan',
            'data' => $holiday,
        ], 201);
    }

    public function show($id)
    {
        $holiday = Holiday::find($id);

        if (!$holiday) {
            return response()->json([
                'success' => false,
                'message' => 'Hari libur tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $holiday,
        ]);
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::find($id);

        if (!$holiday) {
            return response()->json([
                'success' => false,
                'message' => 'Hari libur tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'sometimes|date',
            'nama' => 'sometimes|string|max:255',
            'jenis' => 'sometimes|in:national,school',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $holiday->update($request->only(['tanggal', 'nama', 'jenis']));

        if ($request->has('jenis')) {
            $holiday->update(['is_national_holiday' => $request->jenis === 'national']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hari libur berhasil diperbarui',
            'data' => $holiday,
        ]);
    }

    public function destroy($id)
    {
        $holiday = Holiday::find($id);

        if (!$holiday) {
            return response()->json([
                'success' => false,
                'message' => 'Hari libur tidak ditemukan',
            ], 404);
        }

        $holiday->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hari libur berhasil dihapus',
        ]);
    }

    public function checkHoliday($date)
    {
        $holiday = Holiday::where('tanggal', $date)->first();

        return response()->json([
            'success' => true,
            'is_holiday' => $holiday ? true : false,
            'data' => $holiday,
        ]);
    }
}