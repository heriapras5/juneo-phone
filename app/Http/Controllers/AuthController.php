<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->isActive()) {
            return back()->withErrors([
                'email' => 'Akun Anda belum aktif. Silakan hubungi SuperAdmin.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $name = explode(' ', $user->name)[0];

            return redirect()->intended('/dashboard')
                ->with('success', "Selamat datang kembali, $name!");
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('success', 'Anda telah berhasil logout.');
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Gagal login dengan Google. Silakan coba lagi.');
        }

        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Akun Google tidak terdaftar. Registrasi hanya dapat dilakukan oleh SuperAdmin.');
        }

        $user->update([
            'provider' => 'google',
            'provider_id' => $googleUser->id,
            'avatar' => $googleUser->avatar,
        ]);

        if (!$user->isActive()) {
            return redirect()->route('login')
                ->with('error', 'Akun Anda belum aktif. Silakan hubungi SuperAdmin.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        $name = explode(' ', $user->name)[0];
        return redirect()->intended('/dashboard')
            ->with('success', "Selamat datang, $name!");
    }
}
