// chatbot.js - Lógica interativa do Opi IA (Groq & Conhecimento Local)

// Histórico de mensagens recente mantido na sessão do navegador
let opiHistorico = [];

function toggleOpiChat() {
    const chatWin = document.getElementById('opiChatWindow');
    if (!chatWin) return;
    chatWin.classList.toggle('active');
    
    if (chatWin.classList.contains('active')) {
        const input = document.getElementById('opiChatInput');
        if (input) input.focus();
        scrollChatAoFim();
        inicializarArrastoSugestoes();
    }
}

function toggleExpandOpiChat() {
    const chatWin = document.getElementById('opiChatWindow');
    if (!chatWin) return;
    chatWin.classList.toggle('expanded');
    scrollChatAoFim();
}

function handleOpiKeyPress(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        enviarMensagemOpi();
    }
}

let opiArrastandoSugestoes = false;

function enviarSugestaoOpi(texto) {
    if (opiArrastandoSugestoes) return;
    const chatWin = document.getElementById('opiChatWindow');
    if (chatWin && !chatWin.classList.contains('active')) {
        chatWin.classList.add('active');
    }
    enviarMensagemOpi(texto);
}

function acionarComandoOpi(comando) {
    if (opiArrastandoSugestoes) return;
    const chatWin = document.getElementById('opiChatWindow');
    if (chatWin && !chatWin.classList.contains('active')) {
        chatWin.classList.add('active');
    }
    const input = document.getElementById('opiChatInput');
    const atual = input ? input.value.trim() : '';

    if (atual && !atual.startsWith('/')) {
        input.value = '';
        enviarMensagemOpi(comando + ' ' + atual);
    } else {
        enviarMensagemOpi(comando);
        if (input && comando === '/browser') {
            input.value = '/browser ';
            input.focus();
        }
    }
}

function obterContextoAtual() {
    try {
        const partes = [];
        const params = new URLSearchParams(window.location.search);
        if (params.get('cap')) partes.push('Seção ' + params.get('cap'));
        if (params.get('licao')) partes.push('Lição ' + params.get('licao'));

        const lessonTitle = document.querySelector('.lesson-title, .cdx-heading');
        if (lessonTitle && lessonTitle.textContent.trim()) {
            partes.push('Título: ' + lessonTitle.textContent.trim());
        }

        const missionBox = document.querySelector('.cdx-mission-box, .question-text');
        if (missionBox && missionBox.textContent.trim()) {
            partes.push('Questão/Missão: ' + missionBox.textContent.trim().substring(0, 180));
        }

        if (partes.length > 0) {
            return partes.join(' | ');
        }

        const pageHeading = document.querySelector('h1, h2');
        if (pageHeading && pageHeading.textContent.trim()) {
            return 'Seção: ' + pageHeading.textContent.trim();
        }

        if (document.title) {
            return 'Página: ' + document.title.replace('- Opus', '').trim();
        }
    } catch (e) {
        // Fallback silencioso
    }
    return 'Navegando no aplicativo Opus';
}

function enviarMensagemOpi(textoManual) {
    const input = document.getElementById('opiChatInput');
    const msgContainer = document.getElementById('opiChatMessages');
    const sendBtn = document.getElementById('opiChatSendBtn');
    
    const mensagem = textoManual ? textoManual.trim() : (input ? input.value.trim() : '');
    if (!mensagem) return;

    if (!textoManual && input) {
        input.value = '';
    }

    // 1. Adiciona bolha do usuário
    adicionarBolhaUsuario(mensagem);
    scrollChatAoFim();

    // 2. Registra mensagem do usuário no histórico local
    opiHistorico.push({ role: 'user', content: mensagem });
    if (opiHistorico.length > 8) {
        opiHistorico = opiHistorico.slice(-8);
    }

    // 3. Exibe indicador "Opi digitando..."
    exibirDigitando();
    scrollChatAoFim();
    if (sendBtn) sendBtn.disabled = true;

    // 4. Faz chamada à API do Opi IA (Groq / Local + Consultas)
    const payload = {
        mensagem: mensagem,
        contexto: obterContextoAtual(),
        historico: opiHistorico.slice(0, -1) // Envia mensagens anteriores
    };

    fetch('../../back/api/chatbot.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=utf-8' },
        body: JSON.stringify(payload)
    })
    .then(r => {
        if (!r.ok) {
            throw new Error('HTTP ' + r.status);
        }
        return r.json();
    })
    .then(data => {
        removerDigitando();
        if (sendBtn) sendBtn.disabled = false;

        if (data && data.success && data.resposta) {
            adicionarBolhaOpi(data.resposta);
            // Salva resposta do assistente no histórico
            opiHistorico.push({ role: 'assistant', content: data.resposta });
        } else {
            const msgErro = (data && data.resposta) 
                ? data.resposta 
                : "Desculpe, tive um pequeno problema ao processar sua pergunta. Pode tentar novamente?";
            adicionarBolhaOpi(msgErro);
        }
        scrollChatAoFim();
    })
    .catch(err => {
        console.error('Erro no Chatbot Opi:', err);
        removerDigitando();
        if (sendBtn) sendBtn.disabled = false;
        adicionarBolhaOpi("Ops! Tive um problema de comunicação. Verifique se o servidor local está ativo e tente novamente.");
        scrollChatAoFim();
    });
}

function adicionarBolhaUsuario(texto) {
    const msgContainer = document.getElementById('opiChatMessages');
    if (!msgContainer) return;

    const div = document.createElement('div');
    div.className = 'opi-msg opi-msg-user';
    div.innerHTML = `
        <div class="opi-msg-bubble">
            ${escaparHTML(texto)}
        </div>
    `;
    msgContainer.appendChild(div);
}

function adicionarBolhaOpi(textoMarkdown) {
    const msgContainer = document.getElementById('opiChatMessages');
    if (!msgContainer) return;

    const div = document.createElement('div');
    div.className = 'opi-msg opi-msg-bot';
    
    const textoFormatado = formatarMarkdownOpi(textoMarkdown);

    div.innerHTML = `
        <div class="opi-msg-avatar">
            <span class="opi-msg-ia-badge">IA</span>
        </div>
        <div class="opi-msg-bubble">
            ${textoFormatado}
        </div>
    `;
    msgContainer.appendChild(div);
}

function exibirDigitando() {
    const msgContainer = document.getElementById('opiChatMessages');
    if (!msgContainer || document.getElementById('opiTypingElem')) return;

    const div = document.createElement('div');
    div.className = 'opi-msg opi-msg-bot';
    div.id = 'opiTypingElem';
    div.innerHTML = `
        <div class="opi-msg-avatar">
            <span class="opi-msg-ia-badge">IA</span>
        </div>
        <div class="opi-typing-indicator">
            <div class="opi-typing-dot"></div>
            <div class="opi-typing-dot"></div>
            <div class="opi-typing-dot"></div>
        </div>
    `;
    msgContainer.appendChild(div);
}

function removerDigitando() {
    const elem = document.getElementById('opiTypingElem');
    if (elem) elem.remove();
}

function scrollChatAoFim() {
    const msgContainer = document.getElementById('opiChatMessages');
    if (msgContainer) {
        msgContainer.scrollTop = msgContainer.scrollHeight;
    }
}

function limparChatOpi() {
    const msgContainer = document.getElementById('opiChatMessages');
    if (!msgContainer) return;
    
    opiHistorico = [];
    
    msgContainer.innerHTML = `
        <div class="opi-msg opi-msg-bot">
            <div class="opi-msg-avatar">
                <span class="opi-msg-ia-badge">IA</span>
            </div>
            <div class="opi-msg-bubble">
                Histórico limpo! 🧹<br>
                Pergunte <strong>qualquer coisa</strong> ou use os comandos de consulta em tempo real: <code>/browser</code>, <code>/goal</code> e <code>/boost</code>!
            </div>
        </div>
    `;
}

function escaparHTML(str) {
    if (!str) return '';
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatarMarkdownOpi(texto) {
    if (!texto) return '';

    // 1. Isola blocos de código ```java ... ``` para proteger a formatação
    const codeBlocks = [];
    let processado = texto.replace(/```([a-zA-Z0-9_+#.-]*)\n?([\s\S]*?)```/g, function(match, lang, code) {
        const placeholder = '___CODEBLOCK_' + codeBlocks.length + '___';
        codeBlocks.push({ lang: lang ? lang.trim() : 'code', code: code.trim() });
        return placeholder;
    });

    // 2. Escapa caracteres HTML para segurança contra XSS
    processado = escaparHTML(processado);

    // 2.5 Cabeçalhos Markdown (#, ##, ###, ####)
    processado = processado.replace(/^#{1,4}\s+(.+)$/gm, '<strong style="display:block;margin-top:6px;margin-bottom:2px;font-size:1.04em;">$1</strong>');

    // 2.6 Linhas divisórias Markdown (---)
    processado = processado.replace(/^-{3,}$/gm, '<hr style="border:none;border-top:1px solid rgba(255,255,255,0.15);margin:6px 0;">');

    // 3. Código em linha `exemplo`
    processado = processado.replace(/`([^`]+)`/g, '<code>$1</code>');

    // 4. Negrito **texto**
    processado = processado.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

    // 5. Itálico *texto*
    processado = processado.replace(/\*([^*\n]+)\*/g, '<em>$1</em>');

    // 6. Links Markdown [texto](https://...) e Links HTTP/HTTPS clicáveis (preservando parênteses de URLs da Wikipédia)
    const linkBlocks = [];
    processado = processado.replace(/\[([^\]]+)\]\((https?:\/\/(?:[^\s\(\)]+|\([^\s\(\)]*\))+)\)/g, function(match, label, url) {
        const placeholder = '___LINKBLOCK_' + linkBlocks.length + '___';
        linkBlocks.push(`<a href="${url}" target="_blank" rel="noopener noreferrer" style="color:#38bdf8;text-decoration:underline;">${label}</a>`);
        return placeholder;
    });
    processado = processado.replace(/(https?:\/\/[^\s<]+)/g, function(rawUrl) {
        let cleanUrl = rawUrl;
        let trailing = '';
        while (/[.,;:!?]$/.test(cleanUrl) || (cleanUrl.endsWith(')') && (cleanUrl.match(/\(/g) || []).length < (cleanUrl.match(/\)/g) || []).length)) {
            trailing = cleanUrl.slice(-1) + trailing;
            cleanUrl = cleanUrl.slice(0, -1);
        }
        return `<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer" style="color:#38bdf8;text-decoration:underline;">${cleanUrl}</a>${trailing}`;
    });
    linkBlocks.forEach(function(htmlLink, idx) {
        processado = processado.replace('___LINKBLOCK_' + idx + '___', htmlLink);
    });

    // 7. Listas e marcadores
    processado = processado.replace(/^[•*-]\s+(.+)$/gm, '• $1');

    // 8. Quebras de linha normais
    processado = processado.replace(/\n/g, '<br>');

    // 9. Reinsere os blocos de código com formatação limpa
    codeBlocks.forEach(function(item, idx) {
        const placeholder = '___CODEBLOCK_' + idx + '___';
        const codeEscapado = escaparHTML(item.code);
        const blocoHtml = `<div class="opi-code-block"><div class="opi-code-header"><span class="opi-code-lang">${item.lang}</span></div><pre><code>${codeEscapado}</code></pre></div>`;
        processado = processado.replace(placeholder, blocoHtml);
    });

    return processado;
}

// Ativa rolagem horizontal por arrasto do mouse e roda do mouse nas pílulas de sugestão
function inicializarArrastoSugestoes() {
    const container = document.getElementById('opiChatSuggestions');
    if (!container || container.dataset.dragInit === 'true') return;
    container.dataset.dragInit = 'true';

    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;
    let dragDist = 0;

    container.addEventListener('mousedown', function(e) {
        isDown = true;
        dragDist = 0;
        opiArrastandoSugestoes = false;
        container.classList.add('is-dragging');
        startX = e.pageX - container.offsetLeft;
        scrollLeft = container.scrollLeft;
    });

    container.addEventListener('mouseleave', function() {
        isDown = false;
        container.classList.remove('is-dragging');
        setTimeout(function() { opiArrastandoSugestoes = false; }, 50);
    });

    container.addEventListener('mouseup', function() {
        isDown = false;
        container.classList.remove('is-dragging');
        if (dragDist > 6) {
            opiArrastandoSugestoes = true;
            setTimeout(function() { opiArrastandoSugestoes = false; }, 80);
        } else {
            opiArrastandoSugestoes = false;
        }
    });

    container.addEventListener('mousemove', function(e) {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const walk = (x - startX) * 1.5;
        dragDist += Math.abs(walk);
        if (dragDist > 6) {
            opiArrastandoSugestoes = true;
        }
        container.scrollLeft = scrollLeft - walk;
    });

    // Rolar horizontalmente com a rodinha do mouse
    container.addEventListener('wheel', function(e) {
        if (e.deltaY !== 0) {
            e.preventDefault();
            container.scrollLeft += e.deltaY;
        }
    }, { passive: false });
}

document.addEventListener('DOMContentLoaded', inicializarArrastoSugestoes);

