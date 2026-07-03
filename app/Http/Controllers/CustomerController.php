<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::withCount('services')
            ->when(request('search'), function ($q, $search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return redirect()->route('customers.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:255',
            'no_hp' => 'nullable|max:20',
            'alamat' => 'nullable',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show(Customer $customer)
    {
        $customer->load('services');
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return redirect()->route('customers.index');
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'nama' => 'required|max:255',
            'no_hp' => 'nullable|max:20',
            'alamat' => 'nullable',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:255',
            'no_hp' => 'nullable|max:20',
            'alamat' => 'nullable',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'nama' => $customer->nama,
                'no_hp' => $customer->no_hp,
                'alamat' => $customer->alamat,
            ],
        ]);
    }

    public function destroy(Customer $customer)
    {
        $serviceCount = $customer->services()->count();
        if ($serviceCount > 0) {
            $activeCount = $customer->services()->whereIn('status', ['menunggu', 'dikerjakan'])->count();
            $message = "Pelanggan tidak dapat dihapus karena masih memiliki {$serviceCount} data service.";
            if ($activeCount > 0) {
                $message .= " ({$activeCount} service masih aktif: menunggu/dikerjakan)";
            }
            $message .= " Hapus service terlebih dahulu.";

            return redirect()->route('customers.index')->with('error', $message);
        }

        $customer->delete();
        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}
