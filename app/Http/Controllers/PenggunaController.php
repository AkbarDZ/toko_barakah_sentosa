<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Hanya admin yang bisa melihat daftar pengguna? Atau semua?
        // Untuk aman, admin saja, tapi akan ditangani oleh middleware di route atau kita bisa check session role
        if (session('user_role') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $pengguna = User::orderBy('id', 'desc')->get();
        return view('backend.pengguna.index', compact('pengguna'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (session('user_role') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return view('backend.pengguna.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (session('user_role') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['admin', 'kasir'])],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not used
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (session('user_role') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $pengguna = User::findOrFail($id);
        return view('backend.pengguna.edit', compact('pengguna'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (session('user_role') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $pengguna = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($pengguna->id)],
            'role' => ['required', Rule::in(['admin', 'kasir'])],
            'password' => 'nullable|string|min:6',
        ]);

        $pengguna->name = $request->name;
        $pengguna->email = $request->email;
        $pengguna->role = $request->role;

        if ($request->filled('password')) {
            $pengguna->password = Hash::make($request->password);
        }

        $pengguna->save();

        return redirect()->route('pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (session('user_role') !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $pengguna = User::findOrFail($id);

        if ($pengguna->id == session('user_id')) {
             return redirect()->route('pengguna.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan.');
        }

        $pengguna->delete();

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
