<?php
$pageTitle = 'Auditoría de Vendedores - Einsur Global CRM';
require __DIR__ . '/../../layouts/header.php';

// Totales globales
$globalVentas = array_sum(array_map(fn($s) => (float)$s->monto_ventas, $sellers));
$globalFacturado = array_sum(array_map(fn($s) => (float)$s->total_facturado, $sellers));
$globalCobrado = array_sum(array_map(fn($s) => (float)$s->total_cobrado, $sellers));
$globalPorCobrar = array_sum(array_map(fn($s) => (float)$s->total_por_cobrar, $sellers));
$globalVencido = array_sum(array_map(fn($s) => (float)$s->total_vencido, $sellers));
$globalSinFactura = array_sum(array_map(fn($s) => (int)$s->deals_sin_factura, $sellers));
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user-tie" style="color: var(--primary);"></i> Auditoría Financiera por Vendedor</h1>
        <p>Panorama completo: Ventas cerradas → Facturación → Cobranza → Morosidad por cada ejecutivo.</p>
    </div>
</div>

<!-- ═══════════ KPIs GLOBALES ═══════════ -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.2rem; margin-bottom: 2rem;">
    <div class="card" style="padding: 1.5rem; border-left: 4px solid #3b82f6;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.5px;">Ventas Cerradas</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #3b82f6; margin-top: 0.3rem;">$<?= number_format($globalVentas, 2) ?></div>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;"><?= count($sellers) ?> vendedores activos</div>
    </div>
    <div class="card" style="padding: 1.5rem; border-left: 4px solid #8b5cf6;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.5px;">Total Facturado</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #8b5cf6; margin-top: 0.3rem;">$<?= number_format($globalFacturado, 2) ?></div>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
            <?php $pctFact = $globalVentas > 0 ? round($globalFacturado / $globalVentas * 100) : 0; ?>
            <?= $pctFact ?>% de las ventas facturadas
        </div>
    </div>
    <div class="card" style="padding: 1.5rem; border-left: 4px solid #10b981;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.5px;">Total Cobrado</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #10b981; margin-top: 0.3rem;">$<?= number_format($globalCobrado, 2) ?></div>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">
            <?php $pctCob = $globalFacturado > 0 ? round($globalCobrado / $globalFacturado * 100) : 0; ?>
            <?= $pctCob ?>% de efectividad de cobro
        </div>
    </div>
    <div class="card" style="padding: 1.5rem; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.5px;">Por Cobrar</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #f59e0b; margin-top: 0.3rem;">$<?= number_format($globalPorCobrar, 2) ?></div>
    </div>
    <div class="card" style="padding: 1.5rem; border-left: 4px solid #ef4444;">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; letter-spacing: 0.5px;">Cartera Vencida</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #ef4444; margin-top: 0.3rem;">$<?= number_format($globalVencido, 2) ?></div>
    </div>
    <?php if ($globalSinFactura > 0): ?>
    <div class="card" style="padding: 1.5rem; border-left: 4px solid #f97316; background: rgba(249, 115, 22, 0.05);">
        <div style="font-size: 0.75rem; text-transform: uppercase; color: #f97316; font-weight: 700; letter-spacing: 0.5px;">⚠ Ventas Sin Factura</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: #f97316; margin-top: 0.3rem;"><?= $globalSinFactura ?></div>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem;">Deals ganados pendientes de facturar</div>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════ TABLA DE VENDEDORES ═══════════ -->
<div class="card">
    <div style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin: 0;">
            <i class="fas fa-chart-bar" style="color: var(--primary); margin-right: 0.5rem;"></i>Desglose por Ejecutivo de Ventas
        </h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Ejecutivo</th>
                    <th style="text-align: center;">Deals</th>
                    <th style="text-align: right;">Ventas Cerradas</th>
                    <th style="text-align: center;">Facturas</th>
                    <th style="text-align: right;">Facturado</th>
                    <th style="text-align: right; color: #10b981;">Cobrado</th>
                    <th style="text-align: right; color: #f59e0b;">Por Cobrar</th>
                    <th style="text-align: right; color: #ef4444;">Vencido</th>
                    <th style="text-align: center;">Efectividad</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sellers)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i class="fas fa-users-slash" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem; display: block;"></i>
                            No hay vendedores con actividad comercial o financiera registrada.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sellers as $seller): 
                        $pctCobranza = $seller->total_facturado > 0 ? round($seller->total_cobrado / $seller->total_facturado * 100) : 0;
                        $pctFacturacion = $seller->monto_ventas > 0 ? round($seller->total_facturado / $seller->monto_ventas * 100) : 0;
                        
                        // Color de la barra de efectividad
                        if ($pctCobranza >= 80) $barColor = '#10b981';
                        elseif ($pctCobranza >= 50) $barColor = '#f59e0b';
                        else $barColor = '#ef4444';
                    ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.8rem;">
                                    <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--accent) 0%, #3b82f6 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem; flex-shrink: 0;">
                                        <?= strtoupper(substr($seller->seller_name, 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);"><?= htmlspecialchars($seller->seller_name) ?></div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($seller->seller_email) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 0.2rem;">
                                    <span style="font-weight: 700; font-size: 1.1rem; color: var(--text-main);"><?= $seller->deals_ganados ?></span>
                                    <span style="font-size: 0.7rem; color: var(--text-muted);">ganados</span>
                                    <?php if ($seller->deals_sin_factura > 0): ?>
                                        <span style="background: #fef3c7; color: #b45309; padding: 0.1rem 0.4rem; border-radius: 8px; font-size: 0.65rem; font-weight: 700;">
                                            ⚠ <?= $seller->deals_sin_factura ?> sin fact.
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 1rem; color: #3b82f6;">$<?= number_format($seller->monto_ventas, 2) ?></strong>
                            </td>
                            <td style="text-align: center;">
                                <span style="background: var(--primary-light); padding: 0.2rem 0.6rem; border-radius: 12px; font-weight: 700; font-size: 0.85rem; color: var(--text-main);">
                                    <?= $seller->total_invoices ?>
                                </span>
                                <?php if ($seller->facturas_vencidas > 0): ?>
                                    <div style="font-size: 0.7rem; color: #ef4444; font-weight: 600; margin-top: 0.2rem;">
                                        <?= $seller->facturas_vencidas ?> vencida<?= $seller->facturas_vencidas > 1 ? 's' : '' ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <strong style="font-size: 1rem;">$<?= number_format($seller->total_facturado, 2) ?></strong>
                                <?php if ($seller->monto_ventas > 0): ?>
                                    <div style="font-size: 0.7rem; color: <?= $pctFacturacion >= 100 ? '#10b981' : '#f59e0b' ?>; font-weight: 600;"><?= $pctFacturacion ?>% facturado</div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #10b981;">
                                $<?= number_format($seller->total_cobrado, 2) ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: <?= $seller->total_por_cobrar > 0 ? '#f59e0b' : 'var(--text-muted)' ?>;">
                                $<?= number_format($seller->total_por_cobrar, 2) ?>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: <?= $seller->total_vencido > 0 ? '#ef4444' : 'var(--text-muted)' ?>;">
                                $<?= number_format($seller->total_vencido, 2) ?>
                            </td>
                            <td style="text-align: center; min-width: 100px;">
                                <div style="font-size: 0.85rem; font-weight: 800; color: <?= $barColor ?>;"><?= $pctCobranza ?>%</div>
                                <div style="width: 100%; height: 6px; background: var(--border); border-radius: 3px; margin-top: 0.3rem; overflow: hidden;">
                                    <div style="width: <?= min($pctCobranza, 100) ?>%; height: 100%; background: <?= $barColor ?>; border-radius: 3px; transition: width 0.5s ease;"></div>
                                </div>
                                <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 0.2rem;">cobro</div>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= url('/finanzas/ceo/auditoria-detalle?seller_id=' . $seller->seller_id) ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                    <i class="fas fa-search-dollar"></i> Auditar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
