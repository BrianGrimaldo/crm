<?php
$userEmail = $_SESSION['user_email'] ?? 'Usuario';
$tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Einsur Global CRM' ?></title>
    <link rel="icon" type="image/png" href="<?= url('/img/icon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #002D62;
            --primary-hover: #004080;
            --primary-light: #e6f0fa;
            --accent: #6edff6;
            --accent-hover: #4dcde8;
            --bg-sidebar: #001f44;
            --bg-main: #f8fafc;
            --text-title: #0f172a;
            --text-main: #334155;
            --text-muted: #64748b;
            --surface: #ffffff;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius-md: 6px;
            --radius-lg: 8px;
        }

        [data-theme="dark"] {
            --primary: #6edff6;
            --primary-hover: #4dcde8;
            --primary-light: rgba(110, 223, 246, 0.1);
            --bg-main: #0f172a;
            --surface: #1e293b;
            --text-title: #f8fafc;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --border: #334155;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.5);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.6);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.7);
        }

        [data-theme="dark"] .topbar {
            background: rgba(30, 41, 59, 0.8) !important;
            border-bottom-color: var(--border) !important;
        }

        [data-theme="dark"] th {
            background: #0f172a !important;
        }

        [data-theme="dark"] .form-control {
            background: #0f172a !important;
            color: var(--text-main) !important;
        }

        [data-theme="dark"] .form-control:focus {
            background: var(--surface) !important;
        }

        [data-theme="dark"] select.form-control option {
            background: var(--surface) !important;
            color: var(--text-main) !important;
        }

        [data-theme="dark"] .tenant-selector-box {
            background: rgba(0, 0, 0, 0.2) !important;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-main);
            color: var(--text-main);
            font-size: 15px;
            font-weight: 400;
        }

        /* Sidebar */
        .sidebar {
            width: 270px;
            height: 100vh;
            background: linear-gradient(160deg, var(--bg-sidebar) 0%, #001126 100%);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
        }

        /* --- BRANDING / LOGO --- */
        .sidebar-brand {
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin-bottom: 5px;
        }

        .logo-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(110, 223, 246, 0.2);
            border-color: rgba(110, 223, 246, 0.4);
        }

        /* --- MENÚ --- */
        .sidebar-menu {
            flex-grow: 1;
            overflow-y: auto;
            padding: 10px 15px;
            list-style: none;
            margin: 0;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .menu-header {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.4);
            margin: 25px 10px 10px 15px;
            font-weight: 700;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            border-radius: 12px;
            margin-bottom: 6px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .sidebar-menu i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 12px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.05);
        }

        .sidebar-menu a:hover i {
            transform: scale(1.15);
            color: var(--accent);
        }

        .sidebar-menu a.active {
            background: linear-gradient(135deg, rgba(110, 223, 246, 0.15) 0%, rgba(110, 223, 246, 0.02) 100%);
            color: white;
            font-weight: 600;
            border: 1px solid rgba(110, 223, 246, 0.2);
            box-shadow: inset 0 0 12px rgba(110, 223, 246, 0.05);
        }

        .sidebar-menu a.active i {
            color: var(--accent);
            text-shadow: 0 0 15px rgba(110, 223, 246, 0.6);
        }

        /* --- FOOTER PERFIL --- */
        .sidebar-footer {
            padding: 16px;
            background: transparent;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px;
            border-radius: var(--radius-md);
            transition: background 0.2s;
        }

        .user-card:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .user-details h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-details span {
            font-size: 0.75rem;
            display: block;
            margin-top: 2px;
            color: rgba(255, 255, 255, 0.5);
        }

        .btn-logout-icon {
            color: rgba(255, 255, 255, 0.4);
            transition: all 0.3s ease;
            font-size: 1.1rem;
            padding: 8px;
            border-radius: 8px;
        }

        .btn-logout-icon:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 270px;
            display: flex;
            flex-direction: column;
            min-width: 0;
            width: calc(100% - 270px);
        }

        .topbar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 1.2rem;
            padding: 0 2.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            z-index: 900;
        }

        .content-area {
            padding: 2.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            max-width: 100%;
            overflow-x: hidden;
        }

        .page-header {
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-title);
            letter-spacing: -0.5px;
        }

        .page-header p {
            color: var(--text-muted);
            margin-top: 0.3rem;
            font-size: 1rem;
        }

        /* Utils for buttons and tables */
        .btn {
            padding: 0.75rem 1.75rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn i {
            font-size: 1.1em;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 45, 98, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 45, 98, 0.3);
            background: linear-gradient(135deg, var(--primary-hover) 0%, var(--primary) 100%);
        }

        .btn-logout {
            background: transparent;
            color: rgba(255, 255, 255, 0.6);
            border: none;
            padding: 0.6rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0, 0, 0, 0.03);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
        }

        th {
            background: var(--bg-main);
            padding: 12px 16px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            font-weight: 600;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }

        td {
            padding: 12px 16px;
            height: 48px;
            border-bottom: 1px solid var(--border);
            color: var(--text-main);
            font-size: 0.95rem;
            font-weight: 400;
            text-align: left;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: var(--primary-light);
        }

        .alert {
            padding: 1.2rem;
            border-radius: var(--radius-md);
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.6rem;
        }

        .form-control {
            width: 100%;
            padding: 0.85rem 1.2rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-main);
            background: #fdfdfd;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(110, 223, 246, 0.15);
        }

        select.form-control {
            cursor: pointer;
        }

        /* Utility classes for Tables and Typography */
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .text-left { text-align: left !important; }
        .tabular-nums { font-variant-numeric: tabular-nums; }
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }
        .text-title { color: var(--text-title); }
        .text-muted { color: var(--text-muted); }
        
        /* Badges for desaturated states */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-error { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }

        /* Empty State */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            text-align: center;
            background: var(--surface);
            border-radius: var(--radius-lg);
            border: 1px dashed var(--border);
            margin: 24px 0;
        }
        .empty-state i {
            font-size: 48px;
            color: var(--text-muted);
            margin-bottom: 16px;
        }
        .empty-state h3 {
            color: var(--text-title);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .empty-state p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 24px;
            max-width: 400px;
        }

        .btn-theme {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.5rem;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }

        .btn-theme:hover {
            color: var(--accent);
            transform: scale(1.1);
        }

        /* Responsive */
        .btn-menu {
            display: none;
            background: none;
            border: none;
            color: var(--text-main);
            cursor: pointer;
            padding: 0.5rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .topbar {
                justify-content: space-between;
                padding: 0 1rem;
                height: 64px;
            }

            .btn-menu {
                display: block;
                font-size: 1.4rem;
                color: var(--primary);
            }

            .content-area {
                padding: 1.5rem 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                margin-bottom: 1.5rem;
            }

            .page-header .btn {
                flex: 1;
                justify-content: center;
                text-align: center;
            }
        }
    </style>
    <script>
        // Aplicar modo oscuro inmediatamente para evitar parpadeo blanco
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>

<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="logo-container">
                <?php
                if (isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin']) {
                    $logoUrl = '/img/grupo_einsur.png';
                } else {
                    $logoUrl = $_SESSION['tenant_logo'] ?? '/img/logoeglobal.png';
                }
                ?>
                <img src="<?= url(htmlspecialchars($logoUrl)) ?>" alt="Logo Empresa"
                    style="max-width: 100%; max-height: 80px; object-fit: contain;">
            </div>
        </div>

        <div class="sidebar-menu">
            <div class="menu-header">Principal</div>
            <?php if (isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin']): ?>
                <a href="<?= url('/grupo-einsur') ?>"
                    class="<?= strpos($currentPath, 'grupo-einsur') !== false ? 'active' : '' ?>">
                    <i class="fas fa-globe"></i> Grupo EINSUR
                </a>
                <a href="<?= url('/dashboard') ?>" class="<?= $currentPath === '/dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> Dashboard Empresa
                </a>
            <?php else: ?>
                <a href="<?= url('/dashboard') ?>"
                    class="<?= strpos($currentPath, 'dashboard') !== false ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
            <?php endif; ?>

            <?php
            $roleStr = strtolower(str_replace(['-', ' '], '', $_SESSION['user_role'] ?? ''));
            $isCobranza = strpos($roleStr, 'cobranza') !== false 
                       || strpos($roleStr, 'collection') !== false 
                       || strpos($roleStr, 'cobrador') !== false;
            $isCEO = in_array($roleStr, ['superadmin', 'superadministrador', 'ceo', 'dirección']) || (!empty($_SESSION['is_superadmin']) && (bool) $_SESSION['is_superadmin']);
            ?>

            <?php if (!$isCobranza && !$isCEO): ?>
                <div class="menu-header">CRM & Ventas</div>
                <?php if (\App\Core\Permission::has('contacts', 'view')): ?>
                    <a href="<?= url('/contactos') ?>"
                        class="<?= strpos($currentPath, 'contactos') !== false ? 'active' : '' ?>">
                        <i class="fas fa-address-book"></i> Contactos
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Permission::has('contacts', 'create')): ?>
                    <a href="<?= url('/importar') ?>" class="<?= strpos($currentPath, 'importar') !== false ? 'active' : '' ?>">
                        <i class="fas fa-file-import"></i> Importar Datos
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Permission::has('accounts', 'view')): ?>
                    <a href="<?= url('/organizaciones') ?>"
                        class="<?= strpos($currentPath, 'organizaciones') !== false ? 'active' : '' ?>">
                        <i class="fas fa-building"></i> Organizaciones
                    </a>
                <?php endif; ?>
                <?php if (\App\Core\Permission::has('deals', 'view')): ?>
                    <a href="<?= url('/oportunidades/pipeline') ?>"
                        class="<?= strpos($currentPath, 'oportunidades') !== false ? 'active' : '' ?>">
                        <i class="fas fa-funnel-dollar"></i> Ventas
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (\App\Core\Permission::has('deals', 'view') && !$isCobranza): ?>
                <a href="<?= url('/ia') ?>" class="<?= strpos($currentPath, '/ia') !== false ? 'active' : '' ?>">
                    <i class="fas fa-robot"></i> Asistente IA
                </a>
                <a href="<?= url('/metas') ?>" class="<?= strpos($currentPath, '/metas') !== false ? 'active' : '' ?>">
                    <i class="fas fa-bullseye"></i> Metas y Objetivos
                </a>
            <?php endif; ?>


            <?php if (\App\Core\Permission::has('finance', 'view') || \App\Core\Permission::has('reports', 'view')): ?>
                <div class="menu-header">Finanzas</div>
            <?php endif; ?>
            <?php if (\App\Core\Permission::has('finance', 'view')): ?>
                <?php if (!$isCobranza): ?>
                    <a href="<?= url('/finanzas') ?>"
                        class="<?= strpos($currentPath, 'finanzas') !== false && strpos($currentPath, 'cobranza') === false && strpos($currentPath, 'auditoria') === false ? 'active' : '' ?>">
                        <i class="fas fa-file-invoice-dollar"></i> Dashboard Finanzas
                    </a>
                <?php endif; ?>

                <?php if ($isCobranza || in_array($roleStr, ['superadmin', 'admin'])): ?>
                    <a href="<?= url('/finanzas/cobranza') ?>"
                        class="<?= strpos($currentPath, 'cobranza') !== false ? 'active' : '' ?>">
                        <i class="fas fa-wallet"></i> Portal de Cobranza
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (\App\Core\Permission::has('reports', 'view')): ?>
                <a href="<?= url('/analiticas') ?>"
                    class="<?= strpos($currentPath, 'analiticas') !== false ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Analíticas y Gráficas
                </a>
            <?php endif; ?>

            <div class="menu-header">Administración</div>
            <?php
            $role = isset($_SESSION['user_role']) ? strtolower(str_replace('-', '', $_SESSION['user_role'])) : '';
            if ($role === 'superadmin' || $role === 'admin' || $role === 'ceo' || $role === 'dirección'):
                ?>
                <a href="<?= url('/vendedores') ?>"
                    class="<?= strpos($currentPath, 'vendedores') !== false ? 'active' : '' ?>">
                    <i class="fas fa-user-tie"></i> Control Vendedores
                </a>
                <a href="<?= url('/finanzas/ceo/auditoria') ?>"
                    class="<?= strpos($currentPath, 'auditoria') !== false ? 'active' : '' ?>">
                    <i class="fas fa-search-dollar"></i> Auditoría de Ventas
                </a>
                <?php if (isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin']): ?>
                    <a href="<?= url('/empresas') ?>" class="<?= strpos($currentPath, 'empresas') !== false ? 'active' : '' ?>">
                        <i class="fas fa-building-user"></i> Empresas
                    </a>
                <?php endif; ?>
            <?php endif; ?>


            <?php if (\App\Core\Permission::has('activities', 'view') && !$isCEO && !$isCobranza): ?>
                <a href="<?= url('/tickets') ?>" class="<?= strpos($currentPath, 'tickets') !== false ? 'active' : '' ?>">
                    <i class="fas fa-ticket-alt"></i> Tickets de Soporte
                </a>
                <a href="<?= url('/metricas') ?>" class="<?= strpos($currentPath, 'metricas') !== false && strpos($currentPath, 'sla') === false ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Dashboard Live
                </a>
                <a href="<?= url('/metricas/sla') ?>" class="<?= strpos($currentPath, 'sla') !== false ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i> Reportes SLA
                </a>
                <a href="<?= url('/tareas') ?>" class="<?= strpos($currentPath, 'tareas') !== false ? 'active' : '' ?>">
                    <i class="fas fa-check-square"></i> Bitácora de Tareas
                </a>
            <?php endif; ?>

            <a href="https://ticketseg.einsursupply.com/" target="_blank"
                class="<?= strpos($currentPath, 'tickets') !== false ? 'active' : '' ?>">
                <i class="fas fa-headset"></i> Soporte y Tickets
            </a>

            <?php if (\App\Core\Permission::has('reports', 'view')): ?>
                <a href="<?= url('/reportes') ?>" class="<?= strpos($currentPath, 'reportes') !== false ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Reportes y Excel
                </a>
            <?php endif; ?>

            <?php if (\App\Core\Permission::has('products', 'view')): ?>
                <a href="<?= url('/productos') ?>"
                    class="<?= strpos($currentPath, 'productos') !== false ? 'active' : '' ?>">
                    <i class="fas fa-boxes"></i> Inventario / Equipos
                </a>
            <?php endif; ?>

            <?php if (\App\Core\Permission::has('users', 'view')): ?>
                <a href="<?= url('/usuarios') ?>" class="<?= strpos($currentPath, 'usuarios') !== false ? 'active' : '' ?>">
                    <i class="fas fa-users-cog"></i> Usuarios
                </a>
            <?php endif; ?>
            <a href="<?= url('/perfil') ?>" class="<?= strpos($currentPath, 'perfil') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-circle"></i> Mi Perfil
            </a>
            <?php if (\App\Core\Permission::has('settings', 'view')): ?>
                <div
                    style="margin: 1rem 1.5rem 0.5rem; font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">
                    Configuración
                </div>
                <a href="<?= url('/roles') ?>" class="<?= strpos($currentPath, 'roles') !== false ? 'active' : '' ?>">
                    <i class="fas fa-shield-alt"></i> Roles y Permisos
                </a>
                <a href="<?= url('/configuracion/embudo') ?>"
                    class="<?= strpos($currentPath, 'pipeline') !== false ? 'active' : '' ?>">
                    <i class="fas fa-stream"></i> Embudo de Ventas
                </a>
                <a href="<?= url('/configuracion/tipificaciones') ?>"
                    class="<?= strpos($currentPath, 'tipificaciones') !== false ? 'active' : '' ?>">
                    <i class="fas fa-tags"></i> Tipificaciones
                </a>
            <?php endif; ?>
        </div>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-info">
                    <?php $displayUserName = $_SESSION['user_name'] ?? $userEmail ?? 'Usuario'; ?>
                    <div class="user-avatar"><?= strtoupper(substr($displayUserName, 0, 1)) ?></div>
                    <div class="user-details">
                        <h6 title="<?= htmlspecialchars($displayUserName) ?>"><?= htmlspecialchars($displayUserName) ?></h6>
                        <span><?= htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Usuario')) ?></span>
                    </div>
                </div>
                <a href="<?= url('/logout') ?>" class="btn-logout" title="Cerrar Sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <!-- Botón de menú móvil flotante -->
            <button class="btn-menu"
                onclick="document.querySelector('.sidebar').classList.toggle('active')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Logo EJE Comercial (Lado Izquierdo) -->
            <div class="topbar-left-brand" style="display: flex; align-items: center; margin-right: auto;">
                <img src="<?= url('/img/EJE_Comercial.png') ?>" alt="EJE Comercial" style="height: 52px; width: auto; object-fit: contain;">
            </div>

            <!-- Logo Grupo EINSUR (Lado Derecho) -->
            <div class="topbar-brand" style="display: flex; align-items: center;">
                <img src="<?= url('/img/grupo_einsur.png') ?>" alt="Grupo EINSUR" style="height: 40px; width: auto; object-fit: contain;">
            </div>

            <!-- Botón Modo Oscuro -->
            <button id="theme-toggle" class="btn-theme" title="Modo Oscuro / Claro">
                <i class="fas fa-moon"></i>
            </button>

            <?php if (isset($_SESSION['available_tenants']) && count($_SESSION['available_tenants']) > 1): ?>
                <div class="tenant-selector-box"
                    style="display: flex; align-items: center; gap: 0.8rem; background: var(--border); padding: 0.3rem 0.8rem; border-radius: 8px;">
                    <label
                        style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i
                            class="fas fa-building"></i> Empresa:</label>
                    <select class="form-control"
                        style="width: auto; padding: 0.4rem 2rem 0.4rem 0.8rem; font-size: 0.95rem; font-weight: 600; color: var(--text-main); border-color: transparent; background-color: transparent; cursor: pointer;"
                        onchange="window.location.href='<?= url('/switch-tenant') ?>?id='+this.value">
                        <?php foreach ($_SESSION['available_tenants'] as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $t['id'] == $_SESSION['tenant_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>

        <div class="content-area">
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>