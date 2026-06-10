<?php
$pageTitle = 'Control de Vendedores - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<style>
/* ========== FOUNDATION ========== */
.content-area { background: var(--bg-main) !important; }

.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.8rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em; margin-bottom: 0.2rem; }
.page-header p { color: var(--text-muted); font-size: 0.95rem; font-weight: 500; }

/* ========== PANEL ========== */
.panel {
    background: var(--surface); border: 1px solid var(--border); border-radius: 18px;
    padding: 1.5rem; transition: box-shadow 0.3s;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}

/* ========== TABLE ========== */
.vendedores-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.vendedores-table th { 
    background: rgba(0,0,0,0.02); padding: 1rem 1.25rem; font-size: 0.75rem; 
    text-transform: uppercase; color: var(--text-muted); font-weight: 700; 
    border-bottom: 1px solid var(--border); letter-spacing: 0.05em;
    text-align: left;
}
.vendedores-table th:first-child { border-top-left-radius: 12px; }
.vendedores-table th:last-child { border-top-right-radius: 12px; }
.vendedores-table td { 
    padding: 1.25rem; border-bottom: 1px solid var(--border); color: var(--text-main); 
    font-size: 0.95rem; font-weight: 500; vertical-align: middle;
}
.vendedores-table tr:last-child td { border-bottom: none; }
.vendedores-table tr:hover td { background: rgba(0,0,0,0.015); }

/* ========== SELLER AVATAR ========== */
.seller-av-wrapper { display: flex; align-items: center; gap: 1rem; }
.seller-av { 
    width: 40px; height: 40px; border-radius: 12px; 
    background: linear-gradient(135deg, #002D62, #001a3d); color: #fff; 
    display: flex; align-items: center; justify-content: center; 
    font-weight: 800; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(0,45,98,0.2);
}
.seller-name { font-weight: 700; color: var(--text-main); }
.seller-email { font-size: 0.8rem; color: var(--text-muted); }

/* ========== BADGES ========== */
.badge-stat { 
    padding: 0.35rem 0.75rem; border-radius: 8px; font-weight: 700; font-size: 0.85rem; 
    display: inline-block;
}
.badge-won { background: rgba(16,185,129,0.1); color: #10b981; }
.badge-lost { background: rgba(239,68,68,0.1); color: #ef4444; }
.badge-open { background: rgba(99,102,241,0.1); color: #6366f1; }
.badge-amount { font-weight: 800; font-size: 1.1rem; color: #10b981; }

.progress-container { width: 100%; background: var(--border); border-radius: 8px; height: 6px; overflow: hidden; margin-top: 0.4rem; }
.progress-bar { height: 100%; background: #10b981; border-radius: 8px; }

</style>

<div class="page-header">
    <h1>Control de Vendedores</h1>
    <p>Supervisión del rendimiento comercial y estado del pipeline de cada miembro del equipo.</p>
</div>

<div class="panel" style="padding: 0; overflow: hidden;">
    <div class="table-responsive">
        <table class="vendedores-table">
            <thead>
                <tr>
                    <th>Vendedor</th>
                    <th>Deals Activos</th>
                    <th>Ganados</th>
                    <th>Perdidos</th>
                    <th>Tasa de Cierre</th>
                    <th style="text-align: right;">Ingresos Generados</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vendedores)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            No hay vendedores registrados en el sistema.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($vendedores as $v): 
                        $totalClosed = $v->won_deals + $v->lost_deals;
                        $winRate = $totalClosed > 0 ? round(($v->won_deals / $totalClosed) * 100) : 0;
                        $initials = strtoupper(substr($v->name, 0, 1));
                    ?>
                    <tr>
                        <td>
                            <div class="seller-av-wrapper">
                                <div class="seller-av"><?= $initials ?></div>
                                <div>
                                    <div class="seller-name"><?= htmlspecialchars($v->name) ?></div>
                                    <div class="seller-email"><?= htmlspecialchars($v->email) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-stat badge-open"><?= $v->open_deals ?> activos</span>
                        </td>
                        <td>
                            <span class="badge-stat badge-won"><?= $v->won_deals ?></span>
                        </td>
                        <td>
                            <span class="badge-stat badge-lost"><?= $v->lost_deals ?></span>
                        </td>
                        <td style="width: 150px;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;"><?= $winRate ?>%</div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?= $winRate ?>%;"></div>
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div class="badge-amount">$<?= number_format((float)$v->won_amount, 2, '.', ',') ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
