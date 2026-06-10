@extends('layouts.app')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">
        <div id="filter-template-produk" style="display: none;">
            <label style="margin-right: 15px;">
                Kategori:
                <select id="dropdown-kategori" class="form-select form-select-sm" style="margin-left: 5px;">
                    <option value="">Semua Kategori</option>
                    @foreach($kategori as $k)
                        <option value="{{ $k->nama_kategori }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div id="filter-date-produk" style="display: none;">
            <label style="margin-right: 20px;">
                Tanggal:
                <input type="date" id="start-date" class="form-control form-control-sm" style="display:inline-block; width:auto; margin-left:5px;">
            </label>
            <label >
                Sampai:
                <input type="date" id="end-date" class="form-control form-control-sm" style="display:inline-block; width:auto; margin-left:5px;">
            </label>
        </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 fw-bold text-dark">Daftar Produk</h3>
            @if(session('user_role') === 'admin')
            <a href="{{ route('produk.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus"></i> + Tambah Produk
            </a>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle custom-datatable"
            data-nosort="3,6"
            data-filter-template="#filter-template-produk"
            data-filter-id="#dropdown-kategori"
            data-filter-col="4"
            data-date-template="#filter-date-produk"
            data-date-start="#start-date"
            data-date-end="#end-date"
            data-date-col="5">
            {{--
            data-nosort="3,5" -> Kolom ke-3 (Gambar) dan ke-5 (Aksi) supaya ignore sorting
            data-filter-template="#filter-template-produk" -> Template filter yang akan digunakan
            data-filter-id="#dropdown-kategori" -> ID elemen filter yang akan memicu filter, samain id sama yg  di div filternya
            data-filter-col="4" -> Kolom ke-4 (Kategori) kolom yg bakal jadi referensi filternya
            --}}
                <thead class="table-light">
                    <tr>
                        <th style="width:50px;" class="text-center">No</th>
                        <th>Kode</th>
                        <th>Produk</th>
                        <th>Gambar</th>
                        <th>Kategori</th>
                        <th>Terakhir Diubah</th>
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
                                    <!-- asset('storage/...') akan otomatis mengarah ke folder public/storage/ -->
                                    <img src="{{ asset('storage/' . $d->direktori_gambar) }}"
                                        alt="{{ $d->nama_produk }}" width="100"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <span style="display:none;" class="text-danger small">image can't be found</span>
                                @else
                                    <!-- Tampilkan gambar placeholder jika kosong -->
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
                                <!-- Y-m-d disembunyikan untuk dibaca DataTables -->
                                <span style="display:none;">{{ $d->created_at->format('Y-m-d') }}</span>
                                <!-- Format cantik untuk dibaca user -->
                                {{ $d->created_at->translatedFormat('d M Y') }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    @if(session('user_role') === 'admin')
                                    <a href="{{ route('produk.edit', $d->getKey()) }}" class="btn btn-sm btn-warning text-white mr-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    <form action="{{ route('produk.destroy', $d->getKey()) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                                <p class="mb-0">Data produk belum tersedia.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
