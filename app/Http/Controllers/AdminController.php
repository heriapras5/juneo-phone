<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', ['admin', 'superadmin'])->orderBy('role')->orderBy('name')->get();
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

        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'active';

        User::create($data);

        $label = $data['role'] === 'superadmin' ? 'SuperAdmin' : 'Admin';
        return redirect()->route('admin.index')->with('success', $label . ' berhasil ditambahkan');
    }

    public function edit(User $admin)
    {
        return redirect()->route('admin.index');
    }

    public function update(Request $request, User $admin)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,superadmin',
            'status' => 'required|in:active,pending,rejected',
        ]);

        $admin->name = $data['name'];
        $admin->email = $data['email'];
        $admin->role = $data['role'];
        $admin->status = $data['status'];
        if (!empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }
        if (!empty($data['role'])) {
            $admin->role = $data['role'];
        }
        if (!empty($data['status'])) {
            $admin->status = $data['status'];
        }
        $admin->save();

        $label = $admin->role === 'superadmin' ? 'SuperAdmin' : 'Admin';
        return redirect()->route('admin.index')->with('success', $label . ' berhasil diperbarui');
    }

    public function destroy(User $admin)
    {
        if ($admin->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri');
        }

        $label = $admin->role === 'superadmin' ? 'SuperAdmin' : 'Admin';
        $admin->delete();
        return redirect()->route('admin.index')->with('success', $label . ' berhasil dihapus');
    }
}
