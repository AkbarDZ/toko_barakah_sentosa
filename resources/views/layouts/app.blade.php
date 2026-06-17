<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>TOKO BARAKAH SENTOSA</title>

    {{-- 🔴 DI BAGIAN INI: Pengaturan Icon Tab Browser (Favicon) --}}
    {{-- Jika Anda punya file logo baru (misal: logo.png atau favicon.png) di folder public/, ganti nama file di bawah ini --}}
    <link rel="icon" type="image/png" href="{{ asset('images/Salinan iconlogowarung.png') }}">

    <!-- CSS FRAMEWORK & PLUGINS -->
    <link rel="stylesheet" href="{{ asset('mystuffs/universal.css') }}">
    <link rel="stylesheet" href="{{ asset('matrix/dist/css/style.min.css') }}">
    <link rel="stylesheet" href="{{ asset('matrix/assets/extra-libs/DataTables/datatables.min.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <style>
        /* ================= ATUR STRUKTUR UTAMA (ANTI TIMPA & RAPAT) ================= */
        body {
            background-color: #f4f7f6;
            overflow-x: hidden;
        }

        /* Topbar Tinggi Diperkecil Sedikit Agar Elegan */
        .topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            height: 56px;
            z-index: 1030;
            background: #ffffff !important;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05) !important;
        }

        /* ================= CUSTOM SIDEBAR MODERN ================= */
        .custom-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 240px;
            background-color: #ffffff;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.03);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        /* Header di dalam sidebar disamakan tingginya dengan topbar */
        .sidebar-header {
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .sidebar-menu-wrapper {
            overflow-y: auto;
            padding: 10px 8px;
            flex-grow: 1;
        }

        .menu-heading {
            font-size: 0.7rem;
            font-weight: 700;
            color: #adb5bd;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 12px 12px 4px 12px;
        }

        .nav-menu-item {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            color: #495057 !important;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 2px;
            font-weight: 500;
            font-size: 0.88rem;
            transition: all 0.2s ease;
        }

        .nav-menu-item i {
            font-size: 1.2rem;
            margin-right: 10px;
            color: #6c757d;
        }

        .nav-menu-item:hover, .nav-menu-item.active {
            background-color: #e8f0fe;
            color: #1a73e8 !important;
            font-weight: 600;
        }

        .nav-menu-item:hover i, .nav-menu-item.active i {
            color: #1a73e8;
        }

        /* Overlay Hitam Transparan HP */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1040;
            display: none;
            backdrop-filter: blur(2px);
        }

        /* ================= PENGATURAN GEOMETRI LAYAR (PAS & PRESISI) ================= */

        /* 1. Tampilan Mobile / Potret (Layar < 1200px) */
        @media (max-width: 1199.98px) {
            .custom-sidebar {
                transform: translateX(-100%);
                z-index: 1050;
            }
            .custom-sidebar.show {
                transform: translateX(0);
            }
            .custom-sidebar.show ~ .sidebar-overlay {
                display: block;
            }

            .page-wrapper {
                margin-left: 0 !important;
                padding: 72px 12px 20px 12px !important;
            }
        }

        /* 2. Tampilan Desktop */
        @media (min-width: 1200px) {
            .custom-sidebar {
                transform: translateX(0) !important;
                z-index: 1025;
                top: 0;
            }

            .page-wrapper {
                margin-left: 240px !important;
                padding: 76px 20px 20px 20px !important;
            }

            .btn-toggle-menu {
                display: none !important;
            }
        }

        /* Kontainer Diperluas Maksimal */
        .content-container-max {
            max-width: 100%;
            width: 100%;
            margin: 0 auto;
        }
    </style>
</head>

<body>

    @php $hasAuth = session('user_id') ? true : false; @endphp

    <div id="main-wrapper">

        <!-- ================= NAVBAR ATAS ================= -->
        @if($hasAuth)
        <header class="topbar">
            <nav class="navbar h-100 navbar-expand px-3 py-0">
                <div class="d-flex align-items-center justify-content-between w-100">

                    <!-- Kiri: Tombol Menu & Nama Toko -->
                    <div class="d-flex align-items-center">
                        <button class="btn btn-light btn-toggle-menu rounded-circle p-2 me-2 d-flex align-items-center justify-content-center"
                                type="button" id="toggleSidebarBtn" style="width: 36px; height: 36px;">
                            <i class="mdi mdi-menu" style="font-size: 1.3rem; color: #495057;"></i>
                        </button>

                        <a class="navbar-brand m-0 p-0 d-flex align-items-center" href="{{ route('dashboard') }}">
                            <img src="{{ asset('images/Salinan iconlogowarung.png') }}" alt="Logo" style="height: 32px; width: auto;" class="me-2 mr-1">
                            <span class="fw-bold text-dark" style="font-size: 1.1rem;">
                                TOKO BARAKAH SENTOSA
                            </span>
                        </a>
                    </div>

                    <!-- Kanan: User Admin -->
                    <div class="d-flex align-items-center text-secondary small fw-semibold bg-light px-3 py-1.5 rounded-pill">
                        <i class="mdi mdi-account-circle text-primary me-1 mr-1" style="font-size: 1.1rem;"></i>
                        <div style="line-height:1; text-align:left;">
                            <div style="font-weight:600;">{{ session('user_name') ?? 'User' }}</div>
                            <small class="text-muted">{{ ucfirst(session('user_role') ?? '') }}</small>
                        </div>
                    </div>

                </div>
            </nav>
        </header>

        <!-- ================= SIDEBAR UTAMA ================= -->
        <aside class="custom-sidebar" id="sidebarContainer">
            <div class="sidebar-header">
                <span class="fw-bold text-primary" style="font-size: 0.85rem; letter-spacing: 0.5px;">MENU UTAMA</span>
                <button class="btn btn-sm btn-light rounded-circle d-xl-none" type="button" id="closeSidebarBtn">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>

            <div class="sidebar-menu-wrapper">
                <nav class="d-flex flex-column">
                    <div class="menu-heading">Utama</div>
                    <a class="nav-menu-item {{ Request::is('/') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="mdi mdi-view-dashboard"></i>
                        <span>Dashboard</span>
                    </a>

                    @if(session('user_role') === 'admin')
                    <div class="menu-heading">Manajemen Barang</div>
                    <a class="nav-menu-item {{ Request::routeIs('produk.*') ? 'active' : '' }}" href="{{ route('produk.index') }}">
                        <i class="mdi mdi-cube"></i>
                        <span>Produk</span>
                    </a>
                    <a class="nav-menu-item {{ Request::routeIs('kategori.*') ? 'active' : '' }}" href="{{ route('kategori.index') }}">
                        <i class="mdi mdi-format-list-bulleted"></i>
                        <span>Kategori</span>
                    </a>
                    <a class="nav-menu-item {{ Request::routeIs('satuan-produk.*') ? 'active' : '' }}" href="{{ route('satuan-produk.index') }}">
                        <i class="mdi mdi-playlist-check"></i>
                        <span>Satuan</span>
                    </a>
                    @endif

                    <div class="menu-heading">Kasir & Gudang</div>
                    <a class="nav-menu-item {{ Request::routeIs('transaksi.*') ? 'active' : '' }}" href="{{ route('transaksi.index') }}">
                        <i class="mdi mdi-cart"></i>
                        <span>Transaksi</span>
                    </a>
                    <a class="nav-menu-item {{ Request::routeIs('stok.*') ? 'active' : '' }}" href="{{ route('stok.index') }}">
                        <i class="mdi mdi-database"></i>
                        <span>Stok</span>
                    </a>
                    @if(session('user_role') === 'admin')
                    <div class="menu-heading">Manajemen Pengguna</div>
                    <a class="nav-menu-item {{ Request::routeIs('pengguna.*') ? 'active' : '' }}" href="{{ route('pengguna.index') }}">
                        <i class="mdi mdi-account"></i>
                        <span>Pengguna</span>
                    </a>
                    @endif
                 </nav>
            </div>
            <div class="px-3 py-3" style="border-top:1px solid #f0f0f0;">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-start" style="gap:10px;">
                        <i class="mdi mdi-logout"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Overlay gelap pelindung konten luar -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        @endif

        <!-- ================= AREA HALAMAN UTAMA ================= -->
        <div class="page-wrapper" @if(!$hasAuth) style="margin-left:0; padding:72px 12px 20px 12px;" @endif>
            <div class="container-fluid content-container-max px-0">

                <!-- ALERT NOTIFIKASI -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                        {{ session('success') }}
                        {{-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">Close</button> --}}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        {{ session('error') }}
                        {{-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> --}}
                    </div>
                @endif

                <!-- OUTPUT BLADE CONTENT -->
                @yield('content')

            </div>

            <!-- FOOTER -->
            <div class="text-center mt-4 pb-2">
                <p class="text-muted small">© 2026 <strong>Toko Barakah Sentosa</strong> • Versi 1.0.4</p>
            </div>
        </div>

    </div>

    <!-- JS SCRIPTS -->
    <script src="{{ asset('matrix/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('matrix/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('matrix/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
    <script src="{{ asset('mystuffs/universal.js') }}"></script>

    <!-- LOGIKA JAVASCRIPT SLIDE MENU HP -->
    <script>
        $(document).ready(function() {
            function openSidebar() {
                $('#sidebarContainer').addClass('show');
                $('#sidebarOverlay').fadeIn(200);
            }

            function closeSidebar() {
                $('#sidebarContainer').removeClass('show');
                $('#sidebarOverlay').fadeOut(200);
            }

            $('#toggleSidebarBtn').on('click', function(e) {
                e.stopPropagation();
                if ($('#sidebarContainer').hasClass('show')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            $('#closeSidebarBtn, #sidebarOverlay, .nav-menu-item').on('click', function() {
                closeSidebar();
            });
        });
    </script>

</body>

</html>
