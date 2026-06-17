@extends('layouts.app')

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold text-dark">Daftar Pengguna</h3>
                @if(session('user_role') === 'admin')
                    <a href="{{ route('pengguna.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus"></i> Tambah Pengguna
                    </a>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-bordered custom-datatable" data-nosort="4">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengguna as $p)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $p->name }}</td>
                                <td>{{ $p->email }}</td>
                                <td>
                                    @if($p->role === 'admin')
                                        <span class="badge bg-success text-white">Admin</span>
                                    @else
                                        <span class="badge bg-primary text-white">Kasir</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        @if(session('user_role') === 'admin')
                                            <a href="{{ route('pengguna.edit', $p->id) }}" class="btn btn-sm btn-warning text-white mr-1" style="margin-right: 4px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('pengguna.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data pengguna ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" {{ $p->id == session('user_id') ? 'disabled' : '' }}>
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <p class="mb-0">Data pengguna belum tersedia.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

@endsection
