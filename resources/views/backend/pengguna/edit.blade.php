@extends('layouts.app')

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold text-dark">Edit Pengguna</h3>
                <a href="{{ route('pengguna.index') }}" class="btn btn-secondary shadow-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pengguna.update', $pengguna->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $pengguna->name) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $pengguna->email) }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-bold">Password Baru (Opsional)</label>
                        <input type="password" name="password" id="password" class="form-control" minlength="6">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label fw-bold">Role</label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="kasir" {{ old('role', $pengguna->role) == 'kasir' ? 'selected' : '' }}>Kasir</option>
                            <option value="admin" {{ old('role', $pengguna->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fas fa-save"></i> Perbarui Pengguna
                    </button>
                </div>
            </form>

        </div>
    </div>

@endsection
