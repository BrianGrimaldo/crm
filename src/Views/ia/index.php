<?php
$pageTitle = 'Asistente del CRM - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<style>
    /* ── Variables locales ─────────────────────────────────────── */
    :root {
        --ia-radius: 18px;
        --ia-radius-sm: 12px;
    }

    /* Ocultamos el header genérico de página: el asistente vive en su propio lienzo */
    .page-header {
        max-width: 780px;
        margin: 0 auto 0;
        padding-left: .25rem;
        padding-right: .25rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .page-header .btn-new-chat {
        margin-top: .3rem;
        flex-shrink: 0;
    }

    /* ── Layout principal ─────────────────────────────────────── */
    .ia-shell {
        display: flex;
        justify-content: center;
        height: calc(100vh - 210px);
        min-height: 480px;
    }

    .ia-shell-inner {
        width: 100%;
        max-width: 780px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* ── Área de mensajes (sin "panel" con bordes, como Claude) ──── */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem .25rem 1rem;
        display: flex;
        flex-direction: column;
        gap: 1.6rem;
    }

    .chat-messages::-webkit-scrollbar {
        width: 5px;
    }

    .chat-messages::-webkit-scrollbar-thumb {
        background: var(--border);
        border-radius: 4px;
    }

    /* ── Mensajes ──────────────────────────────────────────────── */
    .msg {
        display: flex;
        gap: .75rem;
        align-items: flex-start;
        width: 100%;
    }

    .msg-avatar {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: #fff;
        font-size: .75rem;
        margin-top: .1rem;
    }

    .msg-user {
        justify-content: flex-end;
    }

    .msg-body {
        max-width: 82%;
    }

    .msg-user .msg-body {
        background: rgba(128, 128, 128, .09);
        border-radius: var(--ia-radius-sm);
        padding: .65rem 1rem;
    }

    .msg-ai .msg-body {
        max-width: 100%;
        padding-top: .05rem;
    }

    .msg-text {
        font-size: .92rem;
        line-height: 1.65;
        color: var(--text-main);
    }

    .msg-text p {
        margin: 0 0 .75rem;
    }

    .msg-text p:last-child {
        margin-bottom: 0;
    }

    .msg-time {
        font-size: .68rem;
        color: var(--text-muted);
        margin-top: .3rem;
    }

    .msg-user .msg-time {
        text-align: right;
    }

    /* ── Estado de bienvenida (centrado, como pantalla inicial de Claude) ── */
    .welcome-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 1.5rem;
        padding: 1rem;
    }

    .welcome-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.3rem;
    }

    .welcome-title {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -.02em;
        margin-bottom: .5rem;
    }

    .welcome-subtitle {
        font-size: .88rem;
        color: var(--text-muted);
        max-width: 420px;
        line-height: 1.5;
        margin: 0 auto;
    }

    .welcome-cards {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: .65rem;
        width: 100%;
        max-width: 560px;
    }

    @media(max-width:600px) {
        .welcome-cards {
            grid-template-columns: 1fr;
        }
    }

    .welcome-card {
        text-align: left;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--ia-radius-sm);
        padding: .8rem .95rem;
        font-size: .82rem;
        font-weight: 600;
        color: var(--text-main);
        cursor: pointer;
        transition: all .2s;
        font-family: 'Outfit', sans-serif;
        line-height: 1.4;
    }

    .welcome-card:hover {
        border-color: var(--accent);
        background: rgba(110, 223, 246, .06);
        transform: translateY(-1px);
    }

    /* ── Typing animation ──────────────────────────────────────── */
    .typing {
        display: flex;
        gap: 4px;
        align-items: center;
        padding: .4rem 0;
    }

    .typing span {
        width: 6px;
        height: 6px;
        background: var(--text-muted);
        border-radius: 50%;
        animation: typingBounce .8s infinite;
    }

    .typing span:nth-child(2) {
        animation-delay: .15s;
    }

    .typing span:nth-child(3) {
        animation-delay: .3s;
    }

    @keyframes typingBounce {

        0%,
        80%,
        100% {
            transform: scale(.6);
            opacity: .4;
        }

        40% {
            transform: scale(1);
            opacity: 1;
        }
    }

    /* ── Barra de input tipo "pill", flotante y centrada ─────────── */
    .chat-input-wrap {
        padding: .9rem .25rem 1.4rem;
    }

    .chat-input-box {
        display: flex;
        align-items: flex-end;
        gap: .5rem;
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: 22px;
        padding: .55rem .6rem .55rem 1.1rem;
        transition: border-color .2s, box-shadow .2s;
    }

    .chat-input-box:focus-within {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(110, 223, 246, .12);
    }

    .chat-input {
        flex: 1;
        border: none;
        background: transparent;
        color: var(--text-main);
        font-size: .9rem;
        font-family: 'Outfit', sans-serif;
        outline: none;
        resize: none;
        max-height: 160px;
        padding: .4rem 0;
        line-height: 1.4;
    }

    .chat-send {
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .2s;
        flex-shrink: 0;
    }

    .chat-send:hover {
        opacity: .85;
        transform: scale(1.05);
    }

    .chat-send:disabled {
        opacity: .35;
        cursor: not-allowed;
        transform: none;
    }

    .chat-footnote {
        text-align: center;
        font-size: .68rem;
        color: var(--text-muted);
        margin-top: .55rem;
    }

    /* Botón "nuevo chat" discreto, como acción secundaria en el header */
    .btn-new-chat {
        background: rgba(128, 128, 128, .08);
        border: 1px solid var(--border);
        color: var(--text-muted);
        border-radius: 8px;
        padding: .4rem .85rem;
        font-size: .78rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .2s;
        font-family: 'Outfit', sans-serif;
        display: flex;
        align-items: center;
        gap: .4rem;
        white-space: nowrap;
    }

    .btn-new-chat:hover {
        background: rgba(128, 128, 128, .15);
        color: var(--text-main);
    }
</style>

<!-- Page header -->
<div class="page-header">
    <div>
        <h1><i class="fas fa-robot" style="color:var(--accent);margin-right:.5rem;"></i>Asistente del CRM</h1>
        <p>Tu guía para descubrir funcionalidades y aprender a usar el sistema.</p>
    </div>
    <button class="btn-new-chat" onclick="newChat()">
        <i class="fas fa-plus"></i> Nuevo chat
    </button>
</div>

<!-- Chat IA -->
<div class="ia-shell">
    <div class="ia-shell-inner">

        <!-- Estado de bienvenida (se oculta cuando hay mensajes) -->
        <div class="welcome-state" id="welcomeState">
            <div class="welcome-icon"><i class="fas fa-robot"></i></div>
            <div>
                <div class="welcome-title">¿En qué te ayudo hoy?</div>
                <div class="welcome-subtitle">Pregúntame cómo usar cualquier módulo del CRM: te explico para qué
                    sirve y te doy los pasos exactos.</div>
            </div>
            <div class="welcome-cards" id="welcomeCards">
                <button class="welcome-card" onclick="sendSuggestion(this)">¿Cómo registro una nueva
                    oportunidad?</button>
                <button class="welcome-card" onclick="sendSuggestion(this)">¿Cómo genero un reporte?</button>
                <button class="welcome-card" onclick="sendSuggestion(this)">¿Cómo agrego un contacto nuevo?</button>
                <button class="welcome-card" onclick="sendSuggestion(this)">¿Para qué sirve el módulo de
                    Metas?</button>
            </div>
        </div>

        <!-- Mensajes -->
        <div class="chat-messages" id="chatMessages" style="display:none;"></div>

        <!-- Input -->
        <div class="chat-input-wrap">
            <div class="chat-input-box">
                <textarea class="chat-input" id="chatInput" placeholder="Pregúntame cómo usar el CRM…" rows="1"
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMessage();}"
                    oninput="autoGrow(this)"></textarea>
                <button class="chat-send" id="sendBtn" onclick="sendMessage()">
                    <i class="fas fa-arrow-up"></i>
                </button>
            </div>
            <div class="chat-footnote">El asistente puede cometer errores. Verifica los pasos importantes.</div>
        </div>

    </div>
</div><!-- /ia-shell -->

<script>
    const chatMessages = document.getElementById('chatMessages');
    const welcomeState = document.getElementById('welcomeState');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const chatShellEl = document.querySelector('.ia-shell-inner');

    function nowTime() {
        return new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }

    function autoGrow(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 160) + 'px';
    }

    function showChatArea() {
        welcomeState.style.display = 'none';
        chatMessages.style.display = 'flex';
    }

    function appendMsg(text, role, time) {
        const t = time || nowTime();
        const div = document.createElement('div');
        div.className = `msg msg-${role}`;

        const formatted = text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .split(/\n{2,}/)
            .map(p => `<p>${p.replace(/\n/g, '<br>')}</p>`)
            .join('');

        if (role === 'ai') {
            div.innerHTML = `
                <span class="msg-avatar"><i class="fas fa-robot"></i></span>
                <div class="msg-body">
                    <div class="msg-text">${formatted}</div>
                    <div class="msg-time">${t}</div>
                </div>`;
        } else {
            div.innerHTML = `
                <div class="msg-body">
                    <div class="msg-text">${formatted}</div>
                    <div class="msg-time">${t}</div>
                </div>`;
        }
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function showTyping() {
        const div = document.createElement('div');
        div.className = 'msg msg-ai';
        div.id = 'typing-indicator';
        div.innerHTML = `
            <span class="msg-avatar"><i class="fas fa-robot"></i></span>
            <div class="msg-body">
                <div class="typing"><span></span><span></span><span></span></div>
            </div>`;
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    function removeTyping() { document.getElementById('typing-indicator')?.remove(); }

    function sendSuggestion(btn) {
        chatInput.value = btn.textContent.trim();
        sendMessage();
    }

    async function sendMessage() {
        const msg = chatInput.value.trim();
        if (!msg || sendBtn.disabled) return;

        showChatArea();
        chatInput.value = '';
        autoGrow(chatInput);
        sendBtn.disabled = true;
        appendMsg(msg, 'user');
        showTyping();

        // Módulo actual: se lee del data-module del contenedor del chat.
        // Si este componente se embebe en otras vistas, cada una debe
        // definir su propio data-module (ej. "Contactos", "Oportunidades").
        const currentModule = chatShellEl?.dataset.module || null;

        try {
            const res = await fetch('<?= url('/ia/chat') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg, current_module: currentModule })
            });
            const data = await res.json();
            removeTyping();

            if (data.error) {
                appendMsg('⚠ ' + data.error, 'ai');
            } else {
                appendMsg(data.reply || 'Sin respuesta.', 'ai');
            }
        } catch (e) {
            removeTyping();
            appendMsg('Error de conexión. Verifica tu red e intenta de nuevo.', 'ai');
        }

        sendBtn.disabled = false;
        chatInput.focus();
    }

    async function newChat() {
        if (chatMessages.children.length > 0) {
            if (!confirm('¿Iniciar una nueva conversación? Se perderá el hilo actual.')) return;
        }
        try {
            await fetch('<?= url('/ia/new-conversation') ?>', { method: 'POST' });
        } catch (e) { }
        chatMessages.innerHTML = '';
        chatMessages.style.display = 'none';
        welcomeState.style.display = 'flex';
    }

    // Cargar historial al inicio
    (async function loadHistory() {
        try {
            const res = await fetch('<?= url('/ia/history') ?>');
            const data = await res.json();
            if (Array.isArray(data) && data.length > 0) {
                showChatArea();
                data.forEach(m => appendMsg(m.content, m.role === 'assistant' ? 'ai' : 'user'));
            }
        } catch (e) { }
    })();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>