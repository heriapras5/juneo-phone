<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;

class TokoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:tokos,nama',
        ]);

        Toko::create($validated);

        return redirect()->route('spareparts.index')->with('success_toko', 'Toko berhasil ditambahkan.');
    }

    public function update(Request $request, Toko $toko)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:tokos,nama,' . $toko->id,
        ]);

        $toko->update($validated);

        return redirect()->route('spareparts.index')->with('success_toko', 'Toko berhasil diperbarui.');
    }

    public function destroy(Toko $toko)
    {
        $toko->delete();

        return redirect()->route('spareparts.index')->with('success_toko', 'Toko berhasil dihapus.');
    }
}
