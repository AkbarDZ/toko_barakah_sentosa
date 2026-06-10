<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:admin') or ->middleware('role:admin,kasir')
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        if (! $request->session()->has('user_id')) {
            return redirect()->route('login');
        }

        $user = User::find($request->session()->get('user_id'));
        if (! $user) {
            $request->session()->forget('user_id');
            return redirect()->route('login');
        }

        $allowed = array_map('strtolower', array_map('trim', explode(',', $roles)));
        $userRole = strtolower($user->role ?? 'kasir');

        if (! in_array($userRole, $allowed)) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        return $next($request);
    }
}
