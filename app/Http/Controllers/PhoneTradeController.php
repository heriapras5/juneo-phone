<?php

namespace App\Http\Controllers;

use App\Exports\PhoneTradesExport;
use App\Models\PhoneTrade;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PhoneTradeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:stok,terjual',
            'sumber' => 'nullable|in:langsung,service',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $filters = $request->only(['search', 'status', 'sumber', 'from', 'to']);

        $trades = PhoneTrade::filter($filters)
            ->orderByDesc('tanggal_masuk')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $summary = $this->buildSummary($filters);
        $hasPeriodFilter = !empty($filters['from']) || !empty($filters['to']);

        $editTrade = null;
        if ($request->filled('edit')) {
            $found = PhoneTrade::find($request->integer('edit'));
            if ($found) {
                $editTrade = [
                    'id' => $found->id,
                    'type_hp' => $found->type_hp,
                    'brand' => $found->brand,
                    'storage' => $found->storage,
                    'warna' => $found->warna,
                    'kondisi' => $found->kondisi,
                    'sumber' => $found->sumber,
                    'kelengkapan' => $found->kelengkapan,
                    'tanggal_masuk' => $found->tanggal_masuk?->format('Y-m-d'),
                    'harga_beli' => (float) $found->harga_beli,
                    'biaya_perbaikan' => (float) $found->biaya_perbaikan,
                    'penjual' => $found->penjual,
                    'status' => $found->status,
                    'tanggal_keluar' => $found->tanggal_keluar?->format('Y-m-d'),
                    'harga_jual' => $found->harga_jual !== null ? (float) $found->harga_jual : null,
                    'pembeli' => $found->pembeli,
                    'keterangan' => $found->keterangan,
                ];
            }
        }

        return view('phone-trades.index', compact('trades', 'filters', 'summary', 'hasPeriodFilter', 'editTrade'));
    }

    public function show(PhoneTrade $phoneTrade)
    {
        $phoneTrade->load(['histories.user']);

        return view('phone-trades.show', compact('phoneTrade'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $trade = PhoneTrade::create($data);

        return redirect()->route('phone-trades.index')
            ->with('success', "Data HP {$trade->type_hp} ({$trade->kode}) berhasil ditambahkan.");
    }

    public function update(Request $request, PhoneTrade $phoneTrade)
    {
        $data = $this->validatedData($request);

        $phoneTrade->update($data);

        return redirect()->route('phone-trades.index')
            ->with('success', "Data HP {$phoneTrade->type_hp} ({$phoneTrade->kode}) berhasil diperbarui.");
    }

    public function updateStatus(Request $request, PhoneTrade $phoneTrade)
    {
        $tanggalMasuk = $phoneTrade->tanggal_masuk->format('Y-m-d');

        if ($request->filled('harga_jual')) {
            $request->merge([
                'harga_jual' => preg_replace('/\D/', '', (string) $request->input('harga_jual')),
            ]);
        }

        $validated = $request->validate([
            'status' => 'required|in:stok,terjual',
            'tanggal_keluar' => 'nullable|required_if:status,terjual|date|after_or_equal:' . $tanggalMasuk,
            'harga_jual' => 'nullable|required_if:status,terjual|numeric|min:0',
            'pembeli' => 'nullable|string|max:255',
        ]);

        $phoneTrade->update($validated);

        if ($request->input('redirect_to') === 'index') {
            return redirect()->route('phone-trades.index')
                ->with('success', "Status HP {$phoneTrade->type_hp} ({$phoneTrade->kode}) berhasil diubah.");
        }

        return redirect()->route('phone-trades.show', $phoneTrade)
            ->with('success', "Status HP {$phoneTrade->type_hp} ({$phoneTrade->kode}) berhasil diubah.");
    }

    public function destroy(PhoneTrade $phoneTrade)
    {
        $phoneTrade->delete();

        return redirect()->route('phone-trades.index')
            ->with('success', 'Data jual beli HP berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'status', 'sumber', 'from', 'to']);
        $namaToko = str_replace(' ', '-', Setting::getValue('nama_toko', 'Juneo-Phone'));

        return Excel::download(
            new PhoneTradesExport($filters),
            'Laporan-Jual-Beli-HP-' . $namaToko . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['search', 'status', 'sumber', 'from', 'to']);

        $trades = PhoneTrade::filter($filters)
            ->orderByDesc('tanggal_masuk')
            ->orderByDesc('id')
            ->get();

        $summary = $this->buildSummary($filters);
        $namaToko = Setting::getValue('nama_toko', 'Juneo Phone');

        $pdf = Pdf::loadView('exports.pdf-phone-trades', compact('trades', 'summary', 'filters', 'namaToko'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions(['show_warnings' => true]);

        return $pdf->stream('Laporan-Jual-Beli-HP-' . str_replace(' ', '-', $namaToko) . '.pdf');
    }

    private function buildSummary(array $filters): array
    {
        $common = array_intersect_key($filters, array_flip(['search', 'sumber']));

        $stockQuery = PhoneTrade::filter($common)->where('status', PhoneTrade::STATUS_STOK);

        $soldQuery = PhoneTrade::filter($common)
            ->where('status', PhoneTrade::STATUS_TERJUAL)
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('tanggal_keluar', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('tanggal_keluar', '<=', $v));

        $omzet = (float) (clone $soldQuery)->sum('harga_jual');
        $modalTerjual = $this->sumModal(clone $soldQuery);

        return [
            'stock_count' => (clone $stockQuery)->count(),
            'stock_modal' => $this->sumModal(clone $stockQuery),
            'sold_count' => (clone $soldQuery)->count(),
            'omzet' => $omzet,
            'modal_terjual' => $modalTerjual,
            'laba' => $omzet - $modalTerjual,
        ];
    }

    private function sumModal($query): float
    {
        return (float) $query
            ->selectRaw('COALESCE(SUM(harga_beli + biaya_perbaikan), 0) as total')
            ->value('total');
    }

    private function validatedData(Request $request): array
    {
        foreach (['harga_beli', 'harga_jual', 'biaya_perbaikan'] as $field) {
            if ($request->filled($field)) {
                $request->merge([
                    $field => preg_replace('/\D/', '', (string) $request->input($field)),
                ]);
            }
        }

        $validated = $request->validate([
            'type_hp' => 'required|string|max:255',
            'brand' => 'nullable|string|max:100',
            'storage' => 'nullable|string|max:50',
            'warna' => 'nullable|string|max:50',
            'kondisi' => 'required|in:baru,bekas,minus',
            'sumber' => 'nullable|in:langsung,service',
            'kelengkapan' => 'nullable|string|max:255',
            'tanggal_masuk' => 'required|date',
            'harga_beli' => 'required|numeric|min:0',
            'biaya_perbaikan' => 'nullable|numeric|min:0',
            'penjual' => 'nullable|string|max:255',
            'status' => 'required|in:stok,terjual',
            'tanggal_keluar' => 'nullable|required_if:status,terjual|date|after_or_equal:tanggal_masuk',
            'harga_jual' => 'nullable|required_if:status,terjual|numeric|min:0',
            'pembeli' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $validated['sumber'] = $validated['sumber'] ?? PhoneTrade::SUMBER_LANGSUNG;
        $validated['biaya_perbaikan'] = $validated['biaya_perbaikan'] ?? 0;

        if ($validated['status'] === PhoneTrade::STATUS_STOK) {
            $validated['tanggal_keluar'] = null;
            $validated['harga_jual'] = null;
            $validated['pembeli'] = null;
        }

        return $validated;
    }
}
