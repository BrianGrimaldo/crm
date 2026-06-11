<?php
$pageTitle = 'Contactos - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Directorio de Contactos</h1>
        <p>Gestiona los clientes y prospectos de tu empresa.</p>
    </div>
    <?php if (\App\Core\Permission::has('contacts', 'create')): ?>
    <a href="/crm_einsurglobal/public/contactos/create" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Nuevo Contacto
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; gap: 1rem;">
        <form action="/crm_einsurglobal/public/contactos" method="GET" style="display: flex; gap: 1rem; flex: 1;">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o correo..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <select name="type" class="form-control" style="max-width: 200px;">
                <option value="">Todos los Tipos</option>
                <option value="Prospecto" <?= ($_GET['type'] ?? '') == 'Prospecto' ? 'selected' : '' ?>>Prospecto</option>
                <option value="Cliente" <?= ($_GET['type'] ?? '') == 'Cliente' ? 'selected' : '' ?>>Cliente</option>
                <option value="Otro" <?= ($_GET['type'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
            </select>
            <button type="submit" class="btn btn-primary">Buscar</button>
            <?php if (!empty($_GET['search']) || !empty($_GET['type'])): ?>
                <a href="/crm_einsurglobal/public/contactos" class="btn" style="background: var(--border); color: var(--text-main);">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table style="min-width: 1500px;">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Tipo</th>
                    <th>Gerente de Cuenta</th>
                    <th>OrganizaciÃ³n</th>
                    <th>PosiciÃ³n</th>
                    <th>Email</th>
                    <th>LinkedIn</th>
                    <th>TelÃ©fono</th>
                    <th>PaÃ­s</th>
                    <th>Ciudad</th>
                    <th>CÃ³digo Postal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 3rem;">
                            <p style="color: var(--text-muted);">No se encontraron contactos.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($contact->first_name . ' ' . $contact->last_name) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($contact->type ?? 'Prospecto') ?></td>
                            <td><?= htmlspecialchars($contact->owner_name ?? 'Sin Asignar') ?></td>
                            <td><?= htmlspecialchars($contact->account_name ?? '-') ?></td>
                            <td><?= htmlspecialchars($contact->job_title ?? '-') ?></td>
                            <td><?= htmlspecialchars($contact->email ?? '-') ?></td>
                            <td>
                                <?php if (!empty($contact->linkedin)): ?>
                                    <a href="<?= htmlspecialchars($contact->linkedin) ?>" target="_blank" style="color: var(--primary); text-decoration: none;">Ver Perfil</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($contact->phone ?? '-') ?></td>
                            <td><?= htmlspecialchars($contact->country ?? '-') ?></td>
                            <td><?= htmlspecialchars($contact->city ?? '-') ?></td>
                            <td><?= htmlspecialchars($contact->postal_code ?? '-') ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <?php if (\App\Core\Permission::has('contacts', 'update')): ?>
                                        <a href="/crm_einsurglobal/public/contactos/edit?id=<?= $contact->id ?>" style="color: var(--primary-hover); text-decoration: none; font-weight: 600;">Editar</a>
                                    <?php endif; ?>
                                    <?php if (\App\Core\Permission::has('contacts', 'delete')): ?>
                                        <form action="/crm_einsurglobal/public/contactos/delete" method="POST" onsubmit="return confirm('Â¿EstÃ¡s seguro de que deseas eliminar este contacto?');" style="display:inline; margin:0;">
                                            <input type="hidden" name="id" value="<?= $contact->id ?>">
                                            <button type="submit" style="background: none; border: none; color: var(--error); cursor: pointer; font-weight: 600; font-family: inherit; font-size: inherit;">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
