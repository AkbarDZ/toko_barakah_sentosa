@extends('layouts.app')

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold text-dark">Daftar Kategori</h3>
                @if(session('user_role') === 'admin')
                    <a href="{{ route('kategori.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus"></i> + Tambah Kategori
                    </a>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-bordered custom-datatable" data-nosort="3">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($kategori as $k)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $k->nama_kategori }}</td>
                                <td>{{ $k->deskripsi ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        @if(session('user_role') === 'admin')
                                            <a href="{{ route('kategori.edit', $k->id_kategori) }}"
                                                class="btn btn-sm btn-warning text-white mr-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>

                                            <form action="{{ route('kategori.destroy', $k->id_kategori) }}" method="POST"
                                                onsubmit="return confirm('Yakin hapus data?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <p class="mb-0">Data kategori belum tersedia.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

@endsection