@extends('layouts.app')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 fw-bold text-dark">Arsip Produk</h3>
            <a href="{{ route('produk.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle custom-datatable"
            data-nosort="3,6">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;" class="text-center">No</th>
                        <th>Kode</th>
                        <th>Produk</th>
                        <th>Gambar</th>
                        <th>Kategori</th>
                        <th>Dihapus Pada</th>
                        <th style="width:170px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produk as $d)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $d->kode_produk }}
                                </span>
                            </td>
                            <td class="fw-bold text-capitalize">{{ $d->nama_produk }}</td>
                            <td>
                                @if($d->direktori_gambar)
                                    <img src="{{ asset('storage/' . $d->direktori_gambar) }}"
                                        alt="{{ $d->nama_produk }}" width="100"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <span style="display:none;" class="text-danger small">image can't be found</span>
                                @else
                                    <img src="{{ asset('images/no-image.png') }}" alt="No Image"
                                        width="100">
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $d->kategori->nama_kategori ?? 'Tanpa Kategori' }}
                                </span>
                            </td>
                            <td>
                                <span style="display:none;">{{ $d->deleted_at->format('Y-m-d') }}</span>
                                {{ $d->deleted_at->translatedFormat('d M Y') }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    @if(session('user_role') === 'admin')
                                    <form action="{{ route('produk.restore', $d->getKey()) }}" method="POST" onsubmit="return confirm('Yakin ingin memulihkan data ini?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-trash-restore"></i> Pulihkan
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <p class="mb-0">Data arsip produk belum tersedia.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
