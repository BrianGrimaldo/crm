<?php
$pageTitle = 'Portal de Cobranza - Einsur Global CRM';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-wallet" style="color: var(--primary);"></i> Portal de Cobranza</h1>
        <p>Registro de pagos y abonos mediante búsqueda estricta.</p>
    </div>
    <div style="text-align: right; background: #fff; padding: 0.8rem 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border-color);">
        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Ventas Totales (KPI)</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #10b981;">
            $<?= number_format($totalSales ?? 0, 2) ?>
        </div>
    </div>
</div>

<!-- Buscador Estricto -->
<div class="card" style="padding: 2rem; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; text-align: center;">
    <i class="fas fa-search-dollar" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
    <h3 style="margin-bottom: 1rem;">Buscar Factura para Cobro</h3>
    <form method="GET" action="<?= url('/finanzas/cobranza') ?>" style="display: flex; gap: 0.5rem; justify-content: center;">
        <input type="text" name="invoice_number" class="form-control" placeholder="Ingrese el Folio Exacto..." required style="max-width: 300px; font-size: 1.1rem; text-align: center; font-weight: bold; letter-spacing: 1px;" value="<?= htmlspecialchars($_GET['invoice_number'] ?? '') ?>">
        <button type="submit" class="btn btn-primary" style="padding: 0 1.5rem;"><i class="fas fa-search"></i> Buscar</button>
    </form>
</div>

<!-- Resultado de Búsqueda -->
<?php if (empty($invoicesGrouped)): ?>
    <div class="card" style="text-align: center; padding: 3rem;">
        <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #ef4444; margin-bottom: 1rem;"></i>
        <h3 style="color: var(--text-main);">No hay facturas registradas</h3>
        <?php if (!empty($_GET['invoice_number'])): ?>
            <p style="color: var(--text-muted);">No existe ninguna factura con el folio "<strong><?= htmlspecialchars($_GET['invoice_number']) ?></strong>".</p>
            <a href="<?= url('/finanzas/cobranza') ?>" class="btn" style="margin-top: 1rem; background: var(--border);">Limpiar Búsqueda</a>
        <?php else: ?>
            <p style="color: var(--text-muted);">Aún no hay facturas en el sistema para gestionar.</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    
    <?php if (!empty($_GET['invoice_number'])): ?>
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <a href="<?= url('/finanzas/cobranza') ?>" class="btn btn-sm" style="background: var(--border); color: var(--text-main);"><i class="fas fa-times"></i> Limpiar Filtro</a>
        </div>
    <?php endif; ?>

    <?php foreach ($invoicesGrouped as $empresa => $facturas): ?>
        <h2 style="margin-top: 2.5rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; color: var(--text-main); display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-building" style="color: var(--primary);"></i> <?= htmlspecialchars($empresa) ?>
            <span class="status-badge" style="background: var(--bg-main); border: 1px solid var(--border); color: var(--text-muted);"><?= count($facturas) ?> Factura(s)</span>
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 1.5rem;">
            <?php foreach ($facturas as $inv): 
                $saldo = $inv->total - $inv->amount_paid;
                
                $statusClass = 'status-borrador';
                if ($inv->status === 'emitida') $statusClass = 'status-emitida';
                if ($inv->status === 'parcial') $statusClass = 'status-parcial';
                if ($inv->status === 'vencida') $statusClass = 'status-vencida';
                if ($inv->status === 'pagada') $statusClass = 'status-pagada';
            ?>
                <div class="card" style="padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <div>
                            <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem; font-size: 1.2rem;">
                                #<?= htmlspecialchars($inv->invoice_number) ?>
                                <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($inv->status)) ?></span>
                            </h3>
                            <div style="margin-top: 0.4rem; font-size: 0.95rem; color: var(--text-muted);">
                                <i class="fas fa-user-tie"></i> <?= htmlspecialchars($inv->account_name ?? 'Sin Cliente') ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Saldo Pendiente</div>
                            <div style="font-size: 1.4rem; font-weight: 800; color: <?= $saldo > 0 ? '#ef4444' : '#10b981' ?>;">
                                $<?= number_format($saldo, 2) ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($saldo > 0 && $inv->status !== 'cancelada'): ?>
                        <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                            <form method="POST" action="<?= url('/finanzas/pago') ?>" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="invoice_id" value="<?= $inv->id ?>">
                                <input type="hidden" name="redirect_to" value="/finanzas/cobranza<?= !empty($_GET['invoice_number']) ? '?invoice_number='.urlencode($_GET['invoice_number']) : '' ?>">
                                <input type="hidden" name="payment_date" value="<?= date('Y-m-d') ?>">
                                <input type="hidden" name="payment_method" value="transferencia">
                                
                                <input type="number" step="0.01" name="amount" class="form-control" style="width: 120px;" placeholder="Monto" max="<?= $saldo ?>" value="<?= $saldo ?>" required>
                                <button type="submit" class="btn btn-primary btn-sm" style="flex: 1;"><i class="fas fa-plus"></i> Abonar</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color); text-align: center; color: #10b981; font-weight: bold; font-size: 0.9rem;">
                            <i class="fas fa-check-circle"></i> Pagada
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<style>
.status-badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
.status-emitida { background: #dbeafe; color: #1d4ed8; }
.status-parcial { background: #fef3c7; color: #b45309; }
.status-vencida { background: #fee2e2; color: #b91c1c; }
.status-pagada { background: #dcfce7; color: #15803d; }
.status-borrador { background: var(--border); color: var(--text-main); }
</style>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
