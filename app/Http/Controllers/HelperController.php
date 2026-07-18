<?php

namespace App\Http\Controllers;

use App\Models\Helper;
use Illuminate\Http\Request;

class HelperController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'warna' => 'nullable|string|max:255',
        ]);

        $warna = $validated['warna'] ?? 'bg-gray-100 text-gray-600 border-gray-200';

        $helper = Helper::create([
            'nama' => $validated['nama'],
            'warna' => $warna,
        ]);

        return redirect()->route('settings')
            ->with('success', 'Helper berhasil ditambahkan.');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'warna' => 'nullable|string|max:255',
        ]);

        $helper = Helper::create([
            'nama' => $validated['nama'],
            'warna' => $validated['warna'] ?? 'bg-gray-100 text-gray-600 border-gray-200',
        ]);

        return response()->json([
            'success' => true,
            'helper' => $helper,
        ]);
    }

    public function update(Request $request, Helper $helper)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'warna' => 'nullable|string|max:255',
        ]);

        $helper->update([
            'nama' => $validated['nama'],
            'warna' => $validated['warna'] ?? $helper->warna,
        ]);

        return redirect()->route('settings')
            ->with('success', 'Helper berhasil diperbarui.');
    }

    public function destroy(Helper $helper)
    {
        $helper->delete();

        return redirect()->route('settings')
            ->with('success', 'Helper berhasil dihapus.');
    }
}
