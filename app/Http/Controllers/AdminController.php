<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('admin.index', compact('users'));
    }

    public function create()
    {
        return redirect()->route('admin.index');
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,superadmin',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'active';

        User::create($data);

        $label = $data['role'] === User::ROLE_SUPERADMIN ? 'SuperAdmin' : 'Admin';
        return redirect()->route('admin.index')->with('success', $label . ' berhasil ditambahkan');
    }

    public function edit(User $admin)
    {
        return redirect()->route('admin.index');
    }

    public function update(Request $request, User $admin)
    {
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->isSuperAdmin();

        abort_unless(
            $isSuperAdmin || $admin->id === $currentUser->id,
            403,
            'Anda hanya dapat mengelola akun Anda sendiri.'
        );

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,superadmin',
            'status' => 'required|in:active,pending,rejected',
        ]);

        // Admin hanya boleh mengubah data akunnya sendiri, bukan role/status.
        if (!$isSuperAdmin) {
            $data['role'] = $admin->role;
            $data['status'] = $admin->status;
        }

        $admin->name = $data['name'];
        $admin->email = $data['email'];
        $admin->role = $data['role'];
        $admin->status = $data['status'];
        if (!empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }
        $admin->save();

        $label = $admin->role === User::ROLE_SUPERADMIN ? 'SuperAdmin' : 'Admin';
        return redirect()->route('admin.index')->with('success', $label . ' berhasil diperbarui');
    }

    public function destroy(User $admin)
    {
        $this->ensureSuperAdmin();

        if ($admin->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri');
        }

        $label = $admin->role === User::ROLE_SUPERADMIN ? 'SuperAdmin' : 'Admin';
        $admin->delete();
        return redirect()->route('admin.index')->with('success', $label . ' berhasil dihapus');
    }

    /**
     * Manajemen pengguna (tambah/ubah/hapus) hanya boleh dilakukan Super Admin.
     */
    private function ensureSuperAdmin(): void
    {
        abort_unless(
            auth()->user()->isSuperAdmin(),
            403,
            'Hanya Super Admin yang dapat mengelola pengguna.'
        );
    }
}
