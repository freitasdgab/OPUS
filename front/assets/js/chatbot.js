// chatbot.js - Lógica interativa do Opi IA

function toggleOpiChat() {
    const chatWin = document.getElementById('opiChatWindow');
    if (!chatWin) return;
    chatWin.classList.toggle('active');
    
    if (chatWin.classList.contains('active')) {
        const input = document.getElementById('opiChatInput');
        if (input) input.focus();
        scrollChatAoFim();
    }
}

function handleOpiKeyPress(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        enviarMensagemOpi();
    }
}

function enviarSugestaoOpi(texto) {
    const chatWin = document.getElementById('opiChatWindow');
    if (chatWin && !chatWin.classList.contains('active')) {
        chatWin.classList.add('active');
    }
    enviarMensagemOpi(texto);
}

function enviarMensagemOpi(textoManual) {
    const input = document.getElementById('opiChatInput');
    const msgContainer = document.getElementById('opiChatMessages');
    const sendBtn = document.getElementById('opiChatSendBtn');
    
    const mensagem = textoManual ? textoManual : (input ? input.value.trim() : '');
    if (!mensagem) return;

    if (!textoManual && input) {
        input.value = '';
    }

    // 1. Adiciona bolha do usuário
    adicionarBolhaUsuario(mensagem);
    scrollChatAoFim();

    // 2. Exibe indicador "Opi digitando..."
    exibirDigitando();
    scrollChatAoFim();
    if (sendBtn) sendBtn.disabled = true;

    // 3. Faz chamada à API do Opi IA
    fetch('../../back/api_chatbot.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json; charset=utf-8' },
        body: JSON.stringify({ mensagem: mensagem })
    })
    .then(r => r.json())
    .then(data => {
        removerDigitando();
        if (sendBtn) sendBtn.disabled = false;

        if (data.success && data.resposta) {
            adicionarBolhaOpi(data.resposta);
        } else {
            adicionarBolhaOpi("Desculpe, tive um pequeno problema ao processar. Pode tentar novamente?");
        }
        scrollChatAoFim();
    })
    .catch(err => {
        console.error('Erro no Chatbot Opi:', err);
        removerDigitando();
        if (sendBtn) sendBtn.disabled = false;
        adicionarBolhaOpi("Ops! Tive um problema de conexão. Verifique sua internet e tente novamente.");
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
            <img src="../assets/img/opi pulando feliz.png" alt="Opi">
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
            <img src="../assets/img/opi pulando feliz.png" alt="Opi">
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
    
    msgContainer.innerHTML = `
        <div class="opi-msg opi-msg-bot">
            <div class="opi-msg-avatar">
                <img src="../assets/img/opi pulando feliz.png" alt="Opi">
            </div>
            <div class="opi-msg-bubble">
                Histórico limpo! 🧹<br>
                Como posso te ajudar agora? Tire suas dúvidas sobre o Opus ou sobre programação em Java!
            </div>
        </div>
    `;
}

function escaparHTML(str) {
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatarMarkdownOpi(texto) {
    if (!texto) return '';

    let html = texto;

    // Blocos de Código ```java ... ```
    html = html.replace(/```(?:java)?\n?([\s\S]*?)```/gi, function(match, code) {
        return '<pre><code>' + escaparHTML(code.trim()) + '</code></pre>';
    });

    // Código em linha `code`
    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

    // Negrito **texto**
    html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

    // Itálico *texto*
    html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');

    // Quebras de linha
    html = html.replace(/\n/g, '<br>');

    return html;
}
