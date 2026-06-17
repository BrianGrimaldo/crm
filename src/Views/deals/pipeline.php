<?php
$pageTitle = 'Pipeline de Ventas - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Pipeline de Ventas</h1>
        <p>Gestiona tus oportunidades en cada etapa del embudo.</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="/oportunidades" class="btn btn-outline" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin-right: 0.5rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
            Vista Lista
        </a>
        <a href="/oportunidades/create" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Nueva Oportunidad
        </a>
    </div>
</div>

<style>
    .kanban-board {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        padding-bottom: 1rem;
        flex: 1;
        min-height: 0;
        align-items: flex-start;
        padding-top: 1rem;
    }
    
    .kanban-board::-webkit-scrollbar {
        height: 8px;
    }
    .kanban-board::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.02);
        border-radius: 4px;
    }
    .kanban-board::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 4px;
    }

    .kanban-column {
        flex: 1;
        min-width: 220px;
        background: var(--bg-main);
        border-radius: 16px;
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        max-height: 100%;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
        transition: transform 0.3s ease;
    }

    .kanban-column:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .kanban-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
        position: relative;
        overflow: hidden;
        background: var(--surface);
    }

    .kanban-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--stage-color);
        opacity: 0.8;
    }

    .kanban-header h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.2rem;
    }
    
    .kanban-header .total-amount {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    .kanban-header .count {
        background: rgba(0,0,0,0.04);
        color: var(--text-main);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        border: 1px solid var(--border);
    }

    .kanban-cards {
        padding: 1rem 1.25rem;
        overflow-y: auto;
        flex: 1;
        min-height: 150px;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .kanban-cards::-webkit-scrollbar {
        width: 6px;
    }
    .kanban-cards::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 4px;
    }

    .kanban-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.25rem;
        cursor: grab;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        flex-shrink: 0;
    }

    .kanban-card:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04);
        border-color: var(--stage-color);
    }

    .kanban-card:active {
        cursor: grabbing;
        transform: scale(0.98);
    }

    .card-title {
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 0.75rem;
        font-size: 1.05rem;
        line-height: 1.3;
    }

    .card-amount {
        color: #10b981;
        background: rgba(16, 185, 129, 0.1);
        display: inline-block;
        padding: 0.3rem 0.75rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 1rem;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--border);
        padding-top: 0.75rem;
        margin-top: 0.5rem;
    }

    .card-contact {
        font-size: 0.85rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: 500;
    }

    .kanban-cards.drag-over {
        background: rgba(0, 0, 0, 0.02);
        border: 2px dashed var(--stage-color, var(--primary));
        border-radius: 12px;
    }
</style>

<div class="kanban-board">
    <?php foreach ($stages as $stage): ?>
        <?php
            $stageDeals = $dealsByStage[$stage->id] ?? [];
            $stageSummary = null;
            foreach ($summary as $sum) {
                if ($sum->stage_id == $stage->id) {
                    $stageSummary = $sum;
                    break;
                }
            }
            $totalAmount = $stageSummary ? $stageSummary->total_amount : 0;
            $count = count($stageDeals);
        ?>
        <div class="kanban-column" style="--stage-color: <?= htmlspecialchars($stage->color) ?>;">
            <div class="kanban-header">
                <div>
                    <h3><?= htmlspecialchars($stage->name) ?></h3>
                    <div class="total-amount">
                        $<?= number_format((float)$totalAmount, 2) ?>
                    </div>
                </div>
                <span class="count"><?= $count ?></span>
            </div>            <div class="kanban-cards" data-stage-id="<?= $stage->id ?>" ondragover="allowDrop(event)" ondrop="drop(event)" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)">
                <?php foreach ($stageDeals as $deal): ?>
                    <div class="kanban-card" id="deal-<?= $deal->id ?>" draggable="true" ondragstart="drag(event)" data-deal-id="<?= $deal->id ?>" onclick="window.location.href='/oportunidades/edit?id=<?= $deal->id ?>'">
                        <div class="card-title"><?= htmlspecialchars($deal->name) ?></div>
                        <div class="card-amount">$<?= number_format((float)$deal->amount, 2) ?> <?= htmlspecialchars($deal->currency_code) ?></div>
                        
                        <!-- Quick Stage Selector -->
                        <div style="margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;" onclick="event.stopPropagation()">
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Etapa:</span>
                            <select onchange="changeStageInline(event, <?= $deal->id ?>)" style="font-size: 0.8rem; padding: 0.2rem 0.4rem; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); cursor: pointer; outline: none; width: 70%;">
                                <?php foreach ($stages as $stg): ?>
                                    <option value="<?= $stg->id ?>" <?= $deal->stage_id == $stg->id ? 'selected' : '' ?>><?= htmlspecialchars($stg->name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="card-footer">
                            <?php if ($deal->contact_name): ?>
                                <div class="card-contact">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <?= htmlspecialchars(trim($deal->contact_name)) ?>
                                </div>
                            <?php else: ?>
                                <div class="card-contact" style="opacity: 0.5;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    Sin contacto
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($deal->probability): ?>
                                <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); background: var(--bg-main); padding: 0.2rem 0.5rem; border-radius: 4px;">
                                    <?= $deal->probability ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function allowDrop(ev) {
        ev.preventDefault();
    }

    function dragEnter(ev) {
        ev.preventDefault();
        let dropzone = ev.target.closest('.kanban-cards');
        if (dropzone) {
            dropzone.classList.add('drag-over');
        }
    }

    function dragLeave(ev) {
        let dropzone = ev.target.closest('.kanban-cards');
        if (dropzone) {
            dropzone.classList.remove('drag-over');
        }
    }

    function drag(ev) {
        ev.dataTransfer.setData("deal_id", ev.target.dataset.dealId);
        ev.dataTransfer.setData("element_id", ev.target.id);
    }

    async function drop(ev) {
        ev.preventDefault();
        let dropzone = ev.target.closest('.kanban-cards');
        if (dropzone) {
            dropzone.classList.remove('drag-over');
            
            let elementId = ev.dataTransfer.getData("element_id");
            let dealId = ev.dataTransfer.getData("deal_id");
            let stageId = dropzone.dataset.stageId;
            
            let card = document.getElementById(elementId);
            dropzone.appendChild(card);
            
            await sendMoveRequest(dealId, stageId);
        }
    }

    async function changeStageInline(event, dealId) {
        let stageId = event.target.value;
        await sendMoveRequest(dealId, stageId);
    }

    async function sendMoveRequest(dealId, stageId) {
        try {
            let response = await fetch('/api/oportunidades/move-stage', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ deal_id: dealId, stage_id: stageId })
            });
            let result = await response.json();
            if (result.status !== 'success') {
                alert('Error al mover: ' + result.message);
                window.location.reload();
            } else {
                window.location.reload(); 
            }
        } catch (err) {
            alert('Error de conexión');
            window.location.reload();
        }
    }
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
