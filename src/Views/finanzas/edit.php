<?php
$pageTitle = 'Detalle de Factura - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';

$saldo = $invoice->total - $invoice->amount_paid;
?>

<div class="page-header">
    <div>
        <h1>Factura #<?= htmlspecialchars($invoice->invoice_number) ?></h1>
        <p>Edición y control de pagos.</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="<?= url('/finanzas/facturas') ?>" class="btn" style="background: var(--border); color: var(--text-main);"><i class="fas fa-arrow-left"></i> Volver</a>
        
        <?php if (\App\Core\Permission::has('finance', 'delete')): ?>
        <form action="<?= url('/finanzas/eliminar') ?>" method="POST" onsubmit="return confirm('¿Eliminar esta factura?');" style="display:inline;">
            <input type="hidden" name="id" value="<?= $invoice->id ?>">
            <button type="submit" class="btn" style="background: #fee2e2; color: #b91c1c;"><i class="fas fa-trash"></i></button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="dash-grid g-2">
    <!-- PANEL IZQUIERDO: DETALLES DE FACTURA -->
    <div class="card">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.1rem; color: var(--primary);">Información General</h3>
            <span class="status-badge status-<?= $invoice->status ?>">
                <?= htmlspecialchars(ucfirst($invoice->status)) ?>
            </span>
        </div>
        <div style="padding: 2rem;">
            <form action="<?= url('/finanzas/actualizar') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $invoice->id ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label>Folio</label>
                        <input type="text" name="invoice_number" class="form-control" value="<?= htmlspecialchars($invoice->invoice_number) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Referencia</label>
                        <input type="text" name="reference" class="form-control" value="<?= htmlspecialchars($invoice->reference ?? '') ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label>Contrato / Proyecto</label>
                        <select name="deal_id" class="form-control">
                            <option value="">Ninguno</option>
                            <?php foreach ($deals as $deal): ?>
                                <option value="<?= $deal->id ?>" <?= $invoice->deal_id == $deal->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($deal->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cliente</label>
                        <select name="account_id" class="form-control">
                            <option value="">Ninguno</option>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?= $acc->id ?>" <?= $invoice->account_id == $acc->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($acc->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label>Total Factura (<?= htmlspecialchars($invoice->currency_code) ?>)</label>
                        <input type="number" step="0.01" name="total" class="form-control" value="<?= $invoice->total ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Estatus</label>
                        <select name="status" class="form-control" <?= $invoice->status == 'pagada' ? 'disabled' : '' ?>>
                            <option value="borrador" <?= $invoice->status == 'borrador' ? 'selected' : '' ?>>Borrador</option>
                            <option value="emitida" <?= $invoice->status == 'emitida' ? 'selected' : '' ?>>Emitida (Por Cobrar)</option>
                            <option value="parcial" <?= $invoice->status == 'parcial' ? 'selected' : '' ?>>Cobro Parcial</option>
                            <option value="vencida" <?= $invoice->status == 'vencida' ? 'selected' : '' ?>>Vencida</option>
                            <option value="cancelada" <?= $invoice->status == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            <?php if ($invoice->status == 'pagada'): ?>
                                <option value="pagada" selected>Pagada Total</option>
                            <?php endif; ?>
                        </select>
                        <?php if ($invoice->status == 'pagada'): ?>
                            <input type="hidden" name="status" value="pagada">
                            <small style="color: #10b981;">Factura completamente pagada.</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="form-group">
                        <label>Fecha Emisión</label>
                        <input type="date" name="issue_date" class="form-control" value="<?= $invoice->issue_date ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha Vencimiento</label>
                        <input type="date" name="due_date" class="form-control" value="<?= $invoice->due_date ?>" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label>Notas</label>
                        <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($invoice->notes ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Archivo PDF (Reemplazar / Subir nuevo)</label>
                        <input type="file" name="pdf" class="form-control" accept="application/pdf" style="padding: 0.6rem 1rem;">
                        <?php if (!empty($invoice->pdf_path)): ?>
                            <div style="margin-top: 0.8rem;">
                                <a href="<?= url('/' . $invoice->pdf_path) ?>" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.95rem; background: var(--border); padding: 0.4rem 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-file-pdf" style="color: #ef4444; font-size: 1.2rem;"></i> Ver PDF Actual
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- PANEL DERECHO: PAGOS Y SALDOS -->
    <div>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border);">
                <h3 style="margin: 0; font-size: 1.1rem; color: var(--primary);">Balance Financiero</h3>
            </div>
            <div style="padding: 1.5rem; display: flex; justify-content: space-around; text-align: center;">
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Total</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: var(--text-main);">$<?= number_format($invoice->total, 2) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Cobrado</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: #10b981;">$<?= number_format($invoice->amount_paid, 2) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Saldo Pendiente</div>
                    <div style="font-size: 1.6rem; font-weight: 800; color: <?= $saldo > 0 ? '#ef4444' : '#10b981' ?>;">$<?= number_format($saldo, 2) ?></div>
                </div>
            </div>
        </div>

        <?php if ($saldo > 0 && $invoice->status != 'cancelada' && $invoice->status != 'borrador'): ?>
        <div class="card" style="margin-bottom: 1.5rem;">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); background: var(--bg-main);">
                <h3 style="margin: 0; font-size: 1.1rem; color: var(--primary);"><i class="fas fa-plus-circle"></i> Registrar Pago</h3>
            </div>
            <div style="padding: 1.5rem;">
                <form action="<?= url('/finanzas/pago') ?>" method="POST">
                    <input type="hidden" name="invoice_id" value="<?= $invoice->id ?>">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Monto</label>
                            <input type="number" step="0.01" name="amount" class="form-control" max="<?= $saldo ?>" value="<?= $saldo ?>" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Método</label>
                            <select name="payment_method" class="form-control">
                                <option value="transferencia">Transferencia</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="cheque">Cheque</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Fecha de Pago</label>
                            <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Ref. Bancaria</label>
                            <input type="text" name="reference" class="form-control" placeholder="Opcional">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; background: #10b981; border-color: #10b981;"><i class="fas fa-hand-holding-usd"></i> Aplicar Pago</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border);">
                <h3 style="margin: 0; font-size: 1.1rem; color: var(--primary);">Historial de Pagos</h3>
            </div>
            <div style="padding: 0;">
                <?php if (empty($payments)): ?>
                    <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
                        No hay pagos registrados.
                    </div>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <?php foreach ($payments as $pay): ?>
                            <tr>
                                <td style="padding: 1rem; border-bottom: 1px solid var(--border);">
                                    <div style="font-weight: 700;">$<?= number_format($pay->amount, 2) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($pay->payment_date)) ?></div>
                                </td>
                                <td style="padding: 1rem; border-bottom: 1px solid var(--border); text-align: right;">
                                    <div style="font-size: 0.85rem; text-transform: capitalize;"><?= htmlspecialchars($pay->payment_method) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">Ref: <?= htmlspecialchars($pay->reference ?: 'N/A') ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<style>
.status-badge { display: inline-block; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }
.status-emitida { background: #dbeafe; color: #1d4ed8; }
.status-parcial { background: #fef3c7; color: #b45309; }
.status-vencida { background: #fee2e2; color: #b91c1c; }
.status-pagada { background: #dcfce7; color: #15803d; }
.status-borrador { background: var(--border); color: var(--text-main); }
.status-cancelada { background: var(--border); color: var(--text-main); }
</style>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
