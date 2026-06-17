<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    /**
     * Show the form for editing the current user's profile.
     */
    public function edit()
    {
        // Get current user ID from session
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Silahkan login terlebih dahulu.');
        }

        $pengguna = User::findOrFail($userId);
        
        return view('backend.profil.edit', compact('pengguna'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Silahkan login terlebih dahulu.');
        }

        $pengguna = User::findOrFail($userId);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($pengguna->id)],
            'password' => 'nullable|string|min:6',
        ]);

        $pengguna->name = $request->name;
        $pengguna->email = $request->email;

        // Note: we don't allow changing role here. The role remains unchanged.

        if ($request->filled('password')) {
            $pengguna->password = Hash::make($request->password);
        }

        $pengguna->save();

        // Update session name if changed
        if (session('user_name') !== $pengguna->name) {
            $request->session()->put('user_name', $pengguna->name);
        }

        return redirect()->route('profil.edit')->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
