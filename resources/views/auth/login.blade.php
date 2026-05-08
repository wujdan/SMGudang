<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Login — GudangKu</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800;900&family=Barlow+Condensed:wght@700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* ── BASE ── */
        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Barlow', sans-serif;
            min-height: 100vh;
            min-height: 100dvh;
            /* dynamic viewport height — lebih akurat di mobile */
            background: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        /* ── DESKTOP: DUA PANEL ── */
        .login-container {
            display: flex;
            width: 100%;
            max-width: 960px;
            max-height: 96vh;
            /* ★ tidak boleh lebih tinggi dari viewport */
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
        }

        /* Panel Kiri — hanya desktop */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 28px;
            position: relative;
            overflow-y: auto;
            /* ★ scroll internal jika konten terlalu panjang */
        }

        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                repeating-linear-gradient(0deg, transparent, transparent 40px, rgba(255, 255, 255, .02) 40px, rgba(255, 255, 255, .02) 41px),
                repeating-linear-gradient(90deg, transparent, transparent 40px, rgba(255, 255, 255, .02) 40px, rgba(255, 255, 255, .02) 41px);
        }

        .left-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 320px;
            color: #fff;
        }

        .left-icon {
            width: 80px;
            height: 80px;
            background: #f59e0b;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: #000;
            margin-bottom: 28px;
            box-shadow: 0 0 60px rgba(245, 158, 11, .3);
        }

        .left-content h1 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 48px;
            font-weight: 900;
            letter-spacing: 2px;
            line-height: 1;
            margin-bottom: 10px;
        }

        .left-content h1 span {
            color: #f59e0b;
        }

        .left-content p {
            font-size: 14px;
            color: rgba(255, 255, 255, .45);
            line-height: 1.6;
        }

        .feature-list {
            margin-top: 36px;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, .05);
            color: rgba(255, 255, 255, .6);
            font-size: 13px;
            font-weight: 500;
        }

        .feature-item i {
            color: #f59e0b;
            width: 16px;
            text-align: center;
        }

        /* Panel Kanan */
        .right-panel {
            width: 440px;
            min-width: 0;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 44px;
            overflow-y: auto;
            /* ★ scroll internal jika layar pendek */
        }

        /* ── BRAND MOBILE ── */
        .mobile-brand {
            display: none;
            text-align: center;
            margin-bottom: 28px;
            width: 100%;
        }

        .mobile-brand .brand-icon {
            width: 64px;
            height: 64px;
            background: #f59e0b;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #000;
            margin-bottom: 14px;
        }

        /* ★ PERBAIKAN UTAMA: teks brand mobile pakai warna GELAP agar terbaca di card putih */
        .mobile-brand h1 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 36px;
            font-weight: 900;
            color: #111827;
            /* hitam gelap, kontras di background putih */
            letter-spacing: 2px;
            line-height: 1;
            margin-bottom: 6px;
        }

        .mobile-brand h1 span {
            color: #f59e0b;
        }

        .mobile-brand p {
            font-size: 13px;
            color: #6b7280;
            /* abu gelap, bukan putih transparan */
            line-height: 1.4;
        }

        /* Form header */
        .form-header {
            text-align: center;
            margin-bottom: 32px;
            width: 100%;
        }

        .form-header h2 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 30px;
            font-weight: 800;
            color: #111827;
            letter-spacing: .5px;
        }

        .form-header p {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 16px;
            width: 100%;
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 6px;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
            pointer-events: none;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            font-size: 14px;
            z-index: 2;
            padding: 4px;
            transition: color .15s;
        }

        .toggle-password:hover {
            color: #f59e0b;
        }

        .form-input {
            width: 100%;
            padding: 11px 13px 11px 40px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Barlow', sans-serif;
            transition: border-color .15s, box-shadow .15s;
            color: #111827;
            background: #fff;
        }

        .input-wrap .form-input[type="password"],
        .input-wrap .form-input[type="text"] {
            padding-right: 40px;
        }

        .form-input:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .15);
        }

        .form-input.is-invalid {
            border-color: #ef4444;
        }

        .invalid-msg {
            font-size: 12px;
            color: #ef4444;
            margin-top: 4px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            width: 100%;
        }

        .remember-row input[type=checkbox] {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #f59e0b;
            flex-shrink: 0;
        }

        .remember-row label {
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: #111827;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 800;
            font-family: 'Barlow Condensed', sans-serif;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all .15s;
        }

        .btn-login:hover {
            background: #1f2937;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, .2);
        }

        /* ── RESPONSIVE MOBILE ── */
        @media (max-width: 768px) {
            body {
                /* ★ hapus align-items: flex-start dan margin-top manual
                   biarkan flex centering yg handle posisi */
                align-items: center;
                justify-content: center;
                padding: 16px;
                /* gradient opsional agar lebih hidup di mobile */
                background: radial-gradient(circle at 50% 20%, #1e293b, #0f172a);
            }

            .login-container {
                flex-direction: column;
                max-width: 420px;
                width: 100%;
                /* ★ hapus min-height agar card mengikuti konten,
                   tidak melar tak perlu sehingga memaksa scroll */
                min-height: auto;
                background: #fff;
                border-radius: 24px;
                box-shadow: 0 20px 50px rgba(0, 0, 0, .5);
                /* ★ tidak ada margin-top manual — centering sudah di body */
            }

            .left-panel {
                display: none;
            }

            .right-panel {
                width: 100%;
                /* ★ padding cukup, tidak perlu besar-besar */
                padding: 32px 24px 36px;
                background: #fff;
                border-radius: 24px;
            }

            .mobile-brand {
                display: block;
            }

            /* Sembunyikan form-header desktop, mobile sudah ada brand */
            .form-header {
                display: none;
            }

            .form-input {
                font-size: 15px;
                padding: 13px 13px 13px 42px;
            }

            .btn-login {
                padding: 15px;
                font-size: 15px;
                letter-spacing: 2px;
            }
        }

        /* Layar sangat kecil */
        @media (max-width: 380px) {
            .right-panel {
                padding: 24px 16px 28px;
            }

            .mobile-brand h1 {
                font-size: 30px;
            }
        }

        /* Landscape mobile */
        @media (max-width: 900px) and (max-height: 600px) {
            body {
                padding: 12px 16px;
                align-items: center;
            }

            .login-container {
                /* ★ landscape: tampil dua kolom lagi tapi kompak, max-width diperkecil */
                flex-direction: row;
                max-width: 680px;
                border-radius: 18px;
            }

            /* Tampilkan panel kiri yang minimalis di landscape */
            .left-panel {
                display: flex;
                flex: none;
                width: 200px;
                padding: 24px 20px;
            }

            .left-icon {
                width: 52px;
                height: 52px;
                font-size: 24px;
                margin-bottom: 16px;
            }

            .left-content h1 {
                font-size: 32px;
            }

            .left-content p {
                font-size: 12px;
            }

            .feature-list {
                margin-top: 20px;
            }

            .feature-item {
                font-size: 11px;
                padding: 7px 0;
            }

            .right-panel {
                flex: 1;
                width: auto;
                padding: 20px 24px 24px;
                border-radius: 0 18px 18px 0;
                justify-content: center;
            }

            /* Di landscape, sembunyikan mobile-brand karena panel kiri sudah muncul */
            .mobile-brand {
                display: none;
            }

            .form-header {
                display: block;
                margin-bottom: 18px;
            }

            .form-header h2 {
                font-size: 22px;
            }

            .form-group {
                margin-bottom: 10px;
            }

            .form-input {
                padding: 9px 13px 9px 38px;
                font-size: 13px;
            }

            .remember-row {
                margin-bottom: 12px;
            }

            .btn-login {
                padding: 11px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <!-- Panel Kiri (hanya desktop) -->
        <div class="left-panel">
            <div class="left-content">
                <div class="left-icon"><i class="fa-solid fa-warehouse"></i></div>
                <h1>GUDANG<span>KU</span></h1>
                <p>Sistem manajemen gudang untuk Consumable, Material, dan Tools dengan tracking peminjaman.</p>
                <div class="feature-list">
                    <div class="feature-item"><i class="fa-solid fa-check"></i> Manajemen stok real-time</div>
                    <div class="feature-item"><i class="fa-solid fa-check"></i> Sistem pinjam-kembalikan tools</div>
                    <div class="feature-item"><i class="fa-solid fa-check"></i> Rekap per pekerjaan / proyek</div>
                    <div class="feature-item"><i class="fa-solid fa-check"></i> Export Excel & PDF</div>
                    <div class="feature-item"><i class="fa-solid fa-check"></i> Alert stok menipis otomatis</div>
                </div>
            </div>
        </div>

        <!-- Panel Kanan (Form) -->
        <div class="right-panel">
            <!-- Brand Mobile -->
            <div class="mobile-brand">
                <div class="brand-icon"><i class="fa-solid fa-warehouse"></i></div>
                <h1>GUDANG<span>KU</span></h1>
                <p>Sistem Manajemen Gudang</p>
            </div>

            <!-- Header Form Desktop -->
            <div class="form-header">
                <h2>MASUK KE SISTEM</h2>
                <p>Masukkan kredensial akun gudang Anda</p>
            </div>

            <form method="POST" action="{{ route('login') }}" style="width:100%;">
                @csrf

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope input-icon"></i>
                        <input type="email" name="email"
                            class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            value="{{ old('email') }}" placeholder="Masukkan email" required autofocus>
                    </div>
                    @error('email')
                        <div class="invalid-msg"><i class="fa-solid fa-circle-xmark"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" name="password" id="password"
                            class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                            placeholder="Masukkan password" required>
                        <i class="fa-regular fa-eye toggle-password" id="togglePasswordBtn"
                            onclick="togglePasswordVisibility()" title="Lihat password"></i>
                    </div>
                    @error('password')
                        <div class="invalid-msg"><i class="fa-solid fa-circle-xmark"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="remember-row">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">Ingat saya di perangkat ini</label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-arrow-right-to-bracket" style="margin-right:8px;"></i>
                    Masuk
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const inp = document.getElementById('password');
            const ico = document.getElementById('togglePasswordBtn');
            const isHidden = inp.type === 'password';
            inp.type = isHidden ? 'text' : 'password';
            ico.classList.toggle('fa-eye', !isHidden);
            ico.classList.toggle('fa-eye-slash', isHidden);
            ico.setAttribute('title', isHidden ? 'Sembunyikan password' : 'Lihat password');
        }
    </script>
</body>

</html>
