<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Ditukar menjadi index
    public function index()
    {
        if (session()->has('user_id')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    // Backwards-compatible alias for routes using showLogin
    public function showLogin()
    {
        return $this->index();
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['email' => 'Email atau password salah'])->withInput();
        }

        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_role', $user->role ?? 'kasir');
        $request->session()->put('user_name', $user->name ?? '');

        // Revoke all API tokens to prevent concurrent mobile sessions
        $user->tokens()->delete();

        // Store the active session ID in cache to prevent concurrent web logins
        \Illuminate\Support\Facades\Cache::put("user_session_{$user->id}", $request->session()->getId(), now()->addHours(8));

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('user_id');
        $request->session()->forget('user_role');
        return redirect()->route('login');
    }
}