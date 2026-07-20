<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;

class TokoController extends Controller
{
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tokos,nama',
        ]);

        $toko = Toko::create(['nama' => $validated['name']]);

        return response()->json([
            'success' => true,
            'toko' => ['id' => $toko->id, 'name' => $toko->nama],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:tokos,nama',
        ]);

        Toko::create($validated);

        return redirect()->route('spareparts.index')->with('success', 'Toko berhasil ditambahkan.');
    }

    public function update(Request $request, Toko $toko)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:tokos,nama,' . $toko->id,
        ]);

        $toko->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'toko' => $toko->fresh()]);
        }

        return redirect()->route('spareparts.index')->with('success', 'Toko berhasil diperbarui.');
    }

    public function destroy(Toko $toko)
    {
        $toko->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('spareparts.index')->with('success', 'Toko berhasil dihapus.');
    }
}
