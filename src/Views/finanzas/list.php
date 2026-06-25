<?php
$pageTitle = 'Facturas - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Módulo de Facturación</h1>
        <p>Control de emisiones, cobranza y estatus de pagos.</p>
    </div>
    <?php if (\App\Core\Permission::has('finance', 'create')): ?>
        <a href="<?= url('/finanzas/crear') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Factura</a>
    <?php endif; ?>
</div>

<div class="card" style="padding: 1.5rem; margin-bottom: 2rem;">
    <form method="GET" action="<?= url('/finanzas/facturas') ?>" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 250px;">
            <label style="display:block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.4rem;">Búsqueda</label>
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 1.2rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                <input type="text" name="search" class="form-control" placeholder="Folio, Proyecto, Cliente..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="padding-left: 3rem;">
            </div>
        </div>
        <div style="width: 200px;">
            <label style="display:block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.4rem;">Estatus</label>
            <select name="status" class="form-control">
                <option value="">Todos los estatus</option>
                <option value="borrador" <?= ($_GET['status'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                <option value="emitida" <?= ($_GET['status'] ?? '') === 'emitida' ? 'selected' : '' ?>>Emitida (Por Cobrar)</option>
                <option value="parcial" <?= ($_GET['status'] ?? '') === 'parcial' ? 'selected' : '' ?>>Cobro Parcial</option>
                <option value="vencida" <?= ($_GET['status'] ?? '') === 'vencida' ? 'selected' : '' ?>>Vencida</option>
                <option value="pagada" <?= ($_GET['status'] ?? '') === 'pagada' ? 'selected' : '' ?>>Pagada Total</option>
                <option value="cancelada" <?= ($_GET['status'] ?? '') === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="padding: 0.85rem 1.5rem;"><i class="fas fa-filter"></i> Filtrar</button>
        <?php if (!empty($_GET['search']) || !empty($_GET['status'])): ?>
            <a href="<?= url('/finanzas/facturas') ?>" class="btn" style="background: var(--border); color: var(--text-muted);"><i class="fas fa-times"></i> Limpiar</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Cliente / Proyecto</th>
                    <th>Emisión / Vencimiento</th>
                    <th>Importe Total</th>
                    <th>Saldo Pendiente</th>
                    <th>Estatus</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i class="fas fa-file-invoice-dollar" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i><br>
                            No hay facturas registradas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): 
                        $saldo = $inv->total - $inv->amount_paid;
                        
                        $statusClass = 'status-borrador';
                        if ($inv->status === 'emitida') $statusClass = 'status-emitida';
                        if ($inv->status === 'parcial') $statusClass = 'status-parcial';
                        if ($inv->status === 'vencida') $statusClass = 'status-vencida';
                        if ($inv->status === 'pagada') $statusClass = 'status-pagada';
                        if ($inv->status === 'cancelada') $statusClass = 'status-cancelada';
                    ?>
                        <tr>
                            <td>
                                <strong>#<?= htmlspecialchars($inv->invoice_number) ?></strong>
                                <?php if($inv->source === 'webhook'): ?>
                                    <span style="font-size: 0.65rem; background: #e0e7ff; color: #4f46e5; padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.3rem;"><i class="fas fa-robot"></i> API</span>
                                <?php endif; ?>
                                <?php if(!empty($inv->pdf_path)): ?>
                                    <a href="<?= url('/' . $inv->pdf_path) ?>" target="_blank" title="Descargar PDF" style="margin-left: 0.5rem; color: #ef4444; font-size: 1.1rem;"><i class="fas fa-file-pdf"></i></a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($inv->account_name ?? 'Sin Empresa') ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;"><i class="fas fa-handshake"></i> <?= htmlspecialchars($inv->deal_name ?? 'Sin Proyecto') ?></div>
                            </td>
                            <td>
                                <div><i class="far fa-calendar-plus" style="color: #94a3b8; margin-right: 4px;"></i> <?= date('d M Y', strtotime($inv->issue_date)) ?></div>
                                <?php 
                                    $dueColor = ($inv->status === 'vencida' || ($inv->days_until_due !== null && $inv->days_until_due < 0 && $inv->status !== 'pagada')) ? '#ef4444' : 'var(--text-muted)';
                                ?>
                                <div style="font-size: 0.8rem; color: <?= $dueColor ?>; margin-top: 0.2rem; font-weight: 600;">
                                    <i class="far fa-calendar-times" style="margin-right: 4px;"></i> <?= date('d M Y', strtotime($inv->due_date)) ?>
                                </div>
                            </td>
                            <td>
                                <strong style="font-size: 1.05rem;">$<?= number_format($inv->total, 2) ?> <?= htmlspecialchars($inv->currency_code) ?></strong>
                            </td>
                            <td>
                                <strong style="font-size: 1.05rem; color: <?= $saldo > 0 ? '#ef4444' : '#10b981' ?>;">$<?= number_format($saldo, 2) ?></strong>
                            </td>
                            <td>
                                <span class="status-badge <?= $statusClass ?>" style="display: inline-block; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                                    <?= htmlspecialchars(ucfirst($inv->status)) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <?php if (\App\Core\Permission::has('finance', 'update')): ?>
                                    <a href="<?= url('/finanzas/editar?id=' . $inv->id) ?>" class="btn" style="padding: 0.4rem 0.8rem; background: var(--border); color: var(--primary);"><i class="fas fa-edit"></i> Editar / Pago</a>
                                <?php else: ?>
                                    <a href="<?= url('/finanzas/editar?id=' . $inv->id) ?>" class="btn" style="padding: 0.4rem 0.8rem; background: var(--border); color: var(--primary);"><i class="fas fa-eye"></i> Ver</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.status-badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
.status-emitida { background: #dbeafe; color: #1d4ed8; }
.status-parcial { background: #fef3c7; color: #b45309; }
.status-vencida { background: #fee2e2; color: #b91c1c; }
.status-pagada { background: #dcfce7; color: #15803d; }
.status-borrador { background: var(--border); color: var(--text-main); }
.status-cancelada { background: var(--border); color: var(--text-main); }
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
