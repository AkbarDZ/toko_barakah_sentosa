@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-3 py-md-4"
    style="background-color: #f4f7f6; min-height: 100vh; font-family: 'Inter', sans-serif;">

    {{-- <div class="card border-0 mb-4 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); overflow: hidden;">
        <div class="card-body px-4 py-5 text-white d-flex align-items-center justify-content-between flex-column flex-md-row gap-3">
            <div>
                <h3 class="fw-bold mb-2">Selamat Datang Kembali, {{ auth()->user()->name ?? 'Pengguna' }}!</h3>
                <p class="mb-0 opacity-90">Role Anda: {{ auth()->user()->role ?? 'Pengguna' }}</p>
            </div>
            <div class="text-end d-none d-md-block">
                <i class="bi bi-box-seam fs-1 opacity-50"></i>
            </div>
        </div>
    </div> --}}

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 px-2">
        <div>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                <i class="bi bi-list me-3 fs-4 d-md-none"></i>
            </h5>
        </div>

        <div style="min-width: 300px;" class="w-100 w-md-auto">
            {{-- AKTIF: Form sekarang mengarah ke URL dashboard aktif saat ini --}}
            <form action="{{ url()->current() }}" method="GET">
                <div class="input-group shadow-sm bg-white rounded-pill border px-3 py-1" style="border-color: #2d3436 !important;">
                    <span class="input-group-text bg-white border-0 p-0 me-2">
                        <button type="submit" class="btn p-0 border-0 bg-transparent">
                            <i class="bi bi-search text-dark fs-5 fw-bold"></i>
                        </button>
                    </span>
                    {{-- AKTIF: Menambahkan value agar teks pencarian tidak hilang setelah dienter --}}
                    <input type="text" name="search" class="form-control border-0 p-0 bg-white"
                           placeholder="Cari kode atau nama produk..."
                           value="{{ request('search') }}"
                           style="box-shadow: none; font-style: italic; font-size: 1.05rem;">

                    {{-- TOMBOL RESET: Muncul hanya jika user sedang dalam posisi mencari sesuatu --}}
                    @if(request('search'))
                        <a href="{{ url()->current() }}" class="btn p-0 border-0 bg-transparent ms-2 d-flex align-items-center">
                            <i class="bi bi-x-circle-fill text-secondary fs-5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 mb-4 shadow-sm" style="border-radius: 16px; background-color: #ffffff;">
        <div class="card-body px-4 py-3">
            <h4 class="fw-bold mb-0" style="color: #2d3436; letter-spacing: -0.5px; font-size: calc(1.2rem + 0.3vw);">Dashboard</h4>
            <p class="text-muted small mb-0">Selamat datang di sistem manajemen Warung</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 24px; overflow: hidden; background-color: #ffffff;">
        <div class="card-body p-3 p-sm-4 p-lg-5">
            <div class="row gy-4 gx-xl-5">

                <div class="col-xl-7">
                    <div class="h-100 p-3 p-sm-4 border rounded-4 bg-white shadow-sm d-flex flex-column"
                        style="border: 1px solid #e9ecef !important;">

                        <div class="mb-4">
                            <h6 class="fw-bold mb-0 text-dark text-uppercase tracking-wider">KATALOG PRODUK</h6>
                        </div>

                        <div class="row g-2 g-sm-3 flex-grow-1 align-content-start">
                            @forelse($produk as $p)
                                <div class="col-6 col-sm-4 col-md-3 mb-2">
                                    <div class="card h-100 border-0 shadow-sm product-card"
                                        style="border-radius: 15px; transition: all 0.3s ease;">

                                        <div class="p-2 p-sm-3">
                                            <div class="rounded-3 d-flex align-items-center justify-content-center picture-box"
                                                style="height: 120px; background-color: #f8f9fa; overflow: hidden;">
                                                @if($p->direktori_gambar && file_exists(public_path('storage/' . $p->direktori_gambar)))
                                                    <img src="{{ asset('storage/' . $p->direktori_gambar) }}"
                                                         alt="{{ $p->nama_produk }}"
                                                         class="img-fluid w-100 h-100"
                                                         style="object-fit: cover; border-radius: 8px;"
                                                         onerror="this.onerror=null;this.src='{{ asset('images/Salinan iconlogowarung.png') }}';this.className='img-fluid opacity-50';this.style.maxHeight='60px';this.style.objectFit='contain';">
                                                @else
                                                    <img src="{{ asset('images/Salinan iconlogowarung.png') }}"
                                                         alt="No Image"
                                                         class="img-fluid opacity-50"
                                                         style="max-height: 60px; object-fit: contain;">
                                                @endif
                                            </div>
                                        </div>

                                        <div class="card-body pt-0 px-2 px-sm-3 pb-3 text-center d-flex flex-column justify-content-between">
                                            <p class="mb-2 fw-bold text-dark product-title"
                                                style="font-size: 0.75rem; min-height: 36px; line-height: 1.2;">
                                                {{ $p->nama_produk }}
                                                <br>
                                                <small class="text-muted fw-normal" style="font-size: 0.65rem;">({{ $p->kode_produk }})</small>
                                            </p>
                                            <div class="mt-auto">
                                                @forelse($p->satuanProduk as $sp)
                                                    <span class="badge bg-light text-dark border mb-1 w-100 text-truncate text-start d-block px-2"
                                                        style="font-size: 0.65rem;" title="{{ $sp->nama_satuan }}: Rp {{ number_format($sp->harga_jual, 0, ',', '.') }}">
                                                        {{ $sp->nama_satuan }}: Rp {{ number_format($sp->harga_jual, 0, ',', '.') }}
                                                    </span>
                                                @empty
                                                    <span class="badge bg-light text-dark border mb-1 w-100 text-truncate text-start d-block px-2"
                                                        style="font-size: 0.65rem;" title="Tidak ada satuan">
                                                        Harga belum diatur
                                                    </span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                                    Produk yang Anda cari tidak ditemukan.
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 info-pagination-container">
                            <div class="text-secondary small">
                                Menampilkan halaman {{ $produk->currentPage() }} dari {{ $produk->lastPage() }}
                            </div>
                            <div class="produk-datatables-pagination">
                                {{ $produk->links('pagination::bootstrap-5') }}
                            </div>
                        </div>

                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="h-100 p-3 p-sm-4 border rounded-4 bg-white shadow-sm"
                        style="border: 1px solid #e9ecef !important;">
                        <h6 class="fw-bold mb-4 text-uppercase tracking-wider">Daftar Harga Terkini</h6>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr style="font-size: 0.8rem;">
                                        <th class="border-0 px-2 py-3">Nama Barang</th>
                                        <th class="border-0 text-end px-2 py-3">Harga</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size: 0.85rem;">
                                    @forelse($produklatest as $p)
                                    <tr>
                                        <td class="px-2 border-bottom-0 py-2 text-secondary">
                                            {{ $p->nama_produk }} <small class="text-muted">({{ $p->kode_produk }})</small>
                                        </td>
                                        <td class="px-2 border-bottom-0 py-2 text-end fw-bold text-nowrap">
                                            @if($p->satuanProduk->isNotEmpty())
                                                Rp {{ number_format($p->satuanProduk->first()->harga_jual, 0, ',', '.') }}
                                            @else
                                                <span class="text-muted text-xs fw-normal">Belum ada harga</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-3 text-muted">Tidak ada data harga.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="2" class="text-center py-2">
                                            <small class="text-muted">Menampilkan {{ $produklatest->count() }} produk teratas</small>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-4 mt-sm-5 p-3 rounded-4 bg-light border-start border-primary border-4">
                            <h6 class="fw-bold mb-3" style="font-size: 0.85rem;"><i class="bi bi-info-circle-fill me-1 text-primary"></i> Keterangan Satuan:</h6>
                            <div class="row g-3 text-muted" style="font-size: 0.75rem; line-height: 1.6;">
                                <div class="col-12 col-sm-4 border-sm-end">
                                    <strong class="text-dark">S (Sachet)</strong><br>
                                    12S: 12 Pcs<br>
                                    10S: 10 Pcs
                                </div>
                                <div class="col-12 col-sm-4 border-sm-end">
                                    <strong class="text-dark">R (Renteng)</strong><br>
                                    1R: 12 Sachet<br>
                                    2R: 24 Sachet
                                </div>
                                <div class="col-12 col-sm-4">
                                    <strong class="text-dark">Lainnya</strong><br>
                                    1 Bks: 24 Btg<br>
                                    1 B: 1 Roko
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    /* Menyembunyikan teks info bawaan pagination Laravel */
    .produk-datatables-pagination nav div:first-child {
        display: none !important;
    }

    /* Memastikan tombol paginationnya tetap muncul dan rapi */
    .produk-datatables-pagination nav div:last-child {
        display: flex !important;
    }
    .product-card {
        background-color: #ffffff;
        border: 1px solid #f0f2f5;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1) !important;
        border-color: #0d6efd;
    }

    .table-hover tbody tr {
        transition: background 0.2s;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .tracking-wider {
        letter-spacing: 1px;
    }

    .rounded-pill {
        border-radius: 50rem !important;
    }

    .produk-datatables-pagination .pagination {
        margin-bottom: 0;
    }

    .produk-datatables-pagination .page-item .page-link {
        color: #6c757d;
        border: 1px solid #dee2e6;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    .produk-datatables-pagination .page-item:first-child .page-link {
        border-top-left-radius: 4px !important;
        border-bottom-left-radius: 4px !important;
    }

    .produk-datatables-pagination .page-item:last-child .page-link {
        border-top-right-radius: 4px !important;
        border-bottom-right-radius: 4px !important;
    }

    .produk-datatables-pagination .page-item.active .page-link {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: #fff !important;
    }

    .produk-datatables-pagination .page-item:not(.active) .page-link:hover {
        background-color: #e9ecef;
        color: #495057;
        border-color: #dee2e6;
    }

    .produk-datatables-pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
        opacity: 0.6;
    }

    @media (max-width: 575.98px) {
        .border-sm-end {
            border-bottom: 1px dashed #dee2e6;
            padding-bottom: 10px;
        }
    }

    @media (min-width: 576px) {
        .border-sm-end {
            border-right: 2px solid #dee2e6;
        }
        .w-md-auto {
            width: auto !important;
        }
    }
</style>
@endsection
