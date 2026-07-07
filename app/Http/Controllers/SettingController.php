<?php

namespace App\Http\Controllers;

use App\Models\DamageCategory;
use App\Models\Setting;
use App\Models\TypeHpOption;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'nama_toko' => Setting::getValue('nama_toko', 'Juneo Phone'),
            'subtitle' => Setting::getValue('subtitle', 'Service Hp Android & iPhone'),
            'alamat' => Setting::getValue('alamat', ''),
            'telepon' => Setting::getValue('telepon', ''),
            'logo' => Setting::getValue('logo', ''),
            'biaya_bank_jago' => Setting::getValue('biaya_bank_jago', '110000'),
            'biaya_fee_edi' => Setting::getValue('biaya_fee_edi', '60000'),
            'biaya_fee_nur' => Setting::getValue('biaya_fee_nur', '30000'),
            'biaya_lainnya' => Setting::getValue('biaya_lainnya', '0'),
            'nota_telepon' => Setting::getValue('nota_telepon', ''),
            'nota_rekening' => Setting::getValue('nota_rekening', ''),
        ];

        $damageCategories = DamageCategory::orderBy('sort_order')->orderBy('name')->get();

        return view('settings.index', compact('settings', 'damageCategories'));
    }

    public function update(Request $request)
    {
        foreach (['biaya_bank_jago', 'biaya_fee_edi', 'biaya_fee_nur', 'biaya_lainnya'] as $field) {
            if ($request->has($field)) {
                $request->merge([$field => str_replace('.', '', $request->input($field))]);
            }
        }

        $validated = $request->validate([
            'nama_toko' => 'required|max:255',
            'subtitle' => 'nullable|max:255',
            'alamat' => 'nullable',
            'telepon' => 'nullable|max:20',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'biaya_bank_jago' => 'nullable|integer|min:0',
            'biaya_fee_edi' => 'nullable|integer|min:0',
            'biaya_fee_nur' => 'nullable|integer|min:0',
            'biaya_lainnya' => 'nullable|integer|min:0',
            'nota_telepon' => 'nullable|max:20',
            'nota_rekening' => 'nullable',
        ]);

        foreach (['nama_toko', 'subtitle', 'alamat', 'telepon', 'biaya_bank_jago', 'biaya_fee_edi', 'biaya_fee_nur', 'biaya_lainnya', 'nota_telepon', 'nota_rekening'] as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $validated[$key] ?? '']
            );
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logo', 'public');
            Setting::updateOrCreate(
                ['key' => 'logo'],
                ['value' => $path]
            );
        }

        Setting::flushCache();

        return redirect()->route('settings')
            ->with('success', 'Pengaturan berhasil diperbarui.');
    }

    public function storeDamageCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $maxSort = DamageCategory::max('sort_order') ?? 0;

        DamageCategory::create([
            'name' => $validated['name'],
            'sort_order' => $maxSort + 1,
            'is_active' => true,
        ]);

        return redirect()->route('settings')
            ->with('success', 'Jenis kerusakan berhasil ditambahkan.');
    }

    public function updateDamageCategory(Request $request, DamageCategory $damageCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $damageCategory->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('settings')
            ->with('success', 'Jenis kerusakan berhasil diperbarui.');
    }

    public function quickStoreDamageCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $maxSort = DamageCategory::max('sort_order') ?? 0;

        $category = DamageCategory::create([
            'name' => $validated['name'],
            'sort_order' => $maxSort + 1,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'delete_url' => route('settings.damage-categories.destroy', $category),
            ],
        ]);
    }

    public function destroyDamageCategory($damageCategory)
    {
        $category = DamageCategory::find($damageCategory);

        if (!$category) {
            return redirect()->route('settings')
                ->with('error', 'Jenis kerusakan tidak ditemukan atau sudah dihapus.');
        }

        $category->delete();

        return redirect()->route('settings')
            ->with('success', 'Jenis kerusakan berhasil dihapus.');
    }

    public function quickStoreTypeHp(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $maxSort = TypeHpOption::max('sort_order') ?? 0;

        $option = TypeHpOption::create([
            'name' => $validated['name'],
            'sort_order' => $maxSort + 1,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $option->id,
                'name' => $option->name,
            ],
        ]);
    }

    public function destroyTypeHp($typeHpOption)
    {
        $option = TypeHpOption::find($typeHpOption);

        if (!$option) {
            return redirect()->route('settings')
                ->with('error', 'Type HP tidak ditemukan atau sudah dihapus.');
        }

        $option->delete();

        return redirect()->route('settings')
            ->with('success', 'Type HP berhasil dihapus.');
    }

}
