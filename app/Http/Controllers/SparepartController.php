<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\SparepartBrand;
use App\Models\SparepartKategori;
use App\Models\StokMutasi;
use App\Models\Toko;
use App\Models\Setting;
use App\Exports\SparepartsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SparepartController extends Controller
{
    public function index(Request $request)
    {
        $spareparts = Sparepart::filter($request->only(['kategori', 'brand', 'toko', 'search']))
            ->orderBy('kategori')
            ->orderBy('nama_barang')
            ->paginate(10)
            ->withQueryString();

        $kategoriOptions = SparepartKategori::active()->ordered()->get(['id', 'name'])->toArray();
        $brandOptions = SparepartBrand::orderBy('name')->get(['id', 'name'])->toArray();
        $tokoOptions = Toko::orderBy('nama')->get(['id', 'nama'])->toArray();
        $tokos = Toko::orderBy('nama')->get();

        $editSparepart = null;
        if ($request->query('edit')) {
            $editSparepart = Sparepart::find((int) $request->query('edit'));
        }

        $pageData = null;
        $errors = Session::get('errors');
        if ($errors && $errors->any()) {
            $isTokoForm = old('_toko_form') === '1';
            $pageData = [
                'hasErrors' => true,
                'showTokoModal' => $isTokoForm,
                'old_nama_barang' => $isTokoForm ? '' : old('nama_barang', ''),
                'old_kategori' => $isTokoForm ? '' : old('kategori', ''),
                'old_brand' => $isTokoForm ? '' : old('brand', ''),
                'old_type_hp' => $isTokoForm ? '' : old('type_hp', ''),
                'old_stok' => $isTokoForm ? '' : old('stok', ''),
                'old_harga_beli' => $isTokoForm ? '' : old('harga_beli', ''),
                'old_toko' => $isTokoForm ? '' : old('toko', ''),
                'old_keterangan' => $isTokoForm ? '' : old('keterangan', ''),
                'editing_id' => $isTokoForm ? '' : old('editing_id'),
                '_method' => $isTokoForm ? 'POST' : old('_method', 'POST'),
                'toko_editing_id' => $isTokoForm ? old('_method') === 'PUT' ? old('editing_id', '') : '' : '',
            ];
        }

        $pageData = array_merge($pageData ?? [], [
            'kategoriOptions' => $kategoriOptions,
            'brandOptions' => $brandOptions,
            'tokoOptions' => $tokoOptions,
        ]);

        if ($editSparepart) {
            $pageData = array_merge($pageData, [
                'editSparepart' => $editSparepart->only(['id', 'nama_barang', 'kategori', 'brand', 'type_hp', 'harga_beli', 'toko', 'keterangan']),
            ]);
        }

        return view('spareparts.index', compact('spareparts', 'kategoriOptions', 'brandOptions', 'tokoOptions', 'tokos', 'pageData'));
    }

    public function create()
    {
        $kategoriOptions = SparepartKategori::active()->ordered()->get(['id', 'name'])->toArray();
        $brandOptions = SparepartBrand::orderBy('name')->get(['id', 'name'])->toArray();
        $tokoOptions = Toko::orderBy('nama')->get(['id', 'nama'])->toArray();
        return view('spareparts.create', compact('kategoriOptions', 'brandOptions', 'tokoOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string',
            'brand' => 'nullable|string|max:255',
            'type_hp' => 'nullable|string|max:255',
            'stok' => 'required|integer|min:0',
            'harga_beli' => 'nullable|numeric|min:0',
            'toko' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $sparepart = Sparepart::create($validated);

        if ($sparepart->stok > 0) {
            $sparepart->mutasi()->create([
                'tipe' => 'masuk',
                'jumlah' => $sparepart->stok,
                'stok_sebelum' => 0,
                'stok_sesudah' => $sparepart->stok,
                'keterangan' => 'Stok awal',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('spareparts.index')
            ->with('success', 'Sparepart berhasil ditambahkan.');
    }

    public function show(Sparepart $sparepart)
    {
        $sparepart->load(['mutasi.user']);
        $brandOptions = SparepartBrand::orderBy('name')->get(['id', 'name'])->toArray();
        return view('spareparts.show', compact('sparepart', 'brandOptions'));
    }

    public function edit(Sparepart $sparepart)
    {
        $kategoriOptions = SparepartKategori::active()->ordered()->get(['id', 'name'])->toArray();
        $brandOptions = SparepartBrand::orderBy('name')->get(['id', 'name'])->toArray();
        $tokoOptions = Toko::orderBy('nama')->get(['id', 'nama'])->toArray();
        return view('spareparts.edit', compact('sparepart', 'kategoriOptions', 'brandOptions', 'tokoOptions'));
    }

    public function update(Request $request, Sparepart $sparepart)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string',
            'brand' => 'nullable|string|max:255',
            'type_hp' => 'nullable|string|max:255',
            'harga_beli' => 'nullable|numeric|min:0',
            'toko' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $sparepart->update($validated);

        return redirect()->route('spareparts.index')
            ->with('success', 'Sparepart berhasil diperbarui.');
    }

    public function destroy(Sparepart $sparepart)
    {
        $sparepart->mutasi()->delete();
        $sparepart->delete();

        return redirect()->route('spareparts.index')
            ->with('success', 'Sparepart berhasil dihapus.');
    }

    public function mutasiCreate(Sparepart $sparepart)
    {
        return view('spareparts.mutasi-create', compact('sparepart'));
    }

    public function mutasiStore(Request $request, Sparepart $sparepart)
    {
        $validated = $request->validate([
            'tipe' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $stokSebelum = $sparepart->stok;

        if ($validated['tipe'] === 'keluar' && $validated['jumlah'] > $stokSebelum) {
            return redirect()->back()->withInput()
                ->with('error', 'Stok tidak mencukupi. Stok saat ini: ' . $stokSebelum);
        }

        $stokSesudah = $validated['tipe'] === 'masuk'
            ? $stokSebelum + $validated['jumlah']
            : $stokSebelum - $validated['jumlah'];

        DB::beginTransaction();
        try {
            $sparepart->update(['stok' => $stokSesudah]);

            $sparepart->mutasi()->create([
                'tipe' => $validated['tipe'],
                'jumlah' => $validated['jumlah'],
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'keterangan' => $validated['keterangan'],
                'user_id' => auth()->id(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan mutasi: ' . $e->getMessage());
        }

        return redirect()->route('spareparts.show', $sparepart)
            ->with('success', 'Mutasi stok berhasil dicatat.');
    }

    public function quickStoreKategori(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $name = trim($request->name);
        $existing = SparepartKategori::where('name', $name)->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Kategori sudah ada.']);
        }

        $maxSort = SparepartKategori::max('sort_order') ?? 0;
        $kategori = SparepartKategori::create([
            'name' => $name,
            'sort_order' => $maxSort + 1,
        ]);

        return response()->json(['success' => true, 'kategori' => $kategori]);
    }

    public function quickStoreBrand(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);

        $name = trim($request->name);
        $existing = SparepartBrand::where('name', $name)->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Brand sudah ada.']);
        }

        $brand = SparepartBrand::create(['name' => $name]);

        return response()->json(['success' => true, 'brand' => $brand]);
    }

    public function updateBrand(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $name = trim($request->name);
        $existing = SparepartBrand::where('name', $name)->where('id', '!=', $id)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Brand sudah ada.']);
        }
        $brand = SparepartBrand::findOrFail($id);
        Sparepart::where('brand', $brand->name)->update(['brand' => $name]);
        $brand->update(['name' => $name]);
        return response()->json(['success' => true, 'brand' => $brand->fresh()]);
    }

    public function destroyBrand($id)
    {
        $brand = SparepartBrand::findOrFail($id);
        Sparepart::where('brand', $brand->name)->update(['brand' => null]);
        $brand->delete();
        return response()->json(['success' => true]);
    }

    public function updateKategori(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $name = trim($request->name);
        $existing = SparepartKategori::where('name', $name)->where('id', '!=', $id)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Kategori sudah ada.']);
        }
        $kategori = SparepartKategori::findOrFail($id);
        Sparepart::where('kategori', $kategori->name)->update(['kategori' => $name]);
        $kategori->update(['name' => $name]);
        return response()->json(['success' => true, 'kategori' => $kategori->fresh()]);
    }

    public function destroyKategori($id)
    {
        $kategori = SparepartKategori::findOrFail($id);
        Sparepart::where('kategori', $kategori->name)->update(['kategori' => null]);
        $kategori->delete();
        return response()->json(['success' => true]);
    }

    public function exportExcel()
    {
        $sparepartNama = str_replace(' ', '-', Setting::getValue('nama_toko', 'Juneo-Phone'));
        return Excel::download(
            new SparepartsExport(request()->only(['kategori', 'brand', 'toko', 'search'])),
            'Data-Sparepart-' . $sparepartNama . '.xlsx'
        );
    }

    public function exportPdf()
    {
        $spareparts = Sparepart::filter(request()->only(['kategori', 'brand', 'toko', 'search']))
            ->orderBy('kategori')
            ->orderBy('nama_barang')
            ->get();
        $namaToko = Setting::getValue('nama_toko', 'Juneo Phone');
        $pdf = Pdf::loadView('exports.pdf-spareparts', compact('spareparts', 'namaToko'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isRemoteEnabled', true);

        $hash = md5(uniqid((string) mt_rand(), true));
        $filename = "temp/pdf-{$hash}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        $tempFiles = Storage::disk('public')->files('temp');
        foreach ($tempFiles as $file) {
            if (Storage::disk('public')->lastModified($file) < now()->subHour()->timestamp) {
                Storage::disk('public')->delete($file);
            }
        }

        $pdfUrl = route('storage.file', ['path' => $filename]);
        $favicon = Setting::getValue('logo')
            ? asset('storage/' . Setting::getValue('logo'))
            : asset('favicon.ico');

        return view('exports.pdf-preview', compact('pdfUrl', 'favicon', 'namaToko'));
    }
}
