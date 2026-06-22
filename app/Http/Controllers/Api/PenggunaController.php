<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function __construct()
    {
        // Double check admin role in addition to sanctum
        $this->middleware(function ($request, $next) {
            if ($request->user() && $request->user()->role !== 'admin') {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $pengguna = User::orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $pengguna
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['admin', 'kasir'])],
        ]);

        $pengguna = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil ditambahkan.',
            'data' => $pengguna
        ], 201);
    }

    public function show($id)
    {
        $pengguna = User::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $pengguna
        ]);
    }

    public function update(Request $request, $id)
    {
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

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diperbarui.',
            'data' => $pengguna
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $pengguna = User::findOrFail($id);

        if ($pengguna->id == $request->user()->id) {
             return response()->json([
                 'success' => false,
                 'message' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan.'
             ], 400);
        }

        $pengguna->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus.'
        ]);
    }
}
