<?php
$pageTitle = 'Empresas - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Empresas Registradas</h1>
        <p>Gestiona todas las empresas (tenants) del sistema.</p>
    </div>
    <a href="/crm_einsurglobal/public/empresas/create" class="btn btn-primary">
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
                        <div style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #64748b;">
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
                <td>
                    <a href="/crm_einsurglobal/public/empresas/edit?id=<?= $empresa->id ?>" class="btn" style="padding: 0.5rem 1rem; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">
                        <i class="fas fa-edit"></i> Editar
                    </a>
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

<?php require __DIR__ . '/../layouts/footer.php'; ?>
