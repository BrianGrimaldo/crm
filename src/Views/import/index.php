<?php
$pageTitle = 'Importación Masiva - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<style>
    /* ═══ PAGE ═══ */
    .import-hero {
        background: linear-gradient(135deg, var(--primary) 0%, #0a4a8e 60%, #1a6abf 100%);
        border-radius: 24px;
        padding: 2.5rem;
        color: #fff;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .import-hero::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 220px;
        height: 220px;
        background: rgba(110, 223, 246, 0.08);
        border-radius: 50%;
    }

    .import-hero h1 {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.4rem;
        letter-spacing: -0.04em;
    }

    .import-hero p {
        font-size: 1rem;
        opacity: 0.8;
        max-width: 640px;
    }

    /* ═══ TABS ═══ */
    .tab-bar {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .tab-btn {
        padding: 0.65rem 1.4rem;
        border-radius: 12px;
        border: 2px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        font-weight: 700;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.25s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .tab-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .tab-btn.active {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
        box-shadow: 0 4px 14px rgba(0, 45, 98, 0.25);
    }

    /* ═══ GRID ═══ */
    .import-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media(max-width:960px) {
        .import-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ═══ PANELS ═══ */
    .panel-card {
        background: var(--surface);
        border: 1px solid rgba(0, 0, 0, 0.04);
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: var(--shadow-md);
    }

    .panel-card-title {
        font-size: 1rem;
        font-weight: 800;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1.2rem;
    }

    .panel-card-title .icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }

    /* ═══ DROP ZONE ═══ */
    .drop-zone {
        border: 2.5px dashed var(--border);
        border-radius: 16px;
        padding: 3rem 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        background: var(--bg-main);
    }

    .drop-zone:hover,
    .drop-zone.dragover {
        border-color: var(--primary);
        background: var(--surface);
        box-shadow: 0 0 0 4px rgba(0, 45, 98, 0.06);
    }

    .drop-zone .dz-icon {
        font-size: 3rem;
        color: var(--border);
        margin-bottom: 1rem;
        transition: color 0.3s;
    }

    .drop-zone:hover .dz-icon {
        color: var(--primary);
    }

    .drop-zone h3 {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--text-main);
        margin-bottom: 0.3rem;
    }

    .drop-zone p {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .drop-zone input[type="file"] {
        display: none;
    }

    .file-selected {
        font-size: 0.82rem;
        color: #10b981;
        font-weight: 700;
        margin-top: 0.75rem;
    }

    /* ═══ COLUMNS REFERENCE ═══ */
    .col-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .col-badge {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.25rem 0.7rem;
        border-radius: 8px;
        background: var(--border);
        color: var(--text-muted);
        border: 1px solid var(--border);
    }

    .col-badge.required {
        background: rgba(0, 45, 98, 0.07);
        color: var(--primary);
        border-color: rgba(0, 45, 98, 0.15);
    }

    /* ═══ PROGRESS ═══ */
    .progress-wrap {
        margin-top: 1rem;
    }

    .progress-bar-bg {
        height: 8px;
        background: rgba(0, 0, 0, 0.06);
        border-radius: 8px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        border-radius: 8px;
        transition: width 0.6s ease;
        width: 0%;
    }

    .progress-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 0.4rem;
    }

    /* ═══ PREVIEW TABLE ═══ */
    .preview-wrap {
        overflow-x: auto;
        margin-top: 1.5rem;
        border-radius: 14px;
        border: 1px solid var(--border);
    }

    .preview-tbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .preview-tbl th {
        background: var(--bg-main);
        padding: 0.7rem 1rem;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        font-weight: 800;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .preview-tbl td {
        padding: 0.65rem 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        color: var(--text-main);
        white-space: nowrap;
    }

    .preview-tbl tr:last-child td {
        border-bottom: none;
    }

    .preview-tbl tr:hover td {
        background: #fafbff;
    }

    /* ═══ RESULT BOX ═══ */
    .result-box {
        border-radius: 16px;
        padding: 1.5rem;
        display: none;
        margin-top: 1.5rem;
    }

    .result-box.success {
        background: #dcfce7;
        border: 1px solid #bbf7d0;
    }

    .result-box.error {
        background: #fee2e2;
        border: 1px solid #fecaca;
    }

    .result-box h3 {
        font-weight: 800;
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }

    .result-box.success h3 {
        color: #166534;
    }

    .result-box.error h3 {
        color: #991b1b;
    }

    .result-stats {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 0.75rem;
    }

    .stat-pill {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 10px;
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
        font-weight: 700;
    }

    /* ═══ STEP GUIDE ═══ */
    .steps {
        display: flex;
        flex-direction: column;
        gap: 1.1rem;
    }

    .step {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }

    .step-num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        flex-shrink: 0;
        background: linear-gradient(135deg, var(--primary), #1a6abf);
        color: #fff;
        font-weight: 800;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0, 45, 98, 0.25);
    }

    .step-body h4 {
        font-weight: 700;
        font-size: 0.92rem;
        color: var(--text-main);
        margin-bottom: 0.2rem;
    }

    .step-body p {
        font-size: 0.82rem;
        color: var(--text-muted);
        line-height: 1.5;
    }

    /* ═══ BTNS ═══ */
    .btn-import {
        background: linear-gradient(135deg, var(--primary), #1a6abf);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 0.85rem 2rem;
        font-weight: 800;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 14px rgba(0, 45, 98, 0.25);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        width: 100%;
        justify-content: center;
        margin-top: 1rem;
    }

    .btn-import:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 45, 98, 0.3);
    }

    .btn-import:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .btn-commit {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 0.85rem 2rem;
        font-weight: 800;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
        display: none;
        align-items: center;
        gap: 0.6rem;
        width: 100%;
        justify-content: center;
        margin-top: 1rem;
    }

    .btn-commit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
    }

    .btn-commit.visible {
        display: flex;
    }
</style>

<!-- Hero -->
<div class="import-hero">
    <h1><i class="fas fa-file-import" style="margin-right:0.6rem;"></i> Importación Masiva de Datos</h1>
    <p>Carga tu lista de contactos u organizaciones desde un archivo CSV y el sistema los registrará automáticamente en
        tu cuenta.</p>
</div>

<!-- Selector de tipo -->
<div class="tab-bar">
    <button class="tab-btn active" id="tab-contacts" onclick="switchTab('contacts')">
        <i class="fas fa-address-card"></i> Contactos
    </button>
    <button class="tab-btn" id="tab-accounts" onclick="switchTab('accounts')">
        <i class="fas fa-building"></i> Organizaciones
    </button>
</div>

<div class="import-grid">
    <!-- ════ IZQUIERDA: Upload + Preview ════ -->
    <div>
        <div class="panel-card">
            <div class="panel-card-title">
                <div class="icon" style="background:rgba(99,102,241,.1);color:#6366f1;"><i class="fas fa-upload"></i>
                </div>
                Cargar archivo CSV
            </div>

            <!-- Drop zone -->
            <div class="drop-zone" id="dropZone" onclick="document.getElementById('csvFile').click()">
                <div class="dz-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                <h3>Arrastra tu archivo aquí</h3>
                <p>o haz clic para seleccionar · Solo archivos <strong>.csv</strong></p>
                <div class="file-selected" id="fileLabel" style="display:none;"></div>
                <input type="file" id="csvFile" accept=".csv,text/csv" onchange="onFileSelect(this)">
            </div>

            <!-- Progress -->
            <div class="progress-wrap" id="progressWrap" style="display:none;">
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" id="progressBar"></div>
                </div>
                <div class="progress-label" id="progressLabel">Analizando…</div>
            </div>

            <button class="btn-import" id="btnPreview" disabled onclick="uploadPreview()">
                <i class="fas fa-eye"></i> Previsualizar datos
            </button>

            <button class="btn-commit" id="btnCommit" onclick="commitImport()">
                <i class="fas fa-database"></i> Confirmar e importar todos
            </button>
        </div>

        <!-- Result box -->
        <div class="result-box" id="resultBox">
            <h3 id="resultTitle"></h3>
            <p id="resultMessage"></p>
            <div class="result-stats" id="resultStats"></div>
            <ul id="resultErrors" style="margin-top:0.75rem;font-size:0.8rem;color:#991b1b;padding-left:1.2rem;"></ul>
        </div>

        <!-- Preview table -->
        <div id="previewSection" style="display:none; margin-top:1.5rem;">
            <div class="panel-card">
                <div class="panel-card-title">
                    <div class="icon" style="background:rgba(16,185,129,.1);color:#10b981;"><i class="fas fa-table"></i>
                    </div>
                    Vista previa <span id="previewCount"
                        style="font-size:0.78rem;font-weight:600;color:var(--text-muted);margin-left:0.5rem;"></span>
                </div>
                <div class="preview-wrap">
                    <table class="preview-tbl">
                        <thead id="previewHead"></thead>
                        <tbody id="previewBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ════ DERECHA: Instrucciones + Columnas ════ -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        <!-- Steps -->
        <div class="panel-card">
            <div class="panel-card-title">
                <div class="icon" style="background:rgba(245,158,11,.1);color:#f59e0b;"><i class="fas fa-list-ol"></i>
                </div>
                ¿Cómo funciona?
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <h4>Descarga la plantilla</h4>
                        <p>Usa nuestra plantilla para asegurarte de que las columnas estén correctas. Excel abrirá el
                            CSV sin problemas.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <h4>Llena tus datos</h4>
                        <p>Agrega tus contactos u organizaciones. La columna marcada con * es obligatoria; el resto son
                            opcionales.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-body">
                        <h4>Guarda como CSV</h4>
                        <p>En Excel: <em>Guardar como → CSV UTF-8 (delimitado por comas)</em>. En Google Sheets:
                            <em>Archivo → Descargar → CSV</em>.
                        </p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">4</div>
                    <div class="step-body">
                        <h4>Previsualiza y confirma</h4>
                        <p>Sube el archivo, revisa la preview y presiona <strong>Confirmar e importar</strong>. ¡Listo!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Download buttons -->
            <div style="display:flex;flex-direction:column;gap:0.5rem;margin-top:1.5rem;">
                <a id="dlContacts" href="<?= url('/importar/plantilla?type=contacts') ?>" class="btn btn-primary"
                    style="justify-content:center;font-size:0.9rem;">
                    <i class="fas fa-download"></i> Plantilla de Contactos
                </a>
                <a id="dlAccounts" href="<?= url('/importar/plantilla?type=accounts') ?>"
                    style="display:none; justify-content:center;font-size:0.9rem;" class="btn btn-primary">
                    <i class="fas fa-download"></i> Plantilla de Organizaciones
                </a>
            </div>
        </div>

        <!-- Column reference -->
        <div class="panel-card">
            <div class="panel-card-title">
                <div class="icon" style="background:rgba(168,85,247,.1);color:#a855f7;"><i class="fas fa-columns"></i>
                </div>
                Columnas disponibles
            </div>

            <!-- Contacts columns -->
            <div id="colContacts">
                <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.75rem;">
                    <span
                        style="background:rgba(0,45,98,0.08);color:var(--primary);padding:0.15rem 0.5rem;border-radius:6px;font-weight:700;font-size:0.72rem;">*obligatorio</span>
                </p>
                <div class="col-grid">
                    <span class="col-badge required">first_name *</span>
                    <span class="col-badge">last_name</span>
                    <span class="col-badge">type</span>
                    <span class="col-badge">email</span>
                    <span class="col-badge">phone</span>
                    <span class="col-badge">mobile</span>
                    <span class="col-badge">job_title</span>
                    <span class="col-badge">department</span>
                    <span class="col-badge">linkedin</span>
                    <span class="col-badge">country</span>
                    <span class="col-badge">city</span>
                    <span class="col-badge">postal_code</span>
                    <span class="col-badge">address</span>
                </div>
                <p style="font-size:0.78rem;color:var(--text-muted);margin-top:1rem;line-height:1.5;">
                    <strong>type</strong>: Prospecto, Cliente, Proveedor<br>
                    Los registros con email duplicado se actualizarán automáticamente.
                </p>
            </div>

            <!-- Accounts columns -->
            <div id="colAccounts" style="display:none;">
                <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.75rem;">
                    <span
                        style="background:rgba(0,45,98,0.08);color:var(--primary);padding:0.15rem 0.5rem;border-radius:6px;font-weight:700;font-size:0.72rem;">*obligatorio</span>
                </p>
                <div class="col-grid">
                    <span class="col-badge required">name *</span>
                    <span class="col-badge">type</span>
                    <span class="col-badge">priority</span>
                    <span class="col-badge">industry</span>
                    <span class="col-badge">website</span>
                    <span class="col-badge">linkedin</span>
                    <span class="col-badge">phone</span>
                    <span class="col-badge">email</span>
                    <span class="col-badge">country</span>
                    <span class="col-badge">city</span>
                    <span class="col-badge">postal_code</span>
                    <span class="col-badge">billing_address</span>
                    <span class="col-badge">notes</span>
                </div>
                <p style="font-size:0.78rem;color:var(--text-muted);margin-top:1rem;line-height:1.5;">
                    <strong>type</strong>: customer, partner, vendor, competitor, other<br>
                    <strong>priority</strong>: A, B, C
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    /* ── State ── */
    let currentType = 'contacts';
    let previewReady = false;

    /* ── Tab switch ── */
    function switchTab(type) {
        currentType = type;
        previewReady = false;

        document.getElementById('tab-contacts').classList.toggle('active', type === 'contacts');
        document.getElementById('tab-accounts').classList.toggle('active', type === 'accounts');

        document.getElementById('colContacts').style.display = type === 'contacts' ? '' : 'none';
        document.getElementById('colAccounts').style.display = type === 'accounts' ? '' : 'none';
        document.getElementById('dlContacts').style.display = type === 'contacts' ? '' : 'none';
        document.getElementById('dlAccounts').style.display = type === 'accounts' ? 'flex' : 'none';

        // Reset
        resetUI();
    }

    function resetUI() {
        previewReady = false;
        document.getElementById('previewSection').style.display = 'none';
        document.getElementById('resultBox').style.display = 'none';
        document.getElementById('btnCommit').classList.remove('visible');
        document.getElementById('btnPreview').disabled = !document.getElementById('csvFile').files.length;
    }

    /* ── Drag & Drop ── */
    const dz = document.getElementById('dropZone');
    dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('dragover'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('dragover'));
    dz.addEventListener('drop', e => {
        e.preventDefault(); dz.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file) {
            document.getElementById('csvFile').files; // can't set directly
            // Assign via DataTransfer
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('csvFile').files = dt.files;
            onFileSelect(document.getElementById('csvFile'));
        }
    });

    function onFileSelect(input) {
        const file = input.files[0];
        if (!file) return;
        const label = document.getElementById('fileLabel');
        label.textContent = '📎 ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        label.style.display = 'block';
        document.getElementById('btnPreview').disabled = false;
        document.getElementById('btnCommit').classList.remove('visible');
        document.getElementById('previewSection').style.display = 'none';
        document.getElementById('resultBox').style.display = 'none';
    }

    /* ── Preview upload ── */
    async function uploadPreview() {
        const fileInput = document.getElementById('csvFile');
        if (!fileInput.files.length) return;

        setProgress(true, 'Analizando archivo…', 30);

        const form = new FormData();
        form.append('csv_file', fileInput.files[0]);
        form.append('import_type', currentType);

        try {
            const res = await fetch('<?= url('/importar/preview') ?>', { method: 'POST', body: form });
            const text = await res.text();

            // Try to parse as JSON
            let data;
            try {
                data = JSON.parse(text);
            } catch (parseErr) {
                setProgress(false);
                showResult('error', '❌ Respuesta no válida', 'El servidor no devolvió JSON válido. Respuesta: ' + text.substring(0, 300));
                return;
            }

            setProgress(true, 'Procesando…', 80);
            await sleep(300);
            setProgress(false);

            if (!data.success) {
                showResult('error', '❌ Error al analizar', data.message || 'Error desconocido');
                return;
            }

            renderPreview(data);
            previewReady = true;
            document.getElementById('btnCommit').classList.add('visible');
            document.getElementById('resultBox').style.display = 'none';

        } catch (err) {
            setProgress(false);
            showResult('error', 'Error de red', err.message);
        }
    }

    function renderPreview(data) {
        const sec = document.getElementById('previewSection');
        const head = document.getElementById('previewHead');
        const body = document.getElementById('previewBody');
        const count = document.getElementById('previewCount');

        count.textContent = `(${data.total} registros · mostrando primeros ${data.preview.length})`;

        // Headers
        head.innerHTML = '<tr>' + data.headers.map(h =>
            `<th>${h}</th>`
        ).join('') + '</tr>';

        // Rows
        body.innerHTML = data.preview.map(row =>
            '<tr>' + data.headers.map(h =>
                `<td>${escHtml(row[h] ?? '')}</td>`
            ).join('') + '</tr>'
        ).join('');

        sec.style.display = '';
        sec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /* ── Commit ── */
    async function commitImport() {
        if (!previewReady) return;

        const btn = document.getElementById('btnCommit');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importando…';
        setProgress(true, 'Insertando en la base de datos…', 60);

        try {
            const res = await fetch('<?= url('/importar/commit') ?>', { method: 'POST' });
            const data = await res.json();

            setProgress(true, 'Finalizando…', 100);
            await sleep(400);
            setProgress(false);

            if (!data.success) {
                showResult('error', '❌ Error en la importación', data.message);
            } else {
                const entity = currentType === 'contacts' ? 'contactos' : 'organizaciones';
                showResult('success',
                    `✅ Importación completada`,
                    `Se importaron <strong>${data.inserted}</strong> ${entity} correctamente.`,
                    data
                );
                document.getElementById('previewSection').style.display = 'none';
                previewReady = false;
            }

        } catch (err) {
            setProgress(false);
            showResult('error', 'Error de red', err.message);
        }

        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-database"></i> Confirmar e importar todos';
        btn.classList.remove('visible');
    }

    /* ── Helpers ── */
    function setProgress(visible, label = '', pct = 0) {
        const wrap = document.getElementById('progressWrap');
        const bar = document.getElementById('progressBar');
        const lbl = document.getElementById('progressLabel');
        wrap.style.display = visible ? '' : 'none';
        bar.style.width = pct + '%';
        lbl.textContent = label;
    }

    function showResult(type, title, message, data = null) {
        const box = document.getElementById('resultBox');
        const h3 = document.getElementById('resultTitle');
        const msg = document.getElementById('resultMessage');
        const stats = document.getElementById('resultStats');
        const errs = document.getElementById('resultErrors');

        box.className = 'result-box ' + type;
        h3.textContent = title;
        msg.innerHTML = message;
        stats.innerHTML = '';
        errs.innerHTML = '';

        if (data) {
            if (data.inserted !== undefined) {
                stats.innerHTML += `<div class="stat-pill" style="color:#166534;"><i class="fas fa-check-circle"></i> ${data.inserted} insertados</div>`;
            }
            if (data.skipped !== undefined && data.skipped > 0) {
                stats.innerHTML += `<div class="stat-pill" style="color:#92400e;"><i class="fas fa-exclamation-triangle"></i> ${data.skipped} omitidos</div>`;
            }
            if (data.errors && data.errors.length) {
                data.errors.forEach(e => {
                    const li = document.createElement('li');
                    li.textContent = e;
                    errs.appendChild(li);
                });
            }

            // Link to destination
            // CÁMBIALO A ESTO
            const dest = currentType === 'contacts' ? '<?= url('/contactos') ?>' : '<?= url('/organizaciones') ?>'; const lbl = currentType === 'contacts' ? 'Ver Contactos' : 'Ver Organizaciones';
            stats.innerHTML += `<a href="${dest}" class="stat-pill" style="color:var(--primary);text-decoration:none;"><i class="fas fa-arrow-right"></i> ${lbl}</a>`;
        }

        box.style.display = '';
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function escHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>