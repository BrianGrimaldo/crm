<?php
$pageTitle = 'Detalle de Auditoría - Einsur Global CRM';
require __DIR__ . '/../../layouts/header.php';
$sellerName = $sellerInfo->name ?? 'Vendedor';
$pctCobranza = $totalFacturado > 0 ? round($totalCobrado / $totalFacturado * 100) : 0;
?>

<div class="page-header">
    <div>
        <a href="<?= url('/finanzas/ceo/auditoria') ?>" style="font-size: 0.85rem; color: var(--text-muted); text-decoration: none; margin-bottom: 0.5rem; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Volver a Vendedores
        </a>
        <h1><i class="fas fa-user-tie" style="color: var(--primary);"></i> <?= htmlspecialchars($sellerName) ?></h1>
        <p>Auditoría completa: Ventas, facturación, cobranza y pendientes.</p>
    </div>
</div>

<!-- ═══════════ KPIs DEL VENDEDOR ═══════════ -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Total Facturado</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #8b5cf6; margin-top: 0.2rem;">$<?= number_format($totalFacturado, 2) ?></div>
        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= count($invoices) ?> facturas</div>
    </div>
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #10b981;">
        <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Total Cobrado</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #10b981; margin-top: 0.2rem;">$<?= number_format($totalCobrado, 2) ?></div>
        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= $pctCobranza ?>% de efectividad</div>
    </div>
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Por Cobrar</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #f59e0b; margin-top: 0.2rem;">$<?= number_format($totalPorCobrar, 2) ?></div>
    </div>
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #ef4444;">
        <div style="font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Cartera Vencida</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #ef4444; margin-top: 0.2rem;">$<?= number_format($totalVencido, 2) ?></div>
    </div>
    <?php if (!empty($dealsWithoutInvoice)): ?>
    <div class="card" style="padding: 1.2rem; border-left: 4px solid #f97316; background: rgba(249, 115, 22, 0.05);">
        <div style="font-size: 0.7rem; text-transform: uppercase; color: #f97316; font-weight: 700;">⚠ Sin Factura</div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #f97316; margin-top: 0.2rem;">$<?= number_format($montoSinFacturar, 2) ?></div>
        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= count($dealsWithoutInvoice) ?> deals pendientes</div>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════ DEALS SIN FACTURA ═══════════ -->
<?php if (!empty($dealsWithoutInvoice)): ?>
<div class="card" style="margin-bottom: 2rem; border: 1px solid rgba(249, 115, 22, 0.3);">
    <div style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border); background: rgba(249, 115, 22, 0.05);">
        <h3 style="font-size: 1rem; font-weight: 700; color: #f97316; margin: 0;">
            <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>Ventas Ganadas Sin Factura Asociada
        </h3>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.3rem;">Estos deals fueron marcados como "Ganado" pero no tienen ninguna factura vinculada.</p>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Proyecto / Deal</th>
                    <th>Cliente</th>
                    <th style="text-align: right;">Monto</th>
                    <th>Fecha Cierre</th>
                    <th style="text-align: center;">Días Sin Facturar</th>
                    <th style="text-align: right;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dealsWithoutInvoice as $deal): ?>
                    <tr>
                        <td><strong style="color: var(--text-main);"><?= htmlspecialchars($deal->deal_name) ?></strong></td>
                        <td style="color: var(--text-muted);"><?= htmlspecialchars($deal->account_name ?? 'Sin empresa') ?></td>
                        <td style="text-align: right; font-weight: 700; color: #3b82f6;">$<?= number_format($deal->amount, 2) ?></td>
                        <td style="color: var(--text-muted);"><?= $deal->actual_close_date ? date('d M Y', strtotime($deal->actual_close_date)) : '—' ?></td>
                        <td style="text-align: center;">
                            <?php 
                            $days = (int)$deal->days_since_won;
                            $urgColor = $days > 30 ? '#ef4444' : ($days > 14 ? '#f59e0b' : '#10b981');
                            ?>
                            <span style="background: <?= $urgColor ?>20; color: <?= $urgColor ?>; padding: 0.2rem 0.6rem; border-radius: 12px; font-weight: 700; font-size: 0.8rem;">
                                <?= $days ?> días
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="<?= url('/finanzas/crear?deal_id=' . $deal->id) ?>" class="btn btn-primary" style="padding: 0.3rem 0.7rem; font-size: 0.75rem;">
                                <i class="fas fa-file-invoice"></i> Crear Factura
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ═══════════ FACTURAS DEL VENDEDOR ═══════════ -->
<div class="card">
    <div style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin: 0;">
            <i class="fas fa-file-invoice-dollar" style="color: var(--primary); margin-right: 0.5rem;"></i>Facturas Emitidas (<?= count($invoices) ?>)
        </h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Cliente / Proyecto</th>
                    <th>Emisión / Vencimiento</th>
                    <th style="text-align: right;">Importe Total</th>
                    <th style="text-align: right;">Pagado</th>
                    <th style="text-align: right;">Saldo</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i class="fas fa-file-invoice" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem; display: block;"></i>
                            Este vendedor no tiene facturas registradas.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): 
                        $saldo = $inv->total - $inv->amount_paid;
                        $statusMap = [
                            'emitida'   => ['bg' => '#dbeafe', 'color' => '#1d4ed8'],
                            'parcial'   => ['bg' => '#fef3c7', 'color' => '#b45309'],
                            'vencida'   => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
                            'pagada'    => ['bg' => '#dcfce7', 'color' => '#15803d'],
                            'cancelada' => ['bg' => '#f1f5f9', 'color' => '#64748b'],
                        ];
                        $st = $statusMap[$inv->status] ?? ['bg' => '#f1f5f9', 'color' => '#64748b'];
                    ?>
                        <tr>
                            <td>
                                <strong style="color: var(--text-main);">#<?= htmlspecialchars($inv->invoice_number) ?></strong>
                                <?php if (!empty($inv->pdf_path)): ?>
                                    <a href="<?= url('/' . $inv->pdf_path) ?>" target="_blank" style="color: #ef4444; font-size: 0.8rem; margin-left: 0.3rem;" title="Ver PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($inv->account_name ?? 'Sin Empresa') ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
                                    <i class="fas fa-handshake" style="margin-right: 3px;"></i> <?= htmlspecialchars($inv->deal_name ?? 'Sin Proyecto') ?>
                                </div>
                                <?php if (!empty($inv->contact_name)): ?>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><i class="fas fa-user" style="margin-right: 3px;"></i> <?= htmlspecialchars($inv->contact_name) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="color: var(--text-main);"><i class="far fa-calendar-plus" style="color: var(--text-muted); margin-right: 4px;"></i> <?= date('d M Y', strtotime($inv->issue_date)) ?></div>
                                <?php $dueColor = ($inv->status === 'vencida') ? '#ef4444' : 'var(--text-muted)'; ?>
                                <div style="font-size: 0.8rem; color: <?= $dueColor ?>; margin-top: 0.2rem; font-weight: 600;">
                                    <i class="far fa-calendar-times" style="margin-right: 4px;"></i> <?= date('d M Y', strtotime($inv->due_date)) ?>
                                    <?php if ($inv->status === 'vencida' && $inv->days_overdue > 0): ?>
                                        <span style="font-size: 0.7rem;">(<?= $inv->days_overdue ?> días)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 1.05rem;">$<?= number_format($inv->total, 2) ?></strong>
                                <div style="font-size: 0.7rem; color: var(--text-muted);"><?= htmlspecialchars($inv->currency_code) ?></div>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;">
                                $<?= number_format($inv->amount_paid, 2) ?>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 1.05rem; color: <?= $saldo > 0 ? '#ef4444' : '#10b981' ?>;">$<?= number_format($saldo, 2) ?></strong>
                            </td>
                            <td>
                                <span style="display: inline-block; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; background: <?= $st['bg'] ?>; color: <?= $st['color'] ?>;">
                                    <?= htmlspecialchars(ucfirst($inv->status)) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
