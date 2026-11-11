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
            ->when(!auth()->user()->isSuperAdmin(), fn ($query) => $query->where('role', User::ROLE_ADMIN))
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
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,superadmin',
        ]);

        $this->ensureRoleAllowed($data['role']);

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
        $this->ensureCanManage($admin);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,superadmin',
            'status' => 'required|in:active,pending,rejected',
        ]);

        $this->ensureRoleAllowed($data['role']);

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
        $this->ensureCanManage($admin);

        if ($admin->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri');
        }

        $label = $admin->role === User::ROLE_SUPERADMIN ? 'SuperAdmin' : 'Admin';
        $admin->delete();
        return redirect()->route('admin.index')->with('success', $label . ' berhasil dihapus');
    }

    /**
     * Pastikan pengguna yang login boleh mengelola akun target.
     * Admin hanya boleh mengelola akun admin, SuperAdmin boleh mengelola semua.
     */
    private function ensureCanManage(User $user): void
    {
        abort_unless(
            in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPERADMIN], true),
            403,
            'Akun ini tidak dapat dikelola.'
        );

        abort_unless(
            auth()->user()->isSuperAdmin() || $user->role === User::ROLE_ADMIN,
            403,
            'Anda tidak memiliki izin untuk mengelola akun ini.'
        );
    }

    /**
     * Pastikan role yang diberikan sesuai dengan hak akses pengguna yang login.
     * Admin hanya boleh membuat/menetapkan role admin.
     */
    private function ensureRoleAllowed(string $role): void
    {
        abort_unless(
            auth()->user()->isSuperAdmin() || $role === User::ROLE_ADMIN,
            403,
            'Anda tidak memiliki izin untuk menetapkan role ini.'
        );
    }
}
