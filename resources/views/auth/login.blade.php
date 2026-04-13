<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        body {
            font-family: 'Barlow', sans-serif;
            min-height: 100vh;
            background: #111827;
            display: flex;
        }

        /* Left panel */
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
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
            max-width: 360px;
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
            color: #fff;
            letter-spacing: 2px;
            line-height: 1;
            margin-bottom: 10px;
        }

        .left-content h1 span {
            color: #f59e0b;
        }

        .left-content p {
            font-size: 14px;
            color: rgba(255, 255, 255, .4);
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

        /* Right panel - form */
        .right-panel {
            width: 440px;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 44px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 32px;
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

        .form-input {
            width: 100%;
            padding: 11px 13px 11px 40px;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Barlow', sans-serif;
            transition: border-color .15s, box-shadow .15s;
            color: #111827;
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
        }

        .remember-row input[type=checkbox] {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #f59e0b;
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

        .hint-box {
            margin-top: 24px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 13px 15px;
            width: 100%;
        }

        .hint-box p {
            font-size: 12px;
            color: #78350f;
            line-height: 1.7;
        }

        .hint-box strong {
            font-weight: 800;
            display: block;
            margin-bottom: 2px;
        }

        @media (max-width: 768px) {
            .left-panel {
                display: none;
            }

            .right-panel {
                width: 100%;
            }
        }
    </style>
</head>

<body>

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

    <div class="right-panel">
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
                        value="{{ old('email', 'admin@gudang.com') }}" placeholder="admin@gudang.com" required
                        autofocus>
                </div>
                @error('email')
                    <div class="invalid-msg"><i class="fa-solid fa-circle-xmark"></i> {{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
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

</body>

</html>
