<?php
// chatbot.php - Componente flutuante do Opi IA
?>
<!-- BOTÃO FLUTUANTE DO CHATBOT OPI IA -->
<div class="opi-chat-trigger" id="opiChatTrigger" onclick="toggleOpiChat()" title="Tirar dúvidas com o Opi IA">
    <div class="opi-trigger-avatar">
        <img src="../assets/img/opi pulando feliz.png" alt="Opi IA" class="opi-trigger-img">
        <span class="opi-online-dot"></span>
    </div>
    <span class="opi-trigger-text">Opi IA</span>
</div>

<!-- JANELA DO CHATBOT -->
<div class="opi-chat-window" id="opiChatWindow">
    <!-- CABEÇALHO DA JANELA -->
    <div class="opi-chat-header">
        <div class="opi-chat-user-info">
            <div class="opi-chat-avatar-wrapper">
                <img src="../assets/img/opi pulando feliz.png" alt="Opi Assistant" class="opi-chat-header-img">
                <span class="opi-online-status"></span>
            </div>
            <div class="opi-chat-title-box">
                <span class="opi-chat-name">Opi IA <i class="fa-solid fa-sparkles" style="color: #ffc800; font-size: 0.8rem;"></i></span>
                <span class="opi-chat-sub">Assistente do Opus & Java</span>
            </div>
        </div>
        <div class="opi-chat-actions">
            <button class="opi-chat-btn-action" onclick="limparChatOpi()" title="Limpar histórico"><i class="fa-solid fa-trash-can"></i></button>
            <button class="opi-chat-btn-action" onclick="toggleOpiChat()" title="Fechar chat"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>

    <!-- CORPO DE MENSAGENS -->
    <div class="opi-chat-messages" id="opiChatMessages">
        <div class="opi-msg opi-msg-bot">
            <div class="opi-msg-avatar">
                <img src="../assets/img/opi pulando feliz.png" alt="Opi">
            </div>
            <div class="opi-msg-bubble">
                Olá! Eu sou o <strong>Opi IA</strong> 🤖<br>
                Estou aqui para tirar suas dúvidas sobre a plataforma Opus (Vidas, Fogo, Ligas, Baús) ou explicar matérias de programação em Java. Como posso te ajudar agora?
            </div>
        </div>
    </div>

    <!-- PÍLULAS DE SUGESTÃO RÁPIDA -->
    <div class="opi-chat-suggestions" id="opiChatSuggestions">
        <button class="opi-chip" onclick="enviarSugestaoOpi('Como funcionam as Vidas e o Fogo no Opus?')">❤️ Vidas & Fogo</button>
        <button class="opi-chip" onclick="enviarSugestaoOpi('Como funciona o Baú de Recompensas?')">🎁 Baú de Recompensas</button>
        <button class="opi-chip" onclick="enviarSugestaoOpi('O que é uma variável em Java?')">💻 Variáveis em Java</button>
        <button class="opi-chip" onclick="enviarSugestaoOpi('Como funciona o laço for em Java?')">🔄 Loops em Java</button>
        <button class="opi-chip" onclick="enviarSugestaoOpi('O que é Orientação a Objetos?')">🏗️ POO em Java</button>
    </div>

    <!-- BARRA DE DIGITAÇÃO -->
    <div class="opi-chat-input-container">
        <input type="text" id="opiChatInput" placeholder="Pergunte ao Opi sobre Java ou sobre o sistema..." onkeypress="handleOpiKeyPress(event)" autocomplete="off">
        <button id="opiChatSendBtn" onclick="enviarMensagemOpi()" title="Enviar mensagem">
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </div>
</div>

<link rel="stylesheet" href="../assets/css/chatbot.css">
<script src="../assets/js/chatbot.js"></script>
