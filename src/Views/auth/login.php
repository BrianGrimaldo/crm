<?php
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EJE Comercial — Iniciar Sesión</title>
    <link rel="icon" type="image/png" href="<?= url('/img/icon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            min-height: 100vh;
            background-color: #f1f5f9;
            background-image: 
                radial-gradient(at 0% 0%, rgba(26, 31, 90, 0.09) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(26, 31, 90, 0.09) 0px, transparent 50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        /* Ambient glow behind the card */
        body::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(26,31,90,0.03) 0%, rgba(255,255,255,0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
        }

        /* ── Card ── */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 0;
            width: 100%;
            max-width: 480px;
            box-shadow: 
                0 25px 50px -12px rgba(26, 31, 90, 0.15),
                0 0 0 1px rgba(26, 31, 90, 0.02),
                inset 0 1px 0 rgba(255, 255, 255, 1);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* Solid top bar */
        .login-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 5px;
            background: #1A1F5A;
            z-index: 20;
        }

        .card-body {
            padding: 0 44px 44px;
        }

        .brand-lockup {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 0 0;
        }

        .brand-lockup img {
            width: 100%;
            max-width: 270px;
            height: auto;
            display: block;
            margin: -35px 0 -45px 0; /* Trim transparent padding */
        }

        .brand-text {
            padding: 0 0 24px;
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .brand-desc {
            font-size: 0.95rem;
            color: #334155;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .brand-sub {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }

        /* ── Form ── */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            width: 20px;
            height: 20px;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.2s;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 13px 16px 13px 44px;
            font-size: 0.95rem;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }

        .form-control.with-eye {
            padding-right: 44px;
        }

        .form-control::placeholder { color: #94a3b8; font-weight: 400; }

        .form-control:focus {
            border-color: #1A1F5A;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(26,31,90,0.1), inset 0 1px 2px rgba(0,0,0,0.01);
            transform: translateY(-1px);
        }

        .form-control:focus + .btn-eye {
            transform: translateY(-1px);
        }

        .input-wrapper:focus-within .input-icon {
            color: #1A1F5A;
            transform: translateY(-1px);
        }

        .btn-eye {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2;
        }

        .btn-eye:hover {
            color: #475569;
        }

        .btn-eye svg {
            width: 20px;
            height: 20px;
        }

        .forgot-link {
            text-align: right;
            margin-top: 8px;
        }

        .forgot-link a {
            font-size: 0.82rem;
            color: #1A1F5A;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link a:hover {
            text-decoration: underline;
        }

        /* ── Button ── */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #1A1F5A 0%, #29317a 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 8px 16px -4px rgba(26, 31, 90, 0.3), inset 0 1px 0 rgba(255,255,255,0.1);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: all 0.4s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -4px rgba(26, 31, 90, 0.4), inset 0 1px 0 rgba(255,255,255,0.2);
        }

        .btn-submit:hover::after {
            left: 100%;
        }

        .btn-submit:active { 
            transform: translateY(0); 
            box-shadow: 0 4px 8px -2px rgba(26, 31, 90, 0.3);
        }

        /* ── Error ── */
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        /* ── Portal link ── */
        .portal-link {
            margin-top: 24px;
            text-align: center;
        }

        .portal-link a {
            font-size: 0.9rem;
            color: #475569;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: color 0.2s;
        }

        .portal-link a:hover { color: #1A1F5A; }

        /* ── Footer ── */
        .login-footer {
            margin-top: 36px;
            text-align: center;
            font-size: 0.78rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="login-card">

        <div class="card-body">

        <div class="brand-lockup">
            <img src="<?= url('/img/EJE_Comercial.png') ?>" alt="EJE Comercial">
        </div>

        <div class="brand-text">
            <p class="brand-desc">EJE Comercial es la plataforma de gestión que centraliza y ordena la información comercial de todas las empresas y unidades de negocio del Grupo EINSUR.</p>
            <p class="brand-sub">Ingresa tus credenciales para acceder</p>
        </div>

        <?php if ($error): ?>
            <div class="error-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('/login') ?>" method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-wrapper">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <input type="email" id="email" name="email" class="form-control"
                        placeholder="usuario@einsurglobal.com" required autocomplete="email" autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-wrapper">
                    <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <input type="password" id="password" name="password" class="form-control with-eye"
                        placeholder="••••••••" required autocomplete="current-password">
                    <button type="button" class="btn-eye" onclick="togglePassword()" tabindex="-1">
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <div class="forgot-link">
                    <a href="#">¿Olvidaste tu contraseña?</a>
                </div>
            </div>

            <button type="submit" class="btn-submit">Iniciar Sesión</button>
        </form>

        <div class="portal-link">
            <a href="<?= url('/portal') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2C6.477 2 2 6.477 2 12v6a2 2 0 002 2h2a2 2 0 002-2v-3a2 2 0 00-2-2H4a10 10 0 1120 0v-1a2 2 0 00-2-2h-2a2 2 0 00-2 2v3a2 2 0 002 2h2a2 2 0 002-2v-6c0-5.523-4.477-10-10-10z"/></svg>
                Portal de Soporte
            </a>
        </div>
        </div><!-- /.card-body -->


    <div class="login-footer">
        © <?= date('Y') ?> Grupo EINSUR · EJE Comercial
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
            } else {
                pwd.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }
    </script>
</body>
</html>
