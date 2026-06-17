<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Autoservicio de Clientes - Einsur Global</title>
    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-color: #f8fafc;
            --surface: #ffffff;
            --primary: #1e1b4b;
            --accent: #6366f1;
            --accent-light: #818cf8;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            background: var(--primary);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand span {
            color: var(--accent-light);
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 700;
            font-size: 0.95rem;
            display: block;
        }

        .tenant-name {
            font-size: 0.75rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }

        /* Contenedor Principal */
        .main-container {
            max-width: 1200px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1.5rem;
            flex: 1;
        }

        /* Alertas */
        .alert-success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Grid */
        .grid-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 900px) {
            .grid-layout {
                grid-template-columns: 1fr;
            }
        }

        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 1.75rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
        }

        .section-title i {
            color: var(--accent);
        }

        /* Tablas */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 0.85rem 1rem;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.92rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }

        .badge-open { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
        .badge-in_progress { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .badge-resolved { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .badge-closed { background: rgba(100, 116, 139, 0.1); color: #64748b; }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.92rem;
            outline: none;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--accent);
        }

        textarea.form-control {
            resize: vertical;
        }

        .btn-submit {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            background: var(--accent-light);
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-brand">
        <i class="fas fa-headset"></i>
        <span>Portal de Autoservicio</span>
    </div>
    <div class="navbar-user">
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($_SESSION['portal_contact_name']) ?></span>
            <span class="tenant-name"><?= htmlspecialchars($_SESSION['portal_tenant_name']) ?></span>
        </div>
        <a href="/portal/logout" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</nav>

<div class="main-container">

    <?php if (isset($_SESSION['portal_success'])): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['portal_success']) ?></span>
            <?php unset($_SESSION['portal_success']); ?>
        </div>
    <?php endif; ?>

    <div class="grid-layout">
        
        <!-- Columna Izquierda: Productos e Historial de Tickets -->
        <div>
            
            <!-- Productos / Catálogo -->
            <div class="section-card">
                <h2 class="section-title"><i class="fas fa-boxes"></i> Productos y Equipos en Catálogo</h2>
                <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 1.5rem;">Consulta los precios vigentes y el stock disponible para tomar decisiones de compra.</p>
                
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio Unitario</th>
                                <th>Disponibilidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay productos disponibles actualmente.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td><strong style="color: var(--accent);"><?= htmlspecialchars($p->sku) ?></strong></td>
                                        <td><strong><?= htmlspecialchars($p->name) ?></strong></td>
                                        <td><?= htmlspecialchars($p->category_name ?? 'General') ?></td>
                                        <td>$<?= number_format((float)$p->unit_price, 2) ?></td>
                                        <td>
                                            <?php 
                                            $stock = (float)($p->stock ?? 0); 
                                            if ($stock > 10) {
                                                echo '<span style="color: #10b981; font-weight:700;"><i class="fas fa-check-circle"></i> En Stock</span>';
                                            } elseif ($stock > 0) {
                                                echo '<span style="color: #f59e0b; font-weight:700;"><i class="fas fa-exclamation-triangle"></i> Poco Stock (' . $stock . ')</span>';
                                            } else {
                                                echo '<span style="color: #ef4444; font-weight:700;"><i class="fas fa-times-circle"></i> Agotado</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mis Tickets de Soporte -->
            <div class="section-card">
                <h2 class="section-title"><i class="fas fa-ticket-alt"></i> Mis Solicitudes de Soporte</h2>
                
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Asunto</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Fecha de Creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tickets)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No tienes solicitudes de soporte registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tickets as $t): ?>
                                    <tr>
                                        <td>#<?= $t->id ?></td>
                                        <td><strong><?= htmlspecialchars($t->subject) ?></strong></td>
                                        <td><?= htmlspecialchars($t->category ?? 'General') ?></td>
                                        <td>
                                            <span class="badge badge-<?= $t->status ?>">
                                                <?= str_replace('_', ' ', $t->status) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($t->created_at)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Columna Derecha: Formulario de Nuevo Ticket -->
        <div>
            <div class="section-card" style="position: sticky; top: 5.5rem;">
                <h2 class="section-title"><i class="fas fa-plus-circle"></i> Reportar un Problema</h2>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.25rem;">Abre una nueva solicitud de soporte y uno de nuestros agentes la atenderá de inmediato.</p>
                
                <form action="/portal/ticket" method="POST">
                    <div class="form-group">
                        <label for="subject" class="form-label">Asunto / Resumen del problema *</label>
                        <input type="text" id="subject" name="subject" class="form-control" placeholder="Ej. Falla en cargador de laptop" required>
                    </div>

                    <div class="form-group">
                        <label for="category" class="form-label">Categoría del problema</label>
                        <select id="category" name="category" class="form-control">
                            <option value="Soporte Técnico">Soporte Técnico</option>
                            <option value="Falla de Hardware">Falla de Hardware</option>
                            <option value="Problema de Software">Problema de Software</option>
                            <option value="Acceso / Cuentas">Acceso / Cuentas</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Descripción detallada</label>
                        <textarea id="description" name="description" class="form-control" rows="5" placeholder="Describe los síntomas o detalles del problema para poder ayudarte mejor..." required></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i>
                        <span>Enviar Solicitud</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

</body>
</html>
