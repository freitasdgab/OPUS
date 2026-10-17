<?php
// chatbot.php - Componente flutuante do Opi IA
?>
<!-- BOTÃO FLUTUANTE DO CHATBOT OPI IA (SOMENTE TEXTO IA, AZUL, SEM NEON) -->
<button class="opi-chat-trigger opi-btn-mascot" id="opiChatTrigger" onclick="toggleOpiChat()" title="Tirar dúvidas com o Opi IA">
    <span class="opi-trigger-text">IA</span>
</button>

<!-- JANELA DO CHATBOT -->
<div class="opi-chat-window" id="opiChatWindow">
    <!-- CABEÇALHO DA JANELA -->
    <div class="opi-chat-header">
        <div class="opi-chat-user-info">
            <div class="opi-chat-avatar-wrapper">
                <span class="opi-chat-header-ia">IA</span>
                <span class="opi-online-status"></span>
            </div>
            <div class="opi-chat-title-box">
                <span class="opi-chat-name">Opi IA</span>
                <span class="opi-chat-sub">Assistente do Opus & Java</span>
            </div>
        </div>
        <div class="opi-chat-actions">
            <button class="opi-chat-btn-action" onclick="toggleExpandOpiChat()" title="Ampliar chat"><i class="fa-solid fa-expand"></i></button>
            <button class="opi-chat-btn-action" onclick="limparChatOpi()" title="Limpar histórico"><i class="fa-solid fa-trash-can"></i></button>
            <button class="opi-chat-btn-action" onclick="toggleOpiChat()" title="Fechar chat"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>

    <!-- CORPO DE MENSAGENS -->
    <div class="opi-chat-messages" id="opiChatMessages">
        <div class="opi-msg opi-msg-bot">
            <div class="opi-msg-avatar">
                <span class="opi-msg-ia-badge">IA</span>
            </div>
            <div class="opi-msg-bubble">
                Olá! Eu sou o <strong>Opi IA</strong> 🤖<br>
                Estou aqui para tirar suas dúvidas sobre a plataforma Opus (Vidas, Fogo, Ligas, Baús) ou explicar matérias de programação em Java. Como posso te ajudar agora?
            </div>
        </div>
    </div>



    <!-- PÍLULAS DE SUGESTÃO RÁPIDA (Rolar ou arrastar para o lado) -->
    <div class="opi-chat-suggestions" id="opiChatSuggestions">
        <button class="opi-chip" onclick="enviarSugestaoOpi('Quantas sessões faltam para eu terminar o curso?')">📊 Sessões Restantes</button>
        <button class="opi-chip" onclick="enviarSugestaoOpi('E por porcentagem?')">📈 Por Porcentagem</button>
        <button class="opi-chip" onclick="enviarSugestaoOpi('Nas ligas, quanto XP eu tenho que ganhar para conseguir subir de liga?')">🏆 Subir de Liga</button>
        <button class="opi-chip" onclick="enviarSugestaoOpi('Hoje eu fiz quantas lições?')">📅 Lições de Hoje</button>
        <button class="opi-chip" onclick="enviarSugestaoOpi('O que eu vou aprender na sessão dois?')">📚 Sessão 2</button>
        <button class="opi-chip" onclick="enviarSugestaoOpi('Com esse conhecimento eu consigo uma vaga de emprego ou estágio?')">💼 Emprego & Estágio</button>
        <button class="opi-chip" onclick="enviarSugestaoOpi('Como funcionam as Vidas e o Fogo no Opus?')">❤️ Vidas & Fogo</button>
    </div>

    <div class="opi-chat-input-container">
        <input type="text" id="opiChatInput" placeholder="Pergunte ao Opi sobre Java ou sobre o sistema..." onkeypress="handleOpiKeyPress(event)" autocomplete="off">
        <button id="opiChatSendBtn" onclick="enviarMensagemOpi()" title="Enviar mensagem">
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </div>
</div>

<link rel="stylesheet" href="../assets/css/chatbot.css">
<script src="../assets/js/chatbot.js"></script>
