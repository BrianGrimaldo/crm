<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Einsur Global CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FFD100;
            --primary-hover: #E5BC00;
            --primary-text: #1a1f36;
            --bg-start: #1A1F5A;
            --bg-end: #262B72;
            --text-main: #ffffff;
            --text-muted: #cbd5e1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, var(--bg-start), var(--bg-end));
            color: var(--text-main);
            overflow: hidden;
            position: relative;
        }

        /* Abstract Elements */
        .shape {
            position: absolute;
            filter: blur(100px);
            z-index: 0;
            opacity: 0.6;
        }

        .shape-1 {
            width: 600px;
            height: 600px;
            background: #FFD100;
            border-radius: 50%;
            top: -200px;
            right: -200px;
            animation: pulse 8s infinite alternate;
        }

        .shape-2 {
            width: 500px;
            height: 500px;
            background: #4A51BA;
            border-radius: 50%;
            bottom: -100px;
            left: -200px;
            animation: pulse 10s infinite alternate-reverse;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(1.2); opacity: 0.8; }
        }

        nav {
            padding: 2rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 10;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -1px;
            background: linear-gradient(to right, #ffffff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links a {
            color: var(--text-main);
            text-decoration: none;
            font-weight: 500;
            margin-left: 2rem;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5%;
            position: relative;
            z-index: 10;
        }

        .hero-content {
            max-width: 800px;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(to right, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            line-height: 1.6;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -10px rgba(0,0,0,0.3);
        }

        .btn-primary {
            background: var(--primary);
            color: var(--primary-text);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-3px);
            box-shadow: 0 15px 25px -10px var(--primary);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-main);
            border: 2px solid rgba(255,255,255,0.2);
            margin-left: 1rem;
        }

        .btn-outline:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.4);
        }
    </style>
</head>
<body>

    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>

    <nav>
        <div class="logo">EINSUR GLOBAL CRM</div>
        <div class="nav-links">
            <a href="/login" class="btn btn-primary" style="padding: 0.6rem 1.5rem; margin-left: 1rem;">Iniciar Sesión</a>
        </div>
    </nav>

    <main class="main-container">
        <div class="hero-content">
            <h1>Gestión inteligente para tu empresa</h1>
            <p>El sistema Multi-Tenant de Einsur Global te permite administrar clientes, ventas y operaciones desde una plataforma centralizada y segura.</p>
            <div>
                <a href="/login" class="btn btn-primary">Acceder al Sistema</a>
            </div>
        </div>
    </main>

</body>
</html>
