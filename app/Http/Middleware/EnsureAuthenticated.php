<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->has('user_id')) {
            return redirect()->route('login');
        }

        $userId = $request->session()->get('user_id');
        $cachedSessionId = \Illuminate\Support\Facades\Cache::get("user_session_{$userId}");

        if ($cachedSessionId && $cachedSessionId !== $request->session()->getId()) {
            // Log out the current session as it has been overridden by a newer login
            $request->session()->forget(['user_id', 'user_role', 'user_name']);
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda telah masuk di perangkat lain.']);
        }

        return $next($request);
    }
}
