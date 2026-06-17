@extends('layouts.app')

@section('content')

<div class="card shadow-sm mb-4">
    <div class="card-body">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 text-secondary"><i class="fas fa-archive"></i> Arsip Pergerakan Stok</h4>
            <a href="{{ route('stok.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered custom-datatable" 
            data-nosort="4,5">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Dokumen</th>
                        <th>Dihapus Pada</th>
                        <th>Tipe</th>
                        <th>Catatan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pergerakan as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $item->kode_pergerakan }}</strong></td>
                        <td>
                            <span style="display:none;">{{ \Carbon\Carbon::parse($item->deleted_at)->format('Y-m-d') }}</span>
                            {{ \Carbon\Carbon::parse($item->deleted_at)->format('d/m/Y') }}
                        </td>
                        <td>
                            @if(strtolower($item->tipe_pergerakan) == 'masuk')
                                <span class="badge bg-success text-white">Masuk</span>
                            @elseif(strtolower($item->tipe_pergerakan) == 'keluar')
                                <span class="badge bg-danger text-white">Keluar</span>
                            @else
                                <span class="badge bg-warning text-dark">Penyesuaian</span>
                            @endif
                        </td>
                        <td>{{ $item->catatan ?? '-' }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                @if(session('user_role') === 'admin')
                                    <form action="{{ route('stok.restore', $item->id_pergerakan) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin memulihkan data ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-trash-restore"></i> Pulihkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
