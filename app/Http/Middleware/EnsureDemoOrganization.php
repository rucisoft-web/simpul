<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun Tamu wajib memilih satu perusahaan demo; tanpa pilihan, kembali ke halaman depan.
 */
class EnsureDemoOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isGuest() && ! session('org_id')) {
            return redirect()->to(route('landing').'#demo')->with('status', 'Pilih perusahaan demo terlebih dahulu.');
        }

        return $next($request);
    }
}
