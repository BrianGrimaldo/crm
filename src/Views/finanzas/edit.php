<?php
$pageTitle = 'Detalle de Factura - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';

$saldo = $invoice->total - $invoice->amount_paid;
$progress = $invoice->total > 0 ? min(100, ($invoice->amount_paid / $invoice->total) * 100) : 0;

$statusColors = [
    'borrador' => ['bg' => 'rgba(100,116,139,0.1)', 'color' => '#64748b', 'icon' => 'fas fa-pen'],
    'emitida' => ['bg' => 'rgba(59,130,246,0.1)', 'color' => '#3b82f6', 'icon' => 'fas fa-paper-plane'],
    'parcial' => ['bg' => 'rgba(245,158,11,0.1)', 'color' => '#f59e0b', 'icon' => 'fas fa-adjust'],
    'vencida' => ['bg' => 'rgba(239,68,68,0.1)', 'color' => '#ef4444', 'icon' => 'fas fa-exclamation-triangle'],
    'pagada' => ['bg' => 'rgba(16,185,129,0.1)', 'color' => '#10b981', 'icon' => 'fas fa-check-circle'],
    'cancelada' => ['bg' => 'rgba(100,116,139,0.1)', 'color' => '#94a3b8', 'icon' => 'fas fa-ban'],
];
$sc = $statusColors[$invoice->status] ?? $statusColors['borrador'];

$roleStr = strtolower(str_replace(['-', ' '], '', $_SESSION['user_role'] ?? ''));
$isCobranza = strpos($roleStr, 'cobranza') !== false || strpos($roleStr, 'collection') !== false || strpos($roleStr, 'cobrador') !== false;
$backUrl = $isCobranza ? url('/finanzas/cobranza') : url('/finanzas/facturas');
?>

<style>
.inv-hero { background: var(--surface); border-radius: 20px; padding: 2rem; border: 1px solid rgba(0,0,0,0.04); box-shadow: var(--shadow-md); margin-bottom: 2rem; position: relative; overflow: hidden; }
.inv-hero::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, <?= $sc['color'] ?>, <?= $sc['color'] ?>88); }
.inv-hero-top { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; }
.inv-hero-folio { font-size: 1.8rem; font-weight: 900; letter-spacing: -0.03em; color: var(--text-main); display: flex; align-items: center; gap: 0.8rem; }
.inv-hero-badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.4rem 1rem; border-radius: 12px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; background: <?= $sc['bg'] ?>; color: <?= $sc['color'] ?>; }
.inv-hero-actions { display: flex; gap: 0.5rem; }
.inv-hero-actions .btn { border-radius: 12px; padding: 0.5rem 1rem; font-size: 0.85rem; }
.inv-meta-strip { display: flex; gap: 2rem; margin-top: 1.5rem; flex-wrap: wrap; }
.inv-meta-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: var(--text-muted); }
.inv-meta-item i { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; background: var(--bg-main); color: var(--primary); }

.inv-kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 2rem; }
@media(max-width:768px) { .inv-kpi-row { grid-template-columns: 1fr; } }
.inv-kpi { background: var(--surface); border-radius: 16px; padding: 1.5rem; border: 1px solid rgba(0,0,0,0.04); box-shadow: var(--shadow-sm); text-align: center; transition: all 0.3s; position: relative; overflow: hidden; }
.inv-kpi:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
.inv-kpi-val { font-size: 1.6rem; font-weight: 900; letter-spacing: -0.03em; line-height: 1.2; }
.inv-kpi-lbl { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); margin-top: 0.3rem; }

.inv-progress-bar { width: 100%; height: 8px; background: var(--bg-main); border-radius: 99px; overflow: hidden; margin-top: 1rem; }
.inv-progress-fill { height: 100%; border-radius: 99px; transition: width 1s cubic-bezier(0.4,0,0.2,1); background: linear-gradient(90deg, #10b981, #059669); }

.inv-grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 1.5rem; }
@media(max-width:1000px) { .inv-grid { grid-template-columns: 1fr; } }

.inv-panel { background: var(--surface); border-radius: 20px; border: 1px solid rgba(0,0,0,0.04); box-shadow: var(--shadow-md); overflow: hidden; margin-bottom: 1.5rem; }
.inv-panel-head { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.04); display: flex; align-items: center; gap: 0.6rem; }
.inv-panel-head-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; }
.inv-panel-head h3 { margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main); }
.inv-panel-body { padding: 1.5rem; }

.inv-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.inv-form-grid label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); margin-bottom: 0.3rem; display: block; }

.pay-row { display: flex; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.03); transition: background 0.2s; }
.pay-row:hover { background: var(--bg-main); }
.pay-row:last-child { border-bottom: none; }
.pay-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; margin-right: 1rem; flex-shrink: 0; background: rgba(16,185,129,0.1); color: #10b981; }

.rev-row { display: flex; align-items: center; padding: 0.8rem 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.03); transition: background 0.2s; }
.rev-row:hover { background: var(--bg-main); }
.rev-row:last-child { border-bottom: none; }
.rev-icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; margin-right: 0.8rem; flex-shrink: 0; background: rgba(168,85,247,0.1); color: #a855f7; }

.inv-empty { padding: 2.5rem; text-align: center; color: var(--text-muted); }
.inv-empty i { font-size: 2rem; margin-bottom: 0.5rem; display: block; opacity: 0.3; }
</style>

<!-- HERO -->
<div class="inv-hero">
    <div class="inv-hero-top">
        <div>
            <div class="inv-hero-folio">
                #<?= htmlspecialchars($invoice->invoice_number) ?>
                <span class="inv-hero-badge"><i class="<?= $sc['icon'] ?>"></i> <?= ucfirst($invoice->status) ?></span>
            </div>
            <div class="inv-meta-strip">
                <div class="inv-meta-item"><i class="fas fa-user-tie"></i> <?= htmlspecialchars($invoice->account_name ?? 'Sin Cliente') ?></div>
                <div class="inv-meta-item"><i class="fas fa-calendar"></i> Emitida: <?= date('d M Y', strtotime($invoice->issue_date)) ?></div>
                <div class="inv-meta-item"><i class="fas fa-clock"></i> Vence: <?= date('d M Y', strtotime($invoice->due_date)) ?></div>
                <?php if (!empty($invoice->pdf_path)): ?>
                    <a href="<?= url('/' . $invoice->pdf_path) ?>" target="_blank" class="inv-meta-item" style="color: #ef4444; text-decoration: none;"><i style="background:rgba(239,68,68,0.1);color:#ef4444;"><span class="fas fa-file-pdf"></span></i> Ver PDF Vigente</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="inv-hero-actions">
            <a href="<?= $backUrl ?>" class="btn" style="background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border);"><i class="fas fa-arrow-left"></i> Volver</a>
            <?php if (\App\Core\Permission::has('finance', 'delete')): ?>
            <form action="<?= url('/finanzas/eliminar') ?>" method="POST" onsubmit="return confirm('¿Eliminar esta factura permanentemente?');" style="display:inline;">
                <input type="hidden" name="id" value="<?= $invoice->id ?>">
                <button type="submit" class="btn" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.15);"><i class="fas fa-trash"></i></button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- KPI ROW -->
<div class="inv-kpi-row">
    <div class="inv-kpi" style="border-top: 3px solid var(--text-main);">
        <div class="inv-kpi-val" style="color: var(--text-main);">$<?= number_format($invoice->total, 2) ?></div>
        <div class="inv-kpi-lbl">Total Facturado</div>
    </div>
    <div class="inv-kpi" style="border-top: 3px solid #10b981;">
        <div class="inv-kpi-val" style="color: #10b981;">$<?= number_format($invoice->amount_paid, 2) ?></div>
        <div class="inv-kpi-lbl">Total Cobrado</div>
        <div class="inv-progress-bar"><div class="inv-progress-fill" style="width: <?= $progress ?>%;"></div></div>
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.3rem;"><?= number_format($progress, 1) ?>% cobrado</div>
    </div>
    <div class="inv-kpi" style="border-top: 3px solid <?= $saldo > 0 ? '#ef4444' : '#10b981' ?>;">
        <div class="inv-kpi-val" style="color: <?= $saldo > 0 ? '#ef4444' : '#10b981' ?>;">$<?= number_format($saldo, 2) ?></div>
        <div class="inv-kpi-lbl">Saldo Pendiente</div>
    </div>
</div>

<!-- MAIN GRID -->
<div class="inv-grid">
    <!-- LEFT: FORM -->
    <div>
        <div class="inv-panel">
            <div class="inv-panel-head">
                <div class="inv-panel-head-icon" style="background:rgba(59,130,246,0.1);color:#3b82f6;"><i class="fas fa-file-alt"></i></div>
                <h3>Información General</h3>
            </div>
            <div class="inv-panel-body">
                <form action="<?= url('/finanzas/actualizar') ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $invoice->id ?>">
                    
                    <div class="inv-form-grid" style="margin-bottom: 1.25rem;">
                        <div><label>Folio</label><input type="text" name="invoice_number" class="form-control" value="<?= htmlspecialchars($invoice->invoice_number) ?>" required></div>
                        <div><label>Referencia</label><input type="text" name="reference" class="form-control" value="<?= htmlspecialchars($invoice->reference ?? '') ?>"></div>
                    </div>
                    <div class="inv-form-grid" style="margin-bottom: 1.25rem;">
                        <div>
                            <label>Contrato / Proyecto</label>
                            <select name="deal_id" class="form-control">
                                <option value="">Ninguno</option>
                                <?php foreach ($deals as $deal): ?>
                                    <option value="<?= $deal->id ?>" <?= $invoice->deal_id == $deal->id ? 'selected' : '' ?>><?= htmlspecialchars($deal->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Cliente</label>
                            <select name="account_id" class="form-control">
                                <option value="">Ninguno</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?= $acc->id ?>" <?= $invoice->account_id == $acc->id ? 'selected' : '' ?>><?= htmlspecialchars($acc->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="inv-form-grid" style="margin-bottom: 1.25rem;">
                        <div><label>Total (<?= htmlspecialchars($invoice->currency_code) ?>)</label><input type="number" step="0.01" name="total" class="form-control" value="<?= $invoice->total ?>" required></div>
                        <div>
                            <label>Estatus</label>
                            <select name="status" class="form-control" <?= $invoice->status == 'pagada' ? 'disabled' : '' ?>>
                                <option value="borrador" <?= $invoice->status == 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                <option value="emitida" <?= $invoice->status == 'emitida' ? 'selected' : '' ?>>Emitida</option>
                                <option value="parcial" <?= $invoice->status == 'parcial' ? 'selected' : '' ?>>Parcial</option>
                                <option value="vencida" <?= $invoice->status == 'vencida' ? 'selected' : '' ?>>Vencida</option>
                                <option value="cancelada" <?= $invoice->status == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                <?php if ($invoice->status == 'pagada'): ?><option value="pagada" selected>Pagada</option><?php endif; ?>
                            </select>
                            <?php if ($invoice->status == 'pagada'): ?><input type="hidden" name="status" value="pagada"><?php endif; ?>
                        </div>
                    </div>
                    <div class="inv-form-grid" style="margin-bottom: 1.25rem;">
                        <div><label>Fecha Emisión</label><input type="date" name="issue_date" class="form-control" value="<?= $invoice->issue_date ?>" required></div>
                        <div><label>Fecha Vencimiento</label><input type="date" name="due_date" class="form-control" value="<?= $invoice->due_date ?>" required></div>
                    </div>
                    <div class="inv-form-grid" style="margin-bottom: 1.5rem;">
                        <div><label>Notas</label><textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($invoice->notes ?? '') ?></textarea></div>
                        <div>
                            <label>Reemplazar PDF</label>
                            <input type="file" name="pdf" class="form-control" accept="application/pdf" style="padding: 0.5rem;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; border-radius: 12px; padding: 0.7rem;"><i class="fas fa-save"></i> Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <!-- RIGHT: PAYMENTS + REVISIONS -->
    <div>
        <!-- REGISTER PAYMENT -->
        <?php if ($saldo > 0 && $invoice->status != 'cancelada' && $invoice->status != 'borrador'): ?>
        <div class="inv-panel" style="margin-bottom: 1.5rem; border-top: 3px solid #10b981;">
            <div class="inv-panel-head">
                <div class="inv-panel-head-icon" style="background:rgba(16,185,129,0.1);color:#10b981;"><i class="fas fa-hand-holding-usd"></i></div>
                <h3>Registrar Pago</h3>
            </div>
            <div class="inv-panel-body">
                <form action="<?= url('/finanzas/pago') ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="invoice_id" value="<?= $invoice->id ?>">
                    <div class="inv-form-grid" style="margin-bottom: 1rem;">
                        <div><label>Monto</label><input type="number" step="0.01" name="amount" class="form-control" max="<?= $saldo ?>" value="<?= $saldo ?>" required></div>
                        <div>
                            <label>Método</label>
                            <select name="payment_method" class="form-control">
                                <option value="transferencia">Transferencia</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="cheque">Cheque</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>
                    </div>
                    <div class="inv-form-grid" style="margin-bottom: 1rem;">
                        <div><label>Fecha de Pago</label><input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                        <div><label>Ref. Bancaria</label><input type="text" name="reference" class="form-control" placeholder="Opcional"></div>
                    </div>
                    <div style="border-top: 1px dashed var(--border-color); padding-top: 1rem; margin-bottom: 1rem;">
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); margin-bottom: 0.6rem;"><i class="fas fa-exchange-alt"></i> Reemplazo de Folio (Opcional)</div>
                        <div class="inv-form-grid">
                            <div><label>Nuevo Folio</label><input type="text" name="new_invoice_number" class="form-control" placeholder="Ej. FAC-100-P1"></div>
                            <div><label>PDF Comprobante</label><input type="file" name="invoice_pdf" class="form-control" accept="application/pdf" style="padding: 0.35rem;"></div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; background: #10b981; border-color: #10b981; border-radius: 12px; padding: 0.7rem;"><i class="fas fa-check-circle"></i> Aplicar Pago</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- PAYMENT HISTORY -->
        <div class="inv-panel" style="margin-bottom: 1.5rem;">
            <div class="inv-panel-head">
                <div class="inv-panel-head-icon" style="background:rgba(16,185,129,0.1);color:#10b981;"><i class="fas fa-receipt"></i></div>
                <h3>Historial de Pagos</h3>
                <span style="margin-left: auto; font-size: 0.75rem; font-weight: 800; background: rgba(16,185,129,0.1); color: #10b981; padding: 0.2rem 0.6rem; border-radius: 8px;"><?= count($payments) ?></span>
            </div>
            <?php if (empty($payments)): ?>
                <div class="inv-empty"><i class="fas fa-receipt"></i>No hay pagos registrados.</div>
            <?php else: ?>
                <?php foreach ($payments as $pay): ?>
                    <div class="pay-row">
                        <div class="pay-icon"><i class="fas fa-arrow-down"></i></div>
                        <div style="flex: 1;">
                            <div style="font-weight: 800; font-size: 1.05rem; color: #10b981;">+$<?= number_format($pay->amount, 2) ?></div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);"><i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($pay->payment_date)) ?></div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.8rem; text-transform: capitalize; font-weight: 600;"><?= htmlspecialchars($pay->payment_method) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Ref: <?= htmlspecialchars($pay->reference ?: 'N/A') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- REVISION HISTORY -->
        <div class="inv-panel">
            <div class="inv-panel-head">
                <div class="inv-panel-head-icon" style="background:rgba(168,85,247,0.1);color:#a855f7;"><i class="fas fa-history"></i></div>
                <h3>Historial de Folios / PDFs</h3>
                <span style="margin-left: auto; font-size: 0.75rem; font-weight: 800; background: rgba(168,85,247,0.1); color: #a855f7; padding: 0.2rem 0.6rem; border-radius: 8px;"><?= count($revisions) ?></span>
            </div>
            <?php if (empty($revisions)): ?>
                <div class="inv-empty"><i class="fas fa-history"></i>No hay versiones anteriores.</div>
            <?php else: ?>
                <?php foreach ($revisions as $rev): ?>
                    <div class="rev-row">
                        <div class="rev-icon"><i class="fas fa-file-invoice"></i></div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($rev->invoice_number) ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><i class="fas fa-clock"></i> <?= date('d M Y, H:i', strtotime($rev->replaced_at)) ?> — <?= htmlspecialchars($rev->notes ?? '') ?></div>
                        </div>
                        <?php if (!empty($rev->pdf_path)): ?>
                            <a href="<?= url('/' . $rev->pdf_path) ?>" target="_blank" class="btn btn-sm" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.15); border-radius: 10px; font-size: 0.8rem;"><i class="fas fa-file-pdf"></i> PDF</a>
                        <?php else: ?>
                            <span style="font-size: 0.75rem; color: var(--text-muted); opacity: 0.5;">Sin PDF</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
