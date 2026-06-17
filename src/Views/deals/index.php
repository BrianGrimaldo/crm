<?php
$pageTitle = 'Oportunidades - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Lista de Oportunidades</h1>
        <p>Vista detallada de todas tus ventas en progreso.</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="/oportunidades/pipeline" class="btn btn-outline" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin-right: 0.5rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            Vista Kanban
        </a>
        <?php if (\App\Core\Permission::has('deals', 'create')): ?>
        <a href="/oportunidades/create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Nueva Oportunidad
        </a>
        <?php endif; ?>
    </div>
</div>

<style>
    .deals-table-container {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
        background: var(--surface);
    }
    
    .deals-table {
        min-width: 1200px;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .deals-table th {
        background: rgba(0, 0, 0, 0.02);
        padding: 1rem 1.25rem;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 700;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
    }

    .deals-table td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        color: var(--text-main);
        font-size: 0.95rem;
        vertical-align: middle;
        transition: background-color 0.2s;
    }

    .deals-table tr:hover td {
        background: rgba(0, 0, 0, 0.01);
    }

    .deals-table tr:last-child td {
        border-bottom: none;
    }

    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-abierto { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
    .status-ganado { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .status-perdido { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

    .stage-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.75rem;
        display: inline-block;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .deal-name {
        font-weight: 700;
        color: var(--text-main);
        text-decoration: none;
        transition: color 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .deal-name:hover {
        color: var(--primary-hover);
    }

    .deal-amount {
        font-weight: 700;
        color: #10b981;
        background: rgba(16, 185, 129, 0.05);
        padding: 0.3rem 0.75rem;
        border-radius: 6px;
        display: inline-block;
    }

    .action-btn {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .action-edit {
        color: var(--text-main);
        background: rgba(0,0,0,0.04);
    }
    .action-edit:hover {
        background: var(--primary);
        color: var(--primary-text);
    }

    .action-delete {
        color: #ef4444;
        background: rgba(239, 68, 68, 0.1);
        border: none;
        cursor: pointer;
        font-family: inherit;
    }
    .action-delete:hover {
        background: #ef4444;
        color: white;
    }
</style>

<div class="card" style="box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border-radius: 16px;">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; gap: 1rem; background: var(--surface); border-top-left-radius: 16px; border-top-right-radius: 16px;">
        <form action="/oportunidades" method="GET" style="display: flex; gap: 1rem; flex: 1; flex-wrap: wrap;">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o contacto..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="background: rgba(0,0,0,0.02); min-width: 200px;">
            <select name="status" class="form-control" style="width: auto; min-width: 150px; flex-grow: 1; max-width: 100%; background: rgba(0,0,0,0.02);">
                <option value="">Todos los Estados</option>
                <option value="Abierto" <?= ($_GET['status'] ?? '') === 'Abierto' ? 'selected' : '' ?>>Abierto</option>
                <option value="Ganado" <?= ($_GET['status'] ?? '') === 'Ganado' ? 'selected' : '' ?>>Ganado</option>
                <option value="Perdido" <?= ($_GET['status'] ?? '') === 'Perdido' ? 'selected' : '' ?>>Perdido</option>
            </select>
            <button type="submit" class="btn btn-primary" style="border-radius: 8px; flex-grow: 1; justify-content: center;">Buscar</button>
            <?php if (!empty($_GET['search']) || !empty($_GET['status'])): ?>
                <a href="/oportunidades" class="btn" style="background: var(--border); color: var(--text-main); flex-grow: 1; justify-content: center;">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive deals-table-container" style="border: none; border-radius: 0 0 16px 16px;">
        <table class="deals-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Encargado</th>
                    <th>Organización</th>
                    <th>Estado</th>
                    <th>Etapa</th>
                    <th>Razón de pérdida</th>
                    <th>Fuente</th>
                    <th>Valor</th>
                    <th>Probabilidad</th>
                    <th>Fecha Esperada</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deals)): ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 4rem 2rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--text-muted); opacity: 0.5; margin-bottom: 1rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                            <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 500;">No se encontraron oportunidades.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($deals as $deal): ?>
                        <?php
                            $statusClass = 'status-abierto';
                            if (($deal->status ?? '') === 'Ganado') $statusClass = 'status-ganado';
                            if (($deal->status ?? '') === 'Perdido') $statusClass = 'status-perdido';
                        ?>
                        <tr>
                            <td>
                                <a href="/oportunidades/edit?id=<?= $deal->id ?>" class="deal-name">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    <?= htmlspecialchars($deal->name) ?>
                                </a>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--border); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">
                                        <?= strtoupper(substr($deal->owner_name ?? 'S', 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($deal->owner_name ?? 'Sin asignar') ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($deal->account_name ?? '-') ?></td>
                            <td>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= htmlspecialchars($deal->status ?? 'Abierto') ?>
                                </span>
                            </td>
                            <td>
                                <span class="stage-badge" style="background: <?= htmlspecialchars($deal->stage_color ?? '#e2e8f0') ?>25; color: <?= htmlspecialchars($deal->stage_color ?? '#64748b') ?>; border: 1px solid <?= htmlspecialchars($deal->stage_color ?? '#e2e8f0') ?>50;">
                                    <?= htmlspecialchars($deal->stage_name ?? 'Desconocida') ?>
                                </span>
                            </td>
                            <td><span style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($deal->lost_reason ?? '-') ?></span></td>
                            <td><span style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($deal->source ?? '-') ?></span></td>
                            <td>
                                <span class="deal-amount">
                                    $<?= number_format((float)$deal->amount, 2) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($deal->probability): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="flex: 1; background: var(--border); height: 6px; border-radius: 3px; overflow: hidden; width: 50px;">
                                            <div style="height: 100%; width: <?= $deal->probability ?>%; background: <?= $deal->probability >= 70 ? '#10b981' : ($deal->probability >= 40 ? '#f59e0b' : '#ef4444') ?>;"></div>
                                        </div>
                                        <span style="font-size: 0.85rem; font-weight: 600;"><?= $deal->probability ?>%</span>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size: 0.9rem;">
                                    <?= $deal->expected_close_date ? date('d/m/Y', strtotime($deal->expected_close_date)) : '-' ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <?php if (\App\Core\Permission::has('deals', 'update')): ?>
                                        <a href="/oportunidades/edit?id=<?= $deal->id ?>" class="action-btn action-edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            Editar
                                        </a>
                                    <?php endif; ?>
                                    <?php if (\App\Core\Permission::has('deals', 'delete')): ?>
                                        <form action="/oportunidades/delete" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta oportunidad?');" style="display:inline; margin:0;">
                                            <input type="hidden" name="id" value="<?= $deal->id ?>">
                                            <button type="submit" class="action-btn action-delete" title="Eliminar">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
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
