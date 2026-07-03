<?php

namespace App\Http\Controllers;

use App\Models\BiayaHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BiayaHarianController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $bulan = $request->query('bulan', now()->format('Y-m'));

        $biaya = BiayaHarian::whereDate('tanggal', $date)->get()->keyBy('kategori');

        $riwayat = BiayaHarian::whereYear('tanggal', substr($bulan, 0, 4))
            ->whereMonth('tanggal', substr($bulan, 5, 2))
            ->select(DB::raw('DATE(tanggal) as tanggal'), DB::raw('SUM(jumlah) as total'))
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy('tanggal', 'desc')
            ->get();

        $defaults = BiayaHarian::defaultValues();
        $kategoriLabels = BiayaHarian::kategoriLabels();

        return view('biaya-harian.index', compact('biaya', 'riwayat', 'defaults', 'kategoriLabels', 'date', 'bulan'));
    }

    public function update(Request $request)
    {
        $items = $request->input('items', []);
        foreach ($items as $key => $item) {
            if (isset($item['jumlah'])) {
                $value = $item['jumlah'];
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
                $items[$key]['jumlah'] = $value;
            }
        }
        $request->merge(['items' => $items]);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'items' => 'required|array',
            'items.*.kategori' => 'required|in:bank_jago,fee_edi,fee_nur,lainnya',
            'items.*.jumlah' => 'nullable|numeric|min:0',
            'items.*.keterangan' => 'nullable|string|max:255',
        ]);

        $dateOnly = Carbon::parse($validated['tanggal'])->format('Y-m-d');

        DB::beginTransaction();
        try {
            foreach ($validated['items'] as $item) {
                $jumlah = isset($item['jumlah']) && $item['jumlah'] !== '' ? (float) $item['jumlah'] : 0;
                $existing = BiayaHarian::whereDate('tanggal', $dateOnly)
                    ->where('kategori', $item['kategori'])
                    ->first();

                $kategoriLabels = BiayaHarian::kategoriLabels();

                if ($existing) {
                    $existing->update([
                        'tanggal' => $dateOnly,
                        'jumlah' => $jumlah,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                } else {
                    BiayaHarian::create([
                        'tanggal' => $dateOnly,
                        'kategori' => $item['kategori'],
                        'nama' => $kategoriLabels[$item['kategori']] ?? $item['kategori'],
                        'jumlah' => $jumlah,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan biaya harian: ' . $e->getMessage());
        }

        return redirect()->route('biaya-harian.index', ['date' => $dateOnly])
            ->with('success', 'Biaya harian berhasil disimpan.');
    }

    public function destroy(Request $request, $date)
    {
        $cleanDate = Carbon::parse($date)->format('Y-m-d');
        $kategori = $request->input('kategori');

        try {
            $query = BiayaHarian::whereDate('tanggal', $cleanDate);
            if ($kategori && in_array($kategori, ['bank_jago', 'fee_edi', 'fee_nur', 'lainnya'])) {
                $query->where('kategori', $kategori);
            }
            $query->delete();

            $msg = $kategori
                ? "Biaya {$kategori} untuk tanggal {$cleanDate} berhasil dihapus."
                : "Biaya harian untuk tanggal {$cleanDate} berhasil dihapus.";

            return redirect()->route('biaya-harian.index', [
                'date' => $cleanDate,
                'bulan' => $request->input('bulan', now()->format('Y-m')),
            ])->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menghapus biaya harian: ' . $e->getMessage());
        }
    }


}
