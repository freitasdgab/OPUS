<?php
$c = file_get_contents('front/assets/js/chat.js');

$search = <<<'JS'
                data.forEach(c => {
                    let unreadHtml = c.unread > 0 ? `<div class="cli-unread">${c.unread}</div>` : '';
                    let activeClass = c.id === currentChatUser ? 'active' : '';
                    
                    let ultima = c.ultima_msg;
                    if (ultima.includes('[DUEL_INVITE]')) ultima = '🔥 Convite para Batalha de Fogo';
                    
                    html += `
                    <div class="chat-list-item ${activeClass}" onclick="abrirChat(${c.id})">
                        <img src="${c.foto}">
                        <div class="cli-info">
                            <div class="cli-name">${c.nome}</div>
                            <div class="cli-last">${ultima}</div>
                        </div>
                        ${unreadHtml}
                    </div>
                    `;
                });
JS;

$replace = <<<'JS'
                data.forEach(c => {
                    let unreadHtml = c.unread > 0 ? `<div class="cli-unread">${c.unread}</div>` : '';
                    let activeClass = c.id === currentChatUser ? 'active' : '';
                    
                    let ultima = c.ultima_msg;
                    if (ultima && ultima.includes('[DUEL_INVITE]')) ultima = '🔥 Convite para Batalha de Fogo';
                    
                    let battleIcon = c.is_battling ? `<div title="Batalha Ativa!" style="color:#ff0055; margin-left:5px;"><i class="fa-solid fa-fire-flame-curved"></i></div>` : '';
                    
                    html += `
                    <div class="chat-list-item ${activeClass}" onclick="abrirChat(${c.id})">
                        <img src="${c.foto}">
                        <div class="cli-info">
                            <div class="cli-name" style="display:flex; align-items:center;">${c.nome} ${battleIcon}</div>
                            <div class="cli-last">${ultima}</div>
                        </div>
                        ${unreadHtml}
                    </div>
                    `;
                });
JS;

$c = preg_replace('/data\.forEach\(c => \{.*?<\/div>\s*`;\s*\}\);/s', $replace, $c);

file_put_contents('front/assets/js/chat.js', $c);
echo "fixed chat.js list";
