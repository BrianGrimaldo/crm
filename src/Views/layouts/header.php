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
    <link rel="icon" type="image/png" href="/crm_einsurglobal/public/img/icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #FFD100;
            --primary-hover: #E5BC00;
            --primary-text: #1a1f36;
            --bg-sidebar: #1A1F5A;
            --bg-sidebar-hover: #262B72;
            --bg-main: #f8fafc;
            --text-sidebar: #ffffff;
            --text-sidebar-muted: #cbd5e1;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --surface: #ffffff;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { display: flex; min-height: 100vh; background-color: var(--bg-main); color: var(--text-main); }

        /* Sidebar */
        .sidebar { 
            width: 260px; 
            height: 100vh; 
            background: linear-gradient(180deg, #002D62 0%, #001a3d 100%); 
            position: fixed; 
            top: 0; 
            left: 0; 
            z-index: 1000; 
            display: flex; 
            flex-direction: column; 
            box-shadow: 4px 0 20px rgba(0,0,0,0.2); 
        }

        /* --- BRANDING / LOGO --- */
        .sidebar-brand { 
            height: 160px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 25px 20px; 
            margin-bottom: 15px; 
        }
        .logo-container { 
            background-color: rgba(255, 255, 255, 0.98); 
            padding: 20px 25px; 
            border-radius: 12px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.2); 
            width: 100%; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            transition: all 0.3s ease; 
            border: 1px solid rgba(255,255,255,0.1);
        }
        .logo-container:hover { 
            transform: translateY(-4px) scale(1.02); 
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        }
        .logo-container h2 {
            color: #002D62;
            font-size: 1.4rem;
            font-weight: 800;
            margin: 0;
            text-align: center;
        }

        /* --- MENÃš --- */
        .sidebar-menu { 
            flex-grow: 1; 
            overflow-y: auto; 
            padding: 10px 15px; 
            list-style: none; 
            margin: 0; 
        }
        .sidebar-menu::-webkit-scrollbar { width: 4px; }
        .sidebar-menu::-webkit-scrollbar-track { background: transparent; }
        .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .sidebar-menu::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.3); }

        .menu-header { 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            color: rgba(255, 255, 255, 0.55); 
            margin: 20px 10px 8px 15px; 
            font-weight: 600; 
        }
        .sidebar-menu a { 
            display: flex; 
            align-items: center; 
            padding: 12px 18px; 
            color: rgba(255,255,255,0.7); 
            text-decoration: none; 
            font-size: 0.9rem; 
            font-weight: 500; 
            border-radius: 8px; 
            margin-bottom: 4px; 
            transition: all 0.2s ease-in-out; 
            border-left: 3px solid transparent;
        }
        .sidebar-menu i { 
            width: 25px; 
            font-size: 1.1rem; 
            margin-right: 12px; 
            text-align: center; 
            transition: color 0.2s;
        }
        .sidebar-menu a:hover { 
            background: rgba(255,255,255,0.08); 
            color: #ffffff; 
            transform: translateX(6px); 
        }
        .sidebar-menu a:hover i { color: #ffffff; }

        .sidebar-menu a.active { 
            background: linear-gradient(90deg, rgba(110, 223, 246, 0.15) 0%, transparent 100%); 
            color: white; 
            font-weight: 500; 
            text-shadow: 0 0 0.5px white, 0 0 0.5px white; 
            border-left: 3px solid #6edff6;
            transform: translateX(0);
        }
        .sidebar-menu a.active i { 
            color: #6edff6; 
            text-shadow: 0 0 10px rgba(110, 223, 246, 0.4); 
        }

        /* --- FOOTER PERFIL --- */
        .sidebar-footer { 
            padding: 20px 15px; 
            background: rgba(255, 255, 255, 0.03); 
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255,255,255,0.06); 
        }
        .user-card { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            color: white; 
            padding: 5px;
        }
        .user-info { display: flex; align-items: center; gap: 12px; overflow: hidden; }
        
        .user-avatar { 
            width: 42px; 
            height: 42px; 
            background: linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%); 
            color: #002D62; 
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 800; 
            font-size: 1.1rem;
            flex-shrink: 0; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .user-details h6 { 
            margin: 0; 
            font-size: 0.9rem; 
            font-weight: 600; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }
        .user-details span { 
            font-size: 0.7rem; 
            letter-spacing: 0.5px;
            display: block; 
            margin-top: 2px;
            color: rgba(255, 255, 255, 0.55);
        }

        .btn-logout { 
            color: rgba(255, 255, 255, 0.5); 
            transition: all 0.3s ease; 
            font-size: 1.2rem; 
            padding: 8px;
            border-radius: 8px;
        }
        .btn-logout:hover { 
            color: #ff4d4d; 
            background: rgba(255, 77, 77, 0.1);
            transform: scale(1.05); 
        }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-width: 0; width: calc(100% - 260px); }
        .topbar { background: var(--surface); height: 70px; display: flex; align-items: center; justify-content: flex-end; padding: 0 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border-bottom: 1px solid var(--border); }
        .btn-logout { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 0.5rem; }
        .btn-logout:hover { background: #ef4444; color: white; }
        .content-area { padding: 2rem; flex: 1; display: flex; flex-direction: column; min-width: 0; max-width: 100%; overflow-x: hidden; }
        
        .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .page-header h1 { font-size: 1.8rem; font-weight: 700; color: var(--text-main); }
        .page-header p { color: var(--text-muted); margin-top: 0.5rem; }
        
        /* Utils for buttons and tables */
        .btn { padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; transition: all 0.3s ease; }
        .btn-primary { background: var(--bg-sidebar); color: white; }
        .btn-primary:hover { background: var(--bg-sidebar-hover); transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        
        .card { background: var(--surface); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); overflow: hidden; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; padding: 1rem 1.5rem; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; border-bottom: 1px solid var(--border); }
        td { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); color: var(--text-main); font-size: 0.95rem; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }
        
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.5rem; }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 1rem; outline: none; transition: border-color 0.3s; }
        .form-control:focus { border-color: var(--primary-hover); box-shadow: 0 0 0 3px rgba(255, 209, 0, 0.2); }
        
        /* Responsive */
        .btn-menu { display: none; background: none; border: none; color: var(--text-main); cursor: pointer; padding: 0.5rem; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            .topbar { justify-content: space-between; padding: 0 1rem; }
            .btn-menu { display: block; }
            .content-area { padding: 1rem; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .page-header > div:last-child { width: 100%; display: flex; flex-wrap: wrap; }
            .page-header .btn { flex: 1; justify-content: center; text-align: center; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="logo-container">
                <?php $logoUrl = $_SESSION['tenant_logo'] ?? '/crm_einsurglobal/public/img/logoeglobal.png'; ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo Empresa" style="max-width: 100%; max-height: 80px; object-fit: contain;">
            </div>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-header">Principal</div>
            <a href="/crm_einsurglobal/public/dashboard" class="<?= strpos($currentPath, 'dashboard') !== false ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>

            <div class="menu-header">CRM & Ventas</div>
            <a href="/crm_einsurglobal/public/contactos" class="<?= strpos($currentPath, 'contactos') !== false ? 'active' : '' ?>">
                <i class="fas fa-address-book"></i> Contactos
            </a>
            <a href="/crm_einsurglobal/public/organizaciones" class="<?= strpos($currentPath, 'organizaciones') !== false ? 'active' : '' ?>">
                <i class="fas fa-building"></i> Organizaciones
            </a>
            <a href="/crm_einsurglobal/public/oportunidades/pipeline" class="<?= strpos($currentPath, 'oportunidades') !== false ? 'active' : '' ?>">
                <i class="fas fa-funnel-dollar"></i> Ventas
            </a>

            <div class="menu-header">AdministraciÃ³n</div>
            <?php 
            $role = isset($_SESSION['user_role']) ? strtolower(str_replace('-', '', $_SESSION['user_role'])) : '';
            if ($role === 'superadmin' || $role === 'admin'): 
            ?>
            <a href="/crm_einsurglobal/public/vendedores" class="<?= strpos($currentPath, 'vendedores') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-tie"></i> Control Vendedores
            </a>
            <?php if (isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin']): ?>
            <a href="/crm_einsurglobal/public/empresas" class="<?= strpos($currentPath, 'empresas') !== false ? 'active' : '' ?>">
                <i class="fas fa-building-user"></i> Empresas
            </a>
            <?php endif; ?>
            <?php endif; ?>
            <?php if (\App\Core\Permission::has('reports', 'view')): ?>
            <a href="/crm_einsurglobal/public/reportes" class="<?= strpos($currentPath, 'reportes') !== false ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Reportes y Excel
            </a>
            <?php endif; ?>

            <?php if (\App\Core\Permission::has('products', 'view')): ?>
            <a href="/crm_einsurglobal/public/productos" class="<?= strpos($currentPath, 'productos') !== false ? 'active' : '' ?>">
                <i class="fas fa-boxes"></i> Inventario / Equipos
            </a>
            <?php endif; ?>
            
            <?php if (\App\Core\Permission::has('users', 'view')): ?>
            <a href="/crm_einsurglobal/public/usuarios" class="<?= strpos($currentPath, 'usuarios') !== false ? 'active' : '' ?>">
                <i class="fas fa-users-cog"></i> Usuarios
            </a>
            <?php endif; ?>
            <a href="/crm_einsurglobal/public/perfil" class="<?= strpos($currentPath, 'perfil') !== false ? 'active' : '' ?>">
                <i class="fas fa-user-circle"></i> Mi Perfil
            </a>
            <?php if (\App\Core\Permission::has('settings', 'view')): ?>
            <div style="margin: 1rem 1.5rem 0.5rem; font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px;">
                ConfiguraciÃ³n
            </div>
            <a href="/crm_einsurglobal/public/roles" class="<?= strpos($currentPath, 'roles') !== false ? 'active' : '' ?>">
                <i class="fas fa-shield-alt"></i> Roles y Permisos
            </a>
            <a href="/crm_einsurglobal/public/configuracion/embudo" class="<?= strpos($currentPath, 'pipeline') !== false ? 'active' : '' ?>">
                <i class="fas fa-stream"></i> Embudo de Ventas
            </a>
            <?php endif; ?>
        </div>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-info">
                    <div class="user-avatar"><?= strtoupper(substr($userEmail, 0, 1)) ?></div>
                    <div class="user-details">
                        <h6><?= htmlspecialchars($userEmail) ?></h6>
                        <span style="<?= ($_SESSION['user_role'] ?? '') === 'admin' ? 'color: var(--accent-cyan); font-weight: 600;' : '' ?>">
                            <?= htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Usuario')) ?>
                        </span>
                    </div>
                </div>
                <a href="/crm_einsurglobal/public/logout" class="btn-logout" title="Cerrar SesiÃ³n">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="topbar">
            <!-- BotÃ³n de menÃº mÃ³vil flotante -->
            <button class="btn-menu" style="margin-right: auto;" onclick="document.querySelector('.sidebar').classList.toggle('active')">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            
            <?php if (isset($_SESSION['available_tenants']) && count($_SESSION['available_tenants']) > 1): ?>
                <div style="display: flex; align-items: center; gap: 0.8rem; background: #f1f5f9; padding: 0.3rem 0.8rem; border-radius: 8px;">
                    <label style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">ðŸ¢ Empresa:</label>
                    <select class="form-control" style="width: auto; padding: 0.4rem 2rem 0.4rem 0.8rem; font-size: 0.95rem; font-weight: 600; color: #0f172a; border-color: transparent; background-color: transparent; cursor: pointer;" onchange="window.location.href='/crm_einsurglobal/public/switch-tenant?id='+this.value">
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
