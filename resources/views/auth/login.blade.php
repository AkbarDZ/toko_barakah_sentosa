<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Toko Barakah Sentosa</title>
  
  <link rel="stylesheet" href="{{ asset('matrix/dist/css/style.min.css') }}">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #0f172a;
      /* Kombinasi background: Gradient Radial + Pola Titik-Titik Halus (Dotted Matrix) */
      background-image: 
        radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.18) 0px, transparent 45%),
        radial-gradient(at 100% 100%, rgba(20, 184, 166, 0.18) 0px, transparent 45%),
        radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
      background-size: 100% 100%, 100% 100%, 24px 24px; /* Ukuran grid titik-titik 24px */
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 20px;
      overflow: hidden;
      position: relative;
    }

    /* Hiasan Bulatan Abstrak di Belakang Kartu (Floating Orbs) */
    body::before, body::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      z-index: 0;
      opacity: 0.5;
    }
    body::before {
      width: 300px;
      height: 300px;
      background: #4f46e5;
      top: 20%;
      left: 25%;
      animation: floatAnimation 8s ease-in-out infinite alternate;
    }
    body::after {
      width: 250px;
      height: 250px;
      background: #0d9488;
      bottom: 20%;
      right: 25%;
      animation: floatAnimation 8s ease-in-out infinite alternate-reverse;
    }

    @keyframes floatAnimation {
      0% { transform: translateY(0px) scale(1); }
      100% { transform: translateY(20px) scale(1.1); }
    }

    .login-wrapper {
      width: 100%;
      max-width: 440px;
      perspective: 1000px;
      z-index: 1; /* Di atas hiasan bulatan */
    }

    .glass-card {
      background: rgba(30, 41, 59, 0.7);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 28px;
      padding: 45px 35px;
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
      position: relative;
      overflow: hidden;
    }

    /* Efek pendaran partikel cahaya lembut di bagian bawah kartu */
    .glass-card::after {
      content: '';
      position: absolute;
      bottom: -100px;
      left: 50%;
      transform: translateX(-50%);
      width: 250px;
      height: 150px;
      background: radial-gradient(circle, rgba(20, 184, 166, 0.15) 0%, transparent 70%);
      pointer-events: none;
      z-index: 0;
    }

    .glass-card > * {
      position: relative;
      z-index: 1;
    }

    /* Desain Logo Warung Modern */
    .warung-icon-container {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
      color: #0f172a;
      border-radius: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.2rem;
      margin: 0 auto 24px auto;
      box-shadow: 0 10px 25px rgba(20, 184, 166, 0.3);
      border: 2px solid rgba(255, 255, 255, 0.1);
    }

    .welcome-text {
      text-align: center;
      margin-bottom: 35px;
    }

    .welcome-text h4 {
      font-weight: 700;
      color: #ffffff;
      font-size: 1.8rem;
      letter-spacing: -0.5px;
      margin-bottom: 8px;
    }

    .welcome-text p {
      color: #94a3b8;
      font-size: 0.95rem;
      line-height: 1.4;
    }

    .form-label {
      font-weight: 600;
      color: #cbd5e1;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
      display: block;
    }

    .input-group-custom {
      position: relative;
    }

    .form-control-custom {
      width: 100%;
      background: rgba(15, 23, 42, 0.6) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      border-radius: 14px !important;
      padding: 14px 16px 14px 48px !important;
      color: #f8fafc !important;
      font-size: 0.95rem;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-control-custom::placeholder {
      color: #475569;
    }

    .form-control-custom:focus {
      background: rgba(15, 23, 42, 0.8) !important;
      border-color: #14b8a6 !important;
      box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.15) !important;
    }

    .input-icon {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
      font-size: 1.2rem;
      transition: color 0.25s ease;
    }

    .form-control-custom:focus + .input-icon {
      color: #14b8a6;
    }

    /* Tombol Masuk */
    .btn-masuk {
      background: #14b8a6;
      border: none;
      color: #0f172a;
      padding: 14px;
      border-radius: 14px;
      font-weight: 700;
      font-size: 1rem;
      letter-spacing: 0.5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.25s ease;
      width: 100%;
      margin-top: 10px;
    }

    .btn-masuk:hover {
      background: #0d9488;
      transform: translateY(-1px);
      box-shadow: 0 8px 20px rgba(20, 184, 166, 0.3);
    }

    .btn-masuk:active {
      transform: translateY(0);
    }

    /* Custom Alert Danger */
    .alert-custom {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.2);
      border-radius: 14px;
      color: #fca5a5;
      padding: 14px;
      font-size: 0.85rem;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
  </style>
</head>
<body>

  <div class="login-wrapper">
    <div class="glass-card">
      
      <div class="warung-icon-container">
        <i class="bi bi-shop-window"></i>
      </div>

      <div class="welcome-text">
        <h4>Selamat Datang!</h4>
        <p>Silakan masuk ke akun Toko Barakah Sentosa Anda</p>
      </div>

      @if($errors->any())
        <div class="alert-custom">
          <i class="bi bi-shield-exclamation-fill" style="color: #ef4444; font-size: 1.1rem;"></i>
          <div>{{ $errors->first() }}</div>
        </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}">
        @csrf
        
        <div class="mb-3">
          <label class="form-label">ALAMAT EMAIL</label>
          <div class="input-group-custom">
            <input type="email" name="email" class="form-control form-control-custom" placeholder="kasir@barakah.com" value="{{ old('email') }}" required autocomplete="off">
            <i class="bi bi-envelope input-icon"></i>
          </div>
        </div>
        
        <div class="mb-4">
          <label class="form-label">KATA SANDI</label>
          <div class="input-group-custom">
            <input type="password" name="password" class="form-control form-control-custom" placeholder="••••••••" required>
            <i class="bi bi-lock input-icon"></i>
          </div>
        </div>

        <button type="submit" class="btn-masuk">
          Masuk Sekarang <i class="bi bi-arrow-right-short" style="font-size: 1.3rem;"></i>
        </button>
      </form>
      
    </div>
  </div>

</body>
</html>