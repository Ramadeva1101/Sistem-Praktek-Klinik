<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectNonAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role !== 'admin') {
            if (auth()->user()->role === 'dokter') {
                return redirect()->route('filament.praktek-klinik.resources.kunjungans.index');
            } elseif (auth()->user()->role === 'kasir') {
                return redirect()->route('filament.praktek-klinik.resources.kasirs.index');
            }
        }

        return $next($request);
    }
}
