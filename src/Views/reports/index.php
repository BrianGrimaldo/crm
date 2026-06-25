<?php
$pageTitle = 'Reportes - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';

$activeTab = $_GET['tab'] ?? 'general';
$selectedSellerId = isset($_GET['seller_id']) && $_GET['seller_id'] !== '' ? (int)$_GET['seller_id'] : null;
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
?>

<style>
    .tabs-container {
        display: flex;
        gap: 1.5rem;
        border-bottom: 2px solid var(--border);
        margin-bottom: 2.5rem;
        padding-bottom: 0.2rem;
    }
    .tab-btn {
        padding: 0.75rem 1.25rem;
        font-size: 1.05rem;
        font-weight: 600;
        text-decoration: none;
        color: var(--text-muted);
        position: relative;
        transition: all 0.3s ease;
    }
    .tab-btn:hover {
        color: var(--primary);
    }
    .tab-btn.active {
        color: var(--primary);
    }
    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--primary);
        border-radius: 3px;
    }
    
    .filter-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(0,0,0,0.03);
        margin-bottom: 2rem;
    }

    .report-table-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(0,0,0,0.03);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-active {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }
    .badge-won {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }
    .badge-lost {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
    .badge-open {
        background: rgba(99, 102, 241, 0.15);
        color: #6366f1;
    }
</style>

<div class="page-header">
    <div>
        <h1>Reportes y Exportación</h1>
        <p>Genera reportes de ventas y analiza la línea de tiempo de tus vendedores.</p>
    </div>
</div>

<div class="tabs-container">
    <a href="?tab=general" class="tab-btn <?= $activeTab === 'general' ? 'active' : '' ?>">
        <i class="fas fa-file-invoice-dollar" style="margin-right: 0.5rem;"></i> Ventas Generales
    </a>
    <a href="?tab=timeline" class="tab-btn <?= $activeTab === 'timeline' ? 'active' : '' ?>">
        <i class="fas fa-history" style="margin-right: 0.5rem;"></i> Línea de Tiempo de Vendedores
    </a>
</div>

<?php if ($activeTab === 'general'): ?>
    <!-- TAB 1: VENTAS GENERALES -->
    <div class="filter-card" style="max-width: 800px;">
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; color: var(--primary);">
            Exportar Oportunidades de Venta
        </h3>
        
        <form action="<?= url('/reportes/exportar-ventas') ?>" method="GET">
            <input type="hidden" name="tab" value="general">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="start_date">Fecha de Inicio</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="end_date">Fecha de Fin</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="seller_id">Filtrar por Vendedor</label>
                    <select id="seller_id" name="seller_id" class="form-control">
                        <option value="">Todos los vendedores</option>
                        <?php foreach ($vendedores as $vendedor): ?>
                            <option value="<?= $vendedor->id ?>" <?= $selectedSellerId === $vendedor->id ? 'selected' : '' ?>>
                                 <?= htmlspecialchars($vendedor->first_name . ' ' . $vendedor->last_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="status">Estado del Trato</label>
                    <select id="status" name="status" class="form-control">
                        <option value="all">Cualquier estado</option>
                        <option value="open">Solo Abiertos</option>
                        <option value="won">Solo Ganados</option>
                        <option value="lost">Solo Perdidos</option>
                    </select>
                </div>
            </div>

            <div style="text-align: right;">
                <button type="submit" class="btn btn-primary" style="padding: 0.85rem 2rem;">
                    <i class="fas fa-file-excel"></i> Descargar Reporte (Excel)
                </button>
            </div>
        </form>
    </div>

<?php elseif ($activeTab === 'timeline'): ?>
    <!-- TAB 2: LÍNEA DE TIEMPO DE VENDEDORES -->
    <div class="filter-card">
        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; color: var(--primary);">
            Filtrar Tiempos por Etapa
        </h3>
        
        <form action="<?= url('/reportes') ?>" method="GET">
            <input type="hidden" name="tab" value="timeline">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="start_date">Fecha de Inicio</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="end_date">Fecha de Fin</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="seller_id">Filtrar por Vendedor</label>
                    <select id="seller_id" name="seller_id" class="form-control">
                        <option value="">Todos los vendedores</option>
                        <?php foreach ($vendedores as $vendedor): ?>
                            <option value="<?= $vendedor->id ?>" <?= $selectedSellerId === $vendedor->id ? 'selected' : '' ?>>
                                 <?= htmlspecialchars($vendedor->first_name . ' ' . $vendedor->last_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem;">
                <button type="submit" class="btn" style="background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border);">
                    <i class="fas fa-search"></i> Consultar en Pantalla
                </button>
                
                <a href="<?= url('/reportes/exportar-timeline?tab=timeline&start_date=' . urlencode($startDate) . '&end_date=<?= urlencode($endDate) ?>&seller_id=<?= $selectedSellerId ?>') ?>" class="btn btn-primary">
                    <i class="fas fa-file-excel"></i> Descargar Reporte (Excel)
                </a>
            </div>
        </form>
    </div>

    <!-- Resultados en Pantalla Visual -->
    <style>
        .timeline-container {
            display: grid;
            gap: 2rem;
            margin-top: 1rem;
        }
        .deal-timeline-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .deal-header {
            padding: 1.5rem;
            background: linear-gradient(to right, var(--bg-main), #ffffff);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .deal-header-main h4 {
            font-size: 1.25rem;
            color: var(--primary);
            margin: 0 0 0.4rem 0;
            font-weight: 800;
        }
        .deal-header-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .deal-header-meta i {
            color: var(--accent);
        }
        .deal-amount {
            font-size: 1.2rem;
            font-weight: 800;
            color: #059669;
            background: #d1fae5;
            padding: 0.4rem 1rem;
            border-radius: 10px;
        }
        
        /* Stepper CSS */
        .stepper-wrapper {
            padding: 2rem 1.5rem;
            overflow-x: auto;
        }
        .stepper {
            display: flex;
            align-items: flex-start;
            min-width: max-content;
        }
        .step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 180px;
            text-align: center;
        }
        /* Connection Line */
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px; /* middle of icon */
            left: 50%;
            width: 100%;
            height: 3px;
            background: var(--border);
            z-index: 1;
        }
        .step.completed:not(:last-child)::after {
            background: linear-gradient(to right, #10b981, #34d399);
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--border);
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 2px var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #94a3b8;
            z-index: 2;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }
        .step.completed .step-icon {
            background: #10b981;
            color: white;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.2);
            border-color: #10b981;
        }
        .step.active .step-icon {
            background: #3b82f6;
            color: white;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.3);
            border-color: #3b82f6;
            animation: pulse-blue 2s infinite;
        }
        
        .step-content h5 {
            font-size: 0.95rem;
            color: var(--text-main);
            margin: 0 0 0.5rem 0;
            font-weight: 700;
        }
        .step-dates {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.6rem;
            line-height: 1.4;
        }
        .duration-pill {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            background: var(--border);
            color: var(--primary);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .step.active .duration-pill {
            background: rgba(59,130,246,0.1);
            color: #2563eb;
            border-color: rgba(59,130,246,0.2);
        }
        
        @keyframes pulse-blue {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
    </style>

    <div class="report-timeline-visual">
        <h3 style="margin-bottom: 1.5rem; color: var(--primary);">Recorrido Visual del Embudo por Trato</h3>
        
        <?php if (empty($timeline)): ?>
            <div style="background: var(--surface); border-radius: 16px; text-align: center; color: var(--text-muted); padding: 4rem 2rem; border: 1px solid var(--border);">
                <i class="fas fa-project-diagram" style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--text-muted);"></i><br>
                <span style="font-size: 1.1rem; font-weight: 500;">No se encontraron recorridos para los filtros seleccionados.</span>
            </div>
        <?php else: 
            // Group timeline rows by deal_id
            $groupedTimeline = [];
            foreach ($timeline as $row) {
                $groupedTimeline[$row['deal_id']]['info'] = [
                    'name' => $row['deal_name'],
                    'seller' => $row['seller_name'],
                    'amount' => $row['amount'],
                    'currency' => $row['currency_code'],
                    'status' => $row['status']
                ];
                $groupedTimeline[$row['deal_id']]['stages'][] = $row;
            }
        ?>
            <div class="timeline-container">
                <?php foreach ($groupedTimeline as $dealId => $dealData): ?>
                    <div class="deal-timeline-card">
                        <!-- Deal Header -->
                        <div class="deal-header">
                            <div class="deal-header-main">
                                <h4><?= htmlspecialchars($dealData['info']['name']) ?></h4>
                                <div class="deal-header-meta">
                                    <span><i class="fas fa-user-tie"></i> <?= htmlspecialchars($dealData['info']['seller']) ?></span>
                                    <span>
                                        <?php if ($dealData['info']['status'] === 'Ganado'): ?>
                                            <span class="badge badge-won"><i class="fas fa-check-circle"></i> Ganado</span>
                                        <?php elseif ($dealData['info']['status'] === 'Perdido'): ?>
                                            <span class="badge badge-lost"><i class="fas fa-times-circle"></i> Perdido</span>
                                        <?php else: ?>
                                            <span class="badge badge-open"><i class="fas fa-door-open"></i> Abierto</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="deal-amount">
                                $<?= number_format((float)$dealData['info']['amount'], 2) ?> <?= htmlspecialchars($dealData['info']['currency']) ?>
                            </div>
                        </div>
                        
                        <!-- Visual Stepper -->
                        <div class="stepper-wrapper">
                            <div class="stepper">
                                <?php 
                                $totalStages = count($dealData['stages']);
                                foreach ($dealData['stages'] as $index => $stage): 
                                    $isLast = ($index === $totalStages - 1);
                                    $isCompleted = !empty($stage['exited_at']);
                                    $isActive = empty($stage['exited_at']);
                                    
                                    $stepClass = 'step';
                                    if ($isCompleted) $stepClass .= ' completed';
                                    if ($isActive) $stepClass .= ' active';
                                ?>
                                    <div class="<?= $stepClass ?>">
                                        <div class="step-icon">
                                            <?php if ($isCompleted): ?>
                                                <i class="fas fa-check"></i>
                                            <?php elseif ($isActive): ?>
                                                <i class="fas fa-spinner fa-spin" style="font-size: 0.85rem;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="step-content">
                                            <h5><?= htmlspecialchars($stage['stage_name']) ?></h5>
                                            <div class="step-dates">
                                                In: <?= date('d M, H:i', strtotime($stage['entered_at'])) ?><br>
                                                Out: <?= $stage['exited_at'] ? date('d M, H:i', strtotime($stage['exited_at'])) : '<span style="color:#2563eb;font-weight:600;">Actual</span>' ?>
                                            </div>
                                            <div class="duration-pill">
                                                <i class="far fa-clock"></i> <?= htmlspecialchars($this->formatDuration((int)$stage['duration'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
