<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Tombol "Masuk sebagai Tamu": masuk tanpa password ke SATU perusahaan demo pilihan pengunjung
     * (?org=BBRD|MEDC|UINSK). Data perusahaan lain tidak ikut tampil.
     */
    public function guest(Request $request)
    {
        $org = Organization::whereNull('parent_id')->where('code', (string) $request->input('org', ''))->first();
        if (! $org) {
            return redirect()->to(route('landing').'#demo')->with('status', 'Pilih perusahaan demo terlebih dahulu.');
        }

        if ($request->user() && ! $request->user()->isGuest()) {
            Auth::logout();
        }
        $guest = User::firstOrCreate(['email' => 'tamu@simpul.test'], ['name' => 'Tamu Demo', 'password' => bin2hex(random_bytes(16)), 'role' => 'guest']);
        Auth::login($guest);
        $request->session()->regenerate();
        session(['org_id' => $org->id]);

        // Tautan demo langsung ke layar tertentu, mis. /coba-sebagai-tamu?next=/topology (hanya path internal).
        $next = (string) $request->query('next', '');
        if (str_starts_with($next, '/') && ! str_starts_with($next, '//')) {
            return redirect($next);
        }

        return redirect()->route('dashboard')->with('status', "Selamat datang! Anda masuk sebagai tamu dengan data demo {$org->name}.");
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
