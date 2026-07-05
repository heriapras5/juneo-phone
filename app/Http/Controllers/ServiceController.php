<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DamageCategory;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\Setting;
use App\Models\TypeHpOption;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceController extends Controller
{
    public function queue()
    {
        $services = Service::with(['customer', 'serviceItems'])
            ->queue()
            ->paginate(20)
            ->withQueryString();

        $completedServices = Service::with(['customer', 'serviceItems'])
            ->where('status', 'selesai')
            ->orderBy('tanggal_selesai', 'desc')
            ->orderBy('tanggal', 'desc')
            ->paginate(10, ['*'], 'completed_page')
            ->withQueryString();

        $statusCount = Service::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $urgentCount = Service::whereIn('status', ['menunggu', 'dikerjakan'])
            ->where('tanggal', '<=', now()->subDays(5))
            ->count();

        $warningCount = Service::whereIn('status', ['menunggu', 'dikerjakan'])
            ->where('tanggal', '>', now()->subDays(5))
            ->where('tanggal', '<=', now()->subDays(3))
            ->count();

        return view('services.queue', compact('services', 'completedServices', 'statusCount', 'urgentCount', 'warningCount'));
    }

    public function index()
    {
        $services = Service::with(['customer', 'serviceItems'])
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('service_number', 'like', "%{$search}%")
                      ->orWhere('type_hp', 'like', "%{$search}%")
                      ->orWhereHas('serviceItems', fn($q) => $q->where('type_hp', 'like', "%{$search}%")->orWhere('kerusakan', 'like', "%{$search}%"))
                      ->orWhereHas('customer', fn($q) => $q->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when(request('date_from'), fn($q, $v) => $q->whereDate('tanggal', '>=', $v))
            ->when(request('date_to'), fn($q, $v) => $q->whereDate('tanggal', '<=', $v))
            ->when(request('status'), fn($q, $v) => $q->where('status', $v))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::orderBy('nama')->get(['id', 'nama', 'no_hp']);

        $statusCount = Service::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $kerusakanOptions = DamageCategory::active()->ordered()->pluck('name')->toArray();

        $typeHpOptions = TypeHpOption::active()->ordered()->pluck('name')->toArray();

        $editService = null;
        if (request('edit')) {
            $editService = Service::with(['customer', 'serviceItems'])->find((int) request('edit'));
        }

        return view('services.index', compact('services', 'customers', 'statusCount', 'kerusakanOptions', 'editService', 'typeHpOptions'));
    }

    public function create()
    {
        return redirect()->route('services.index');
    }

    public function store(Request $request)
    {
        if ($request->has('items')) {
            $items = $request->input('items');
            foreach ($items as $i => $item) {
                if (isset($item['jasa'])) {
                    $items[$i]['jasa'] = preg_replace('/\.(?=\d{3}(?:\.|$))/', '', $item['jasa']);
                }
                if (isset($item['pengeluaran'])) {
                    $items[$i]['pengeluaran'] = preg_replace('/\.(?=\d{3}(?:\.|$))/', '', $item['pengeluaran']);
                }
            }
            $request->merge(['items' => $items]);
        }

        $validated = $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'customer_id' => 'required|exists:customers,id',
            'keterangan' => 'nullable|string|max:1000',
            'tanggal_selesai' => 'nullable|date',
            'estimasi_selesai' => 'nullable|date|after_or_equal:tanggal',
            'teknisi' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.type_hp' => 'required|string|max:255',
            'items.*.kerusakan' => 'required|string',
            'items.*.jasa' => 'nullable|numeric|min:0|max:999999999',
            'items.*.pengeluaran' => 'nullable|numeric|min:0|max:999999999',
            'items.*.keterangan' => 'nullable|string|max:1000',
        ], [], [
            'items.*.type_hp' => 'Tipe HP',
            'items.*.kerusakan' => 'Kerusakan',
            'items.*.jasa' => 'Biaya Jasa',
            'items.*.pengeluaran' => 'Biaya Pengeluaran',
            'items' => 'Item service',
        ]);

        $items = $request->input('items', []);
        $totalJasa = 0;
        $totalPengeluaran = 0;
        foreach ($items as $item) {
            $totalJasa += (float) ($item['jasa'] ?? 0);
            $totalPengeluaran += (float) ($item['pengeluaran'] ?? 0);
        }

        $firstItem = reset($items);
        $validated['type_hp'] = $firstItem['type_hp'];
        $validated['kerusakan'] = $firstItem['kerusakan'];
        $validated['jasa'] = $totalJasa;
        $validated['pengeluaran'] = $totalPengeluaran;

        if ($totalJasa < $totalPengeluaran) {
            session()->flash('warning', 'Peringatan: Biaya jasa lebih kecil dari modal. Service akan rugi!');
        }

        DB::beginTransaction();
        try {
            $service = $this->createServiceWithRetry($validated);

            $serviceItems = [];
            foreach ($items as $item) {
                $kerusakan = json_decode($item['kerusakan'] ?? '[]', true);
                if (!is_array($kerusakan)) $kerusakan = [$item['kerusakan']];
                $si = new ServiceItem([
                    'type_hp' => $item['type_hp'],
                    'kerusakan' => $kerusakan,
                    'jasa' => $item['jasa'] ?? 0,
                    'pengeluaran' => $item['pengeluaran'] ?? 0,
                    'keterangan' => $item['keterangan'] ?? '',
                ]);
                $serviceItems[] = $si;
            }
            if (!empty($serviceItems)) {
                $service->serviceItems()->saveMany($serviceItems);
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $message = 'Gagal menyimpan data service.';
            if (str_contains($e->getMessage(), 'service_number')) {
                $message = 'Gagal membuat nomor service. Silakan coba lagi.';
            }
            return redirect()->back()->withInput()->with('error', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }

        return redirect()->route('services.index')
            ->with('success', 'Data service berhasil ditambahkan.');
    }

    public function show(Service $service)
    {
        $service->load(['customer', 'histories.user', 'serviceItems']);
        return view('services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        return redirect()->route('services.index', ['edit' => $service->id]);
    }

    public function update(Request $request, Service $service)
    {
        if ($request->has('items')) {
            $items = $request->input('items');
            foreach ($items as $i => $item) {
                if (isset($item['jasa'])) {
                    $items[$i]['jasa'] = preg_replace('/\.(?=\d{3}(?:\.|$))/', '', $item['jasa']);
                }
                if (isset($item['pengeluaran'])) {
                    $items[$i]['pengeluaran'] = preg_replace('/\.(?=\d{3}(?:\.|$))/', '', $item['pengeluaran']);
                }
            }
            $request->merge(['items' => $items]);
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'keterangan' => 'nullable|string|max:1000',
            'status' => 'nullable|in:menunggu,dikerjakan,selesai,diambil,batal',
            'tanggal_selesai' => 'nullable|date',
            'estimasi_selesai' => 'nullable|date|after_or_equal:tanggal',
            'teknisi' => 'nullable|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.type_hp' => 'required|string|max:255',
            'items.*.kerusakan' => 'required|string',
            'items.*.jasa' => 'nullable|numeric|min:0|max:999999999',
            'items.*.pengeluaran' => 'nullable|numeric|min:0|max:999999999',
            'items.*.keterangan' => 'nullable|string|max:1000',
        ], [], [
            'items.*.type_hp' => 'Tipe HP',
            'items.*.kerusakan' => 'Kerusakan',
            'items.*.jasa' => 'Biaya Jasa',
            'items.*.pengeluaran' => 'Biaya Pengeluaran',
            'items' => 'Item service',
        ]);

        if (isset($validated['status']) && $validated['status'] !== $service->status) {
            $allowedTransitions = Service::allowedTransitions();
            $current = $service->status;
            $requested = $validated['status'];
            if (!in_array($requested, (array)($allowedTransitions[$current] ?? []))) {
                $allowed = $allowedTransitions[$current] ?? [];
                $allowedLabels = array_map(fn($s) => Service::statusLabels()[$s] ?? $s, $allowed);
                return redirect()->back()->withInput()->with('error', 'Status hanya bisa diubah ke: ' . implode(' → ', $allowedLabels) . '.');
            }
        }

        $items = $request->input('items', []);
        $totalJasa = 0;
        $totalPengeluaran = 0;
        foreach ($items as $item) {
            $totalJasa += (float) ($item['jasa'] ?? 0);
            $totalPengeluaran += (float) ($item['pengeluaran'] ?? 0);
        }

        $firstItem = reset($items);
        $validated['type_hp'] = $firstItem['type_hp'];
        $validated['kerusakan'] = $firstItem['kerusakan'];
        $validated['jasa'] = $totalJasa;
        $validated['pengeluaran'] = $totalPengeluaran;

        if ($totalJasa < $totalPengeluaran) {
            session()->flash('warning', 'Peringatan: Biaya jasa lebih kecil dari modal. Service akan rugi!');
        }

        // Biarkan booted event mengisi tanggal_selesai jika kosong
        if (empty($validated['tanggal_selesai'])) {
            unset($validated['tanggal_selesai']);
        }

        DB::beginTransaction();
        try {
            $service->update($validated);

            $existingIds = $service->serviceItems()->pluck('id')->toArray();
            $submittedIds = [];
            foreach ($items as $item) {
                if (!empty($item['id'])) {
                    $submittedIds[] = (int) $item['id'];
                }
            }
            $idsToDelete = array_diff($existingIds, $submittedIds);
            if (!empty($idsToDelete)) {
                $service->serviceItems()->whereIn('id', $idsToDelete)->delete();
            }

            foreach ($items as $item) {
                $kerusakan = json_decode($item['kerusakan'] ?? '[]', true);
                if (!is_array($kerusakan)) $kerusakan = [$item['kerusakan']];
                $itemData = [
                    'type_hp' => $item['type_hp'],
                    'kerusakan' => $kerusakan,
                    'jasa' => (float) ($item['jasa'] ?? 0),
                    'pengeluaran' => (float) ($item['pengeluaran'] ?? 0),
                    'keterangan' => $item['keterangan'] ?? null,
                ];
                if (!empty($item['id']) && in_array((int) $item['id'], $existingIds)) {
                    $serviceItem = $service->serviceItems()->find((int) $item['id']);
                    if ($serviceItem) {
                        $serviceItem->update($itemData);
                    }
                } else {
                    $service->serviceItems()->create($itemData);
                }
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $message = 'Gagal memperbarui data service.';
            if (str_contains($e->getMessage(), 'service_number')) {
                $message = 'Gagal membuat nomor service. Silakan coba lagi.';
            }
            return redirect()->back()->withInput()->with('error', $message);
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }

        return redirect()->route('services.index')
            ->with('success', 'Data service berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('services.index')
            ->with('success', 'Data service berhasil dihapus.');
    }

    public function updateStatus(Request $request, Service $service)
    {
        $request->validate([
            'status' => 'required|in:menunggu,dikerjakan,selesai,diambil,batal',
            'garansi' => 'nullable',
        ]);

        $allowedTransitions = Service::allowedTransitions();

        $requested = $request->status;
        $current = $service->status;

        if (!in_array($requested, (array)($allowedTransitions[$current] ?? []))) {
            $allowed = $allowedTransitions[$current] ?? [];
            $allowedLabels = array_map(fn($s) => Service::statusLabels()[$s] ?? $s, $allowed);
            $allowedStr = implode(' → ', $allowedLabels);
            return response()->json([
                'success' => false,
                'message' => 'Status hanya bisa diubah ke: ' . $allowedStr . '.',
            ], 422);
        }

        $data = ['status' => $requested];
        if ($request->filled('garansi')) {
            $data['garansi'] = $request->garansi;
        }
        $service->update($data);

        $statusCount = Service::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'success' => true,
            'message' => "Status berhasil diubah ke " . Service::statusLabels()[$requested] . ".",
            'status' => $requested,
            'label' => Service::statusLabels()[$requested],
            'class' => Service::statusClasses()[$requested],
            'statusCount' => $statusCount,
            'nextTransitions' => Service::allowedTransitions()[$requested] ?? [],
        ]);
    }

    public function updateGaransi(Request $request, Service $service)
    {
        $request->validate([
            'garansi' => 'nullable',
        ]);

        $service->update(['garansi' => $request->garansi]);

        return redirect()->back()->with('success', 'Garansi berhasil diperbarui.');
    }

    public function quickUpdate(Request $request, Service $service)
    {
        $request->validate([
            'teknisi' => 'nullable|string',
        ]);

        $data = array_filter($request->only(['teknisi']), fn($v) => !is_null($v));

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang diperbarui.',
            ], 422);
        }

        $service->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data service berhasil diperbarui.',
            'teknisi' => $service->teknisi,
        ]);
    }

    public function printPdf(Service $service)
    {
        $service->load('customer', 'serviceItems');
        $pdf = Pdf::loadView('exports.pdf-service', compact('service'));
        $itemCount = $service->serviceItems->count();
        $height = min(800, 240 + ($itemCount * 20) + 10);
        $pdf->setPaper([0, 0, 164.41, $height]);
        $pdf->setOption('isRemoteEnabled', true);
        return $pdf->stream('nota-service-' . $service->service_number . '.pdf');
    }

    protected function createServiceWithRetry(array $validated, int $maxAttempts = 3): Service
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return Service::create($validated);
            } catch (QueryException $e) {
                if ($attempt === $maxAttempts || !str_contains($e->getMessage(), 'service_number')) {
                    throw $e;
                }
                // Regenerate service number on duplicate
                $validated['service_number'] = null;
            }
        }
        throw new \RuntimeException('Gagal generate nomor service setelah ' . $maxAttempts . ' percobaan.');
    }
}
