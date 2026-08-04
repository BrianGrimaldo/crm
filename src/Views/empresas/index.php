<?php
$pageTitle = 'Empresas - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Empresas Registradas</h1>
        <p>Gestiona todas las empresas (tenants) del sistema.</p>
    </div>
    <a href="<?= url('/empresas/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Empresa
    </a>
</div>

<div class="card table-responsive">
    <table>
        <thead>
            <tr>
                <th>Logo</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Moneda</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empresas as $empresa): ?>
            <tr>
                <td>
                    <?php if ($empresa->logo_url): ?>
                        <img src="<?= htmlspecialchars($empresa->logo_url) ?>" alt="Logo" style="max-height: 40px; border-radius: 4px;">
                    <?php else: ?>
                        <div style="width: 40px; height: 40px; background: var(--border); border-radius: 4px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--text-muted);">
                            <?= strtoupper(substr($empresa->name, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($empresa->name) ?></strong>
                    <br>
                    <small style="color: var(--text-muted);"><?= htmlspecialchars($empresa->slug) ?></small>
                </td>
                <td><?= htmlspecialchars($empresa->email ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($empresa->phone ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($empresa->currency_code) ?></td>
                <td>
                    <?php if ($empresa->is_active): ?>
                        <span style="background: #dcfce7; color: #166534; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Activa</span>
                    <?php else: ?>
                        <span style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Inactiva</span>
                    <?php endif; ?>
                </td>
                <td style="display: flex; gap: 0.5rem; align-items: center;">
                    <a href="<?= url('/empresas/edit?id=' . $empresa->id) ?>" class="btn" style="padding: 0.5rem 1rem; background: var(--border); color: var(--text-main); border: 1px solid var(--text-muted);">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <button
                        type="button"
                        onclick="confirmarEliminar(<?= $empresa->id ?>, '<?= htmlspecialchars(addslashes($empresa->name)) ?>')"
                        class="btn"
                        style="padding: 0.5rem 1rem; background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;"
                        title="Eliminar empresa">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($empresas)): ?>
            <tr>
                <td colspan="7" style="text-align: center; color: var(--text-muted);">No hay empresas registradas.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal de confirmación de eliminación -->
<div id="modal-eliminar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div style="background: var(--bg-card, #1e1e2e); border-radius: 12px; padding: 2rem; max-width: 440px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.5); border: 1px solid var(--border, #333);">
        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem;">
            <div style="width:48px; height:48px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-exclamation-triangle" style="color:#dc2626; font-size:1.25rem;"></i>
            </div>
            <div>
                <h3 style="margin:0; font-size:1.1rem; color: var(--text-main, #fff);">Eliminar empresa</h3>
                <p style="margin:0.25rem 0 0; font-size:0.85rem; color: var(--text-muted, #aaa);">Esta acción no se puede deshacer</p>
            </div>
        </div>
        <p id="modal-mensaje" style="color: var(--text-main, #ccc); margin-bottom:1.5rem; font-size:0.95rem; line-height:1.5;"></p>
        <form id="form-eliminar" method="POST" action="<?= url('/empresas/delete') ?>">
            <input type="hidden" name="id" id="input-empresa-id" value="">
            <div style="display:flex; gap:0.75rem; justify-content:flex-end;">
                <button type="button" onclick="cerrarModal()" class="btn" style="padding:0.6rem 1.25rem; background:var(--border,#333); color:var(--text-main,#fff); border:1px solid var(--text-muted,#555);">
                    Cancelar
                </button>
                <button type="submit" class="btn" style="padding:0.6rem 1.25rem; background:#dc2626; color:#fff; border:none;">
                    <i class="fas fa-trash"></i> Sí, eliminar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmarEliminar(id, nombre) {
    document.getElementById('input-empresa-id').value = id;
    document.getElementById('modal-mensaje').textContent =
        '¿Estás seguro de que deseas eliminar la empresa "' + nombre + '"? Se eliminarán todos sus datos (usuarios, contactos, oportunidades, facturas, etc.) de forma permanente.';
    const modal = document.getElementById('modal-eliminar');
    modal.style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modal-eliminar').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
document.getElementById('modal-eliminar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
