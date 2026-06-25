<?php
$pageTitle = 'Emitir Factura - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Emitir Nueva Factura</h1>
        <p>Registra una factura manual vinculada a un proyecto o cliente.</p>
    </div>
    <a href="<?= url('/finanzas/facturas') ?>" class="btn" style="background: var(--border); color: var(--text-main);"><i class="fas fa-arrow-left"></i> Volver</a>
</div>

<div class="card">
    <div style="padding: 2rem;">
        <form action="<?= url('/finanzas') ?>" method="POST" enctype="multipart/form-data">
            
            <?php if ($prefillDeal): ?>
                <div class="alert alert-success" style="margin-bottom: 2rem;">
                    <i class="fas fa-link" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong style="display: block;">Vinculando factura al Contrato Ganado:</strong>
                        <?= htmlspecialchars($prefillDeal->name) ?> 
                        ($<?= number_format((float)$prefillDeal->amount, 2) ?>)
                    </div>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>Folio de Factura (Obligatorio) <span style="color:#ef4444">*</span></label>
                    <input type="text" name="invoice_number" class="form-control" required placeholder="Ej. FAC-2023-001">
                </div>
                <div class="form-group">
                    <label>Referencia Externa (SAT / ERP)</label>
                    <input type="text" name="reference" class="form-control" placeholder="UUID u otro identificador">
                </div>
            </div>

            <h3 style="font-size: 1.1rem; color: var(--primary); margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);">Relaciones</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>Contrato / Proyecto</label>
                    <select name="deal_id" class="form-control">
                        <option value="">Selecciona un proyecto...</option>
                        <?php foreach ($deals as $deal): ?>
                            <option value="<?= $deal->id ?>" <?= ($prefillDealId == $deal->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($deal->name) ?> (<?= htmlspecialchars($deal->account_name ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cliente (Empresa a Facturar)</label>
                    <select name="account_id" class="form-control">
                        <option value="">Selecciona cliente...</option>
                        <?php foreach ($accounts as $acc): ?>
                            <option value="<?= $acc->id ?>" <?= ($prefillDeal && $prefillDeal->account_id == $acc->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($acc->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Contacto de Facturación</label>
                    <select name="contact_id" class="form-control">
                        <option value="">Selecciona contacto...</option>
                        <?php foreach ($contacts as $contact): ?>
                            <option value="<?= $contact->id ?>">
                                <?= htmlspecialchars($contact->first_name . ' ' . $contact->last_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h3 style="font-size: 1.1rem; color: var(--primary); margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);">Montos y Fechas</h3>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>Subtotal</label>
                    <input type="number" step="0.01" name="subtotal" id="input_subtotal" class="form-control" value="<?= $prefillDeal ? $prefillDeal->amount : '0.00' ?>">
                </div>
                <div class="form-group">
                    <label>Impuestos (IVA)</label>
                    <input type="number" step="0.01" name="tax_amount" id="input_tax" class="form-control" value="0.00">
                </div>
                <div class="form-group">
                    <label>Total Factura <span style="color:#ef4444">*</span></label>
                    <input type="number" step="0.01" name="total" id="input_total" class="form-control" required value="<?= $prefillDeal ? $prefillDeal->amount : '0.00' ?>">
                </div>
                <div class="form-group">
                    <label>Moneda</label>
                    <select name="currency_code" class="form-control">
                        <option value="MXN" selected>MXN - Pesos Mexicanos</option>
                        <option value="USD">USD - Dólares Americanos</option>
                        <option value="EUR">EUR - Euros</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group">
                    <label>Fecha de Emisión <span style="color:#ef4444">*</span></label>
                    <input type="date" name="issue_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Fecha de Vencimiento <span style="color:#ef4444">*</span></label>
                    <input type="date" name="due_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Estatus Inicial</label>
                    <select name="status" class="form-control">
                        <option value="borrador">Borrador (Sin emitir)</option>
                        <option value="emitida" selected>Emitida (Pendiente de pago)</option>
                        <option value="pagada">Pagada (Cobro total recibido)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Archivo PDF (Opcional)</label>
                    <input type="file" name="pdf" class="form-control" accept="application/pdf" style="padding: 0.6rem 1rem;">
                </div>
            </div>
            
            <div class="form-group">
                <label>Notas Adicionales</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Condiciones de pago, cuenta bancaria, comentarios..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <a href="<?= url('/finanzas/facturas') ?>" class="btn" style="background: var(--border); color: var(--text-main);">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Factura</button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-calcular impuestos y total
document.getElementById('input_subtotal').addEventListener('input', calculateTotal);
document.getElementById('input_tax').addEventListener('input', calculateTotal);

function calculateTotal() {
    const subtotal = parseFloat(document.getElementById('input_subtotal').value) || 0;
    const tax = parseFloat(document.getElementById('input_tax').value) || 0;
    document.getElementById('input_total').value = (subtotal + tax).toFixed(2);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
