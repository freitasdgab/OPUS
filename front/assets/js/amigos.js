// amigos.js - Lógica da Central de Amigos e Batalhas (VS)

document.addEventListener('DOMContentLoaded', () => {
    carregarGrupos();
    carregarBatalha(0);
});

function trocarAbaAmigos(aba) {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => btn.classList.remove('active'));
    tabContents.forEach(content => content.classList.remove('active'));

    if (aba === 'grupos') {
        if (tabBtns[0]) tabBtns[0].classList.add('active');
        const contentGrupos = document.getElementById('tabContentGrupos');
        if (contentGrupos) contentGrupos.classList.add('active');
        carregarGrupos();
    } else if (aba === 'batalha') {
        if (tabBtns[1]) tabBtns[1].classList.add('active');
        const contentBatalha = document.getElementById('tabContentBatalha');
        if (contentBatalha) contentBatalha.classList.add('active');
        carregarBatalhas1v1();
    }
}

// ── 1. CARREGAR QUADRADINHOS DE BATALHAS 1V1 (MINHAS BATALHAS) ──
function carregarBatalhas1v1() {
    fetch('../../back/api_amigos.php?action=listar_amigos&tipo=seguindo')
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('containerBatalhas1v1');
        if (!container) return;

        const amigos = data.amigos || [];
        if (amigos.length === 0) {
            container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 25px; background: #191924; border-radius: 20px; border: 2px dashed #2e2e42;">
                <i class="fa-solid fa-user-group" style="font-size: 2rem; margin-bottom: 10px; color: #1cb0f6;"></i>
                <p style="margin: 0; font-weight: 700;">Você ainda não tem batalhas 1v1 ativas.</p>
                <span style="font-size: 0.85rem; color: #64748b;">Crie um Grupo de Batalha ou adicione amigos para rivalizar acertos e XP!</span>
            </div>`;
            return;
        }

        container.innerHTML = '';
        amigos.forEach((a, index) => {
            container.innerHTML += `
                <div class="batalha-1v1-card ${index === 0 ? 'selected' : ''}" onclick="selecionarRival1v1(${a.id}, this)">
                    <div class="batalha-1v1-header">
                        <div class="batalha-1v1-user">
                            <img src="${a.foto_perfil}" class="batalha-1v1-avatar" alt="${a.nome}">
                            <div class="batalha-1v1-info">
                                <h4>VS ${a.nome}</h4>
                                <span>⚡ ${a.xp} XP acumulados</span>
                            </div>
                        </div>
                        <span style="font-family: 'Orbitron', sans-serif; font-size: 0.75rem; color: #ffc800; font-weight: 900;">1v1</span>
                    </div>

                    <div class="batalha-1v1-stats">
                        <div class="stat-item">
                            <span class="stat-label">ACERTOS</span>
                            <span class="stat-val" style="color: #58cc02;">HOJE</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">SEQUÊNCIA</span>
                            <span class="stat-val" style="color: #ff9600;">🔥 ${a.dias_fogo}d</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">DUELO</span>
                            <span class="stat-val" style="color: #1cb0f6;">ATIVO</span>
                        </div>
                    </div>

                    <button class="btn-batalhar-card">
                        <i class="fa-solid fa-swords"></i> Ver Duelo com ${a.nome}
                    </button>
                </div>`;
        });

        // Carrega por padrão o primeiro rival
        if (amigos.length > 0) {
            carregarBatalha(amigos[0].id);
        }
    });
}

function selecionarRival1v1(rivalId, cardElem) {
    document.querySelectorAll('.batalha-1v1-card').forEach(c => c.classList.remove('selected'));
    if (cardElem) cardElem.classList.add('selected');

    carregarBatalha(rivalId);

    const arena = document.getElementById('areaDetalhesBatalha');
    if (arena) {
        arena.scrollIntoView({ behavior: 'smooth' });
    }
}

function carregarBatalha(rivalId) {
    fetch(`../../back/api_amigos.php?action=obter_batalha&rival_id=${rivalId}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.batalha) return;

        const b = data.batalha;
        const p1 = b.player1;
        const p2 = b.player2;

        // Player 1 (Você)
        document.getElementById('p1Nome').innerText = p1.nome;
        document.getElementById('p1Foto').src = p1.foto_perfil;
        document.getElementById('p1Fogo').innerHTML = `<i class="fa-solid fa-fire" style="color:#ff9600;"></i> ${p1.dias_fogo} Dias`;
        document.getElementById('p1Acertos').innerText = p1.acertos_hoje;

        // Player 2 (Rival)
        document.getElementById('p2Nome').innerText = p2.nome;
        document.getElementById('p2Foto').src = p2.foto_perfil;
        document.getElementById('p2Fogo').innerHTML = `<i class="fa-solid fa-fire" style="color:#ff9600;"></i> ${p2.dias_fogo} Dias`;
        document.getElementById('p2Acertos').innerText = p2.acertos_hoje;

        // Barra de Poder
        document.getElementById('p1PctTxt').innerText = b.pct_p1 + '%';
        document.getElementById('p2PctTxt').innerText = b.pct_p2 + '%';
        document.getElementById('p1BarFill').style.width = b.pct_p1 + '%';
        document.getElementById('p2BarFill').style.width = b.pct_p2 + '%';

        // Métricas de Diferença de XP
        const diffXpElem = document.getElementById('xpGapText');
        const leaderElem = document.getElementById('xpLeaderText');

        if (b.diferenca_xp > 0) {
            diffXpElem.innerText = `+${b.diferenca_xp_abs} XP`;
            diffXpElem.style.color = '#58cc02';
            leaderElem.innerText = `Você está à frente de ${p2.nome}! 🔥`;
        } else if (b.diferenca_xp < 0) {
            diffXpElem.innerText = `-${b.diferenca_xp_abs} XP`;
            diffXpElem.style.color = '#ff4b4b';
            leaderElem.innerText = `${p2.nome} está na liderança! ⚡`;
        } else {
            diffXpElem.innerText = `0 XP`;
            diffXpElem.style.color = '#ffc800';
            leaderElem.innerText = `Duelo empatado! Quem fizer a próxima lição assume!`;
        }

        document.getElementById('p1Streak').innerText = p1.dias_fogo + 'd';
        document.getElementById('p2Streak').innerText = p2.dias_fogo + 'd';
    });
}

function provocarRival() {
    const rivalNome = document.getElementById('p2Nome').innerText;
    if (typeof opusAlerta === 'function') {
        opusAlerta({
            tipo: 'warning',
            titulo: 'Provocação Enviada! 🔥',
            mensagem: `Provocação enviada para <strong>${rivalNome}</strong>:<br><br><em>"Seu duelo tá fraco! Quero ver me passar!"</em> 😎`
        });
    } else {
        alert(`🔥 Provocação enviada para ${rivalNome}: "Seu duelo tá fraco! Quero ver me passar!" 😎`);
    }
}

// ── 2. COPIAR LINK E CONVITES (WHATSAPP E CHAT DO OPUS) ──────────
function copiarLinkConvite(userId) {
    const link = `${window.location.origin}/OPUS/front/pages/auth.html?convite=${userId}`;
    navigator.clipboard.writeText(link).then(() => {
        if (typeof opusAlerta === 'function') {
            opusAlerta({
                tipo: 'success',
                titulo: 'Link de Convite Copiado! 🎉',
                mensagem: 'O link foi copiado para sua área de transferência. Envie para seus amigos para jogarem juntos no Opus!',
                linkCopia: link
            });
        } else {
            alert('🎉 Link de convite copiado para a área de transferência!\n\nEnvie para seus amigos para eles entrarem no Opus!');
        }
    }).catch(() => {
        if (typeof opusAlerta === 'function') {
            opusAlerta({
                tipo: 'info',
                titulo: 'Link de Convite',
                mensagem: 'Copie seu link de convite abaixo:',
                linkCopia: link
            });
        } else {
            alert('Seu link de convite: ' + link);
        }
    });
}

function enviarConviteZapGeral(userId) {
    const link = `${window.location.origin}/OPUS/front/pages/auth.html?convite=${userId}`;
    const texto = encodeURIComponent(`🔥 Fala dev! Vem estudar Java e competir comigo no Opus! Acesse pelo meu link de convite: ${link}`);
    window.open(`https://api.whatsapp.com/send?text=${texto}`, '_blank');
}

function enviarConviteZapGrupo(codigoConvite, nomeGrupo) {
    const link = `${window.location.origin}/OPUS/front/pages/auth.html?grupo=${codigoConvite}`;
    const texto = encodeURIComponent(`⚔️ Fala dev! Entre no meu Grupo de Batalha "${nomeGrupo}" no Opus!\n\nUse o código: ${codigoConvite}\nOu clique no link direto: ${link}`);
    window.open(`https://api.whatsapp.com/send?text=${texto}`, '_blank');
}

function enviarConviteChatGrupo(codigoConvite, nomeGrupo) {
    const texto = `⚔️ Entre no meu Grupo de Batalha "${nomeGrupo}" no Opus! Código: ${codigoConvite}`;
    if (typeof enviarSugestaoOpi === 'function') {
        enviarSugestaoOpi(texto);
    } else {
        navigator.clipboard.writeText(texto);
        if (typeof opusToast === 'function') {
            opusToast('Mensagem de convite copiada! Cole no chat para convidar seus amigos.', 'success');
        } else {
            alert('🎉 Mensagem de convite copiada! Cole no chat para convidar seus amigos.');
        }
    }
}

// ── 3. LÓGICA DOS QUADRADINHOS DE GRUPOS ─────────────────────────
function carregarGrupos() {
    fetch('../../back/api_amigos.php?action=listar_grupos')
    .then(r => r.json())
    .then(data => {
        const containerMeus = document.getElementById('containerMeusGrupos');
        const containerSug = document.getElementById('containerGruposSugeridos');

        // Renderiza Meus Grupos (Quadradinhos)
        if (containerMeus) {
            if (!data.meus_grupos || data.meus_grupos.length === 0) {
                containerMeus.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 25px; background: #191924; border-radius: 20px; border: 2px dashed #2e2e42;">
                    <i class="fa-solid fa-shield-halved" style="font-size: 2rem; margin-bottom: 10px; color: #58cc02;"></i>
                    <p style="margin: 0; font-weight: 700;">Você ainda não participa de nenhum Grupo de Batalha.</p>
                    <span style="font-size: 0.85rem; color: #64748b;">Clique em "Criar Grupo de Batalha" acima ou digite o código de um amigo!</span>
                </div>`;
            } else {
                containerMeus.innerHTML = '';
                data.meus_grupos.forEach(g => {
                    const avs = (g.avatares || []).map(img => `<img src="${img}" alt="Membro">`).join('');
                    containerMeus.innerHTML += `
                        <div class="group-card">
                            <div class="group-card-header">
                                <div class="group-title-box">
                                    <h3>🛡️ ${g.nome}</h3>
                                    <div style="font-size: 0.82rem; color: #94a3b8; font-weight: 700; margin-top: 2px;">
                                        ${g.total_membros} Membros no Duelo
                                    </div>
                                    <div class="group-avatars-stack">
                                        ${avs}
                                    </div>
                                </div>
                                <span class="group-code-badge" title="Código do Grupo">${g.codigo_convite}</span>
                            </div>

                            <div class="group-card-actions">
                                <button class="btn-view-group" onclick="visualizarGrupo(${g.id})">
                                    <i class="fa-solid fa-eye"></i> Visualizar Batalha
                                </button>
                                <button class="btn-zap-group-card" title="Convidar no WhatsApp" onclick="enviarConviteZapGrupo('${g.codigo_convite}', '${g.nome}')">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </button>
                            </div>
                        </div>`;
                });
            }
        }

        // Renderiza Grupos Sugeridos
        if (containerSug) {
            if (!data.grupos_sugeridos || data.grupos_sugeridos.length === 0) {
                containerSug.innerHTML = `<div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 20px;">
                    Nenhum outro grupo público disponível no momento. Crie o seu!
                </div>`;
            } else {
                containerSug.innerHTML = '';
                data.grupos_sugeridos.forEach(g => {
                    const avs = (g.avatares || []).map(img => `<img src="${img}" alt="Membro">`).join('');
                    containerSug.innerHTML += `
                        <div class="group-card">
                            <div class="group-card-header">
                                <div class="group-title-box">
                                    <h3>🛡️ ${g.nome}</h3>
                                    <div style="font-size: 0.82rem; color: #94a3b8; font-weight: 700; margin-top: 2px;">
                                        ${g.total_membros} Membros
                                    </div>
                                    <div class="group-avatars-stack">
                                        ${avs}
                                    </div>
                                </div>
                                <span class="group-code-badge">${g.codigo_convite}</span>
                            </div>

                            <div class="group-card-actions">
                                <button class="btn-join-group-card" onclick="entrarNoGrupoDireto('${g.codigo_convite}')">
                                    <i class="fa-solid fa-user-plus"></i> Entrar no Grupo
                                </button>
                                <button class="btn-view-group" onclick="visualizarGrupo(${g.id})">
                                    <i class="fa-solid fa-eye"></i> Visualizar
                                </button>
                            </div>
                        </div>`;
                });
            }
        }
    });
}

function entrarNoGrupoDireto(codigo) {
    const fd = new FormData();
    fd.append('action', 'entrar_grupo');
    fd.append('codigo_convite', codigo);

    fetch('../../back/api_amigos.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (typeof opusToast === 'function') {
                opusToast(data.mensagem, 'success');
            } else {
                alert('🎉 ' + data.mensagem);
            }
            carregarGrupos();
        } else {
            if (typeof opusToast === 'function') {
                opusToast(data.mensagem || 'Erro ao entrar no grupo.', 'error');
            } else {
                alert(data.mensagem || 'Erro ao entrar no grupo.');
            }
        }
    });
}

// ── 4. MODAIS E CRIAÇÃO DE GRUPO ─────────────────────────────────
function abrirModalCriarGrupo() {
    document.getElementById('modalCriarGrupo').classList.add('active');
    carregarAmigosParaModalCriarGrupo();
}

function fecharModalCriarGrupo() {
    document.getElementById('modalCriarGrupo').classList.remove('active');
    // Remove banner de sucesso anterior para próxima abertura
    const old = document.querySelector('#modalCriarGrupo .modal-content > div[style*="58cc02"]');
    if (old) old.remove();
}


function carregarAmigosParaModalCriarGrupo() {
    fetch('../../back/api_amigos.php?action=listar_amigos&tipo=seguindo')
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('listaAmigosParaGrupo');
        if (!container) return;

        const amigos = data.amigos || [];
        if (amigos.length === 0) {
            container.innerHTML = `<div style="text-align: center; color: #94a3b8; font-size: 0.85rem; padding: 15px;">
                Você ainda não tem amigos para adicionar. Seus amigos poderão entrar depois pelo código ou link!
            </div>`;
            return;
        }

        container.innerHTML = '';
        amigos.forEach(a => {
            container.innerHTML += `
                <label class="friend-checkbox-item">
                    <input type="checkbox" name="membros_grupo" value="${a.id}">
                    <img src="${a.foto_perfil}" alt="${a.nome}">
                    <div class="friend-checkbox-info">
                        <strong>${a.nome}</strong>
                        <span>⚡ ${a.xp} XP · 🔥 ${a.dias_fogo}d</span>
                    </div>
                </label>`;
        });
    });
}

function confirmarCriarGrupo() {
    const nomeInput = document.getElementById('inputNomeGrupo');
    const nome = nomeInput.value.trim();
    if (!nome) {
        nomeInput.style.borderColor = '#ff4b4b';
        nomeInput.placeholder = '⚠️ Digite um nome para o grupo!';
        nomeInput.focus();
        setTimeout(() => {
            nomeInput.style.borderColor = '#2e2e42';
            nomeInput.placeholder = 'Ex: Esquadrão Java Devs';
        }, 2500);
        return;
    }

    const checkboxes = document.querySelectorAll('#listaAmigosParaGrupo input[name="membros_grupo"]:checked');
    const fd = new FormData();
    fd.append('action', 'criar_grupo');
    fd.append('nome_grupo', nome);
    checkboxes.forEach(cb => fd.append('membros_ids[]', cb.value));

    const btnCriar = document.querySelector('#modalCriarGrupo .btn-save');
    if (btnCriar) {
        btnCriar.disabled = true;
        btnCriar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Criando...';
    }

    fetch('../../back/api_amigos.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (btnCriar) {
            btnCriar.disabled = false;
            btnCriar.innerHTML = 'Criar Grupo';
        }

        if (data.success) {
            // Exibe o código no modal antes de fechar
            const modalContent = document.querySelector('#modalCriarGrupo .modal-content');
            if (modalContent) {
                const successBanner = document.createElement('div');
                successBanner.style.cssText = `
                    background: rgba(88,204,2,0.12);
                    border: 2px solid #58cc02;
                    border-radius: 14px;
                    padding: 14px 18px;
                    margin-bottom: 16px;
                    text-align: center;
                    animation: fadeInTab 0.3s ease;
                `;
                successBanner.innerHTML = `
                    <div style="font-size:1.6rem; margin-bottom:6px;">🎉</div>
                    <div style="color:#58cc02; font-weight:800; font-size:1rem;">Grupo criado com sucesso!</div>
                    <div style="color:#94a3b8; font-size:0.85rem; margin: 6px 0;">Código de convite:</div>
                    <div style="font-family:monospace; font-size:1.4rem; font-weight:900; color:#ffc800; letter-spacing:2px;">${data.codigo_convite}</div>
                    <button onclick="navigator.clipboard.writeText('${data.codigo_convite}')" style="
                        margin-top: 10px; background: #ffc800; border: none; border-bottom: 3px solid #cc9b00;
                        color: #3a2800; font-weight: 800; font-size: 0.82rem; padding: 7px 16px;
                        border-radius: 10px; cursor: pointer;
                    "><i class='fa-solid fa-copy'></i> Copiar Código</button>
                `;
                modalContent.insertBefore(successBanner, modalContent.firstChild);
            }

            nomeInput.value = '';
            document.querySelectorAll('#listaAmigosParaGrupo input[type="checkbox"]').forEach(cb => cb.checked = false);

            setTimeout(() => {
                fecharModalCriarGrupo();
                carregarGrupos();
            }, 2800);
        } else {
            if (typeof opusToast === 'function') {
                opusToast(data.mensagem || 'Erro ao criar grupo. Tente novamente.', 'error');
            } else {
                alert(data.mensagem || 'Erro ao criar grupo. Tente novamente.');
            }
        }
    })
    .catch(() => {
        if (btnCriar) { btnCriar.disabled = false; btnCriar.innerHTML = 'Criar Grupo'; }
        if (typeof opusToast === 'function') {
            opusToast('Erro de conexão. Verifique sua internet e tente novamente.', 'error');
        } else {
            alert('Erro de conexão. Verifique sua internet e tente novamente.');
        }
    });
}


function entrarGrupoPorCodigo() {
    const codigo = document.getElementById('inputCodigoGrupo').value.trim();
    if (!codigo) {
        if (typeof opusToast === 'function') {
            opusToast('Digite o código do grupo para entrar!', 'warning');
        } else {
            alert('Digite o código do grupo para entrar!');
        }
        return;
    }

    const fd = new FormData();
    fd.append('action', 'entrar_grupo');
    fd.append('codigo_convite', codigo);

    fetch('../../back/api_amigos.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (typeof opusToast === 'function') {
                opusToast(data.mensagem, 'success');
            } else {
                alert('🎉 ' + data.mensagem);
            }
            document.getElementById('inputCodigoGrupo').value = '';
            carregarGrupos();
        } else {
            if (typeof opusToast === 'function') {
                opusToast(data.mensagem || 'Não foi possível entrar no grupo.', 'error');
            } else {
                alert(data.mensagem || 'Não foi possível entrar no grupo.');
            }
        }
    });
}

// ── 5. MODAL VISUALIZAR BATALHA DO GRUPO ─────────────────────────
function visualizarGrupo(grupoId) {
    fetch(`../../back/api_amigos.php?action=detalhes_grupo&grupo_id=${grupoId}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.grupo) {
            if (typeof opusToast === 'function') {
                opusToast('Não foi possível carregar os detalhes do grupo.', 'error');
            } else {
                alert('Não foi possível carregar os detalhes do grupo.');
            }
            return;
        }

        const g = data.grupo;
        document.getElementById('visGrupoTitulo').innerText = '🛡️ ' + g.nome;
        document.getElementById('visGrupoCodigo').innerText = 'CÓDIGO: ' + g.codigo_convite;

        // Configura botões de compartilhamento
        document.getElementById('btnShareZapGroup').onclick = function() {
            enviarConviteZapGrupo(g.codigo_convite, g.nome);
        };
        document.getElementById('btnShareChatGroup').onclick = function() {
            enviarConviteChatGrupo(g.codigo_convite, g.nome);
        };

        // Renderiza lista de membros e classificação do duelo
        const containerMembros = document.getElementById('visGrupoMembrosList');
        containerMembros.innerHTML = '';

        if (g.membros && g.membros.length > 0) {
            g.membros.forEach(m => {
                const isVoceClass = m.is_voce ? 'is-voce' : '';
                const rankClass = `pos-${m.posicao}`;

                containerMembros.innerHTML += `
                    <div class="group-member-item ${isVoceClass}">
                        <div class="gm-rank ${rankClass}">#${m.posicao}</div>
                        <div class="gm-user-box">
                            <img src="${m.foto_perfil}" class="gm-avatar" alt="${m.nome}">
                            <div class="gm-info">
                                <h5>${m.nome} ${m.is_voce ? '(Você)' : ''}</h5>
                                <span>🎯 ${m.acertos_hoje} acertos hoje · ⚡ ${m.xp} XP · 🔥 ${m.dias_fogo}d</span>
                            </div>
                        </div>
                        ${!m.is_voce ? `
                            <button class="btn-duel-member" onclick="duelarComMembro(${m.id})">
                                ⚔️ Duelar VS
                            </button>` : ''}
                    </div>`;
            });
        }

        document.getElementById('modalVisualizarGrupo').classList.add('active');
    });
}

function fecharModalVisualizarGrupo() {
    document.getElementById('modalVisualizarGrupo').classList.remove('active');
}

function duelarComMembro(membroId) {
    fecharModalVisualizarGrupo();
    abrirTelaAmigos('batalha');
    carregarBatalha(membroId);
}

// ── 6. NOVA NAVEGAÇÃO EM GRADE ──────────────────────────────────
function abrirTelaAmigos(tela) {
    document.getElementById('amigosMenuGrid').style.display = 'none';
    document.getElementById('btnVoltarMenuAmigos').style.display = 'inline-flex';
    
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    if (tela === 'grupos') {
        document.getElementById('tabContentGrupos').classList.add('active');
        carregarGrupos();
    } else if (tela === 'batalha') {
        document.getElementById('tabContentBatalha').classList.add('active');
        carregarBatalhas1v1();
    } else if (tela === 'encontrar') {
        document.getElementById('tabContentEncontrar').classList.add('active');
        carregarRecomendacoesLiga();
    } else if (tela === 'chat') {
        document.getElementById('tabContentChat').classList.add('active');
        carregarPedidosPendentes();
        carregarAmigosChat();
    }
}

function voltarMenuAmigos() {
    document.getElementById('amigosMenuGrid').style.display = 'grid';
    document.getElementById('btnVoltarMenuAmigos').style.display = 'none';
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
}

// ── 7. ENCONTRAR AMIGOS E RECOMENDAÇÕES ────────────────────────
function buscarAmigosExato() {
    const q = document.getElementById('inputBuscaAmigo').value.trim();
    if (!q) return;

    fetch(`../../back/api_amigos.php?action=buscar_usuarios&q=${encodeURIComponent(q)}`)
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('containerResultadoBusca');
        if (!data.success || !data.usuarios || data.usuarios.length === 0) {
            container.innerHTML = '<div style="color:#ff4b4b; margin-top: 10px;">Nenhum usuário encontrado com este E-mail ou ID.</div>';
            return;
        }
        renderizarCardsAmigos(data.usuarios, container);
    });
}

function carregarRecomendacoesLiga() {
    fetch('../../back/api_amigos.php?action=buscar_recomendacoes')
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('containerRecomendacoesLiga');
        if (data.success && data.usuarios) {
            renderizarCardsAmigos(data.usuarios, container);
        }
    });
}

function renderizarCardsAmigos(usuarios, container) {
    container.innerHTML = '';
    usuarios.forEach(u => {
        let btnHtml = '';
        if (u.status_conexao === 'aceito') {
            btnHtml = `<button class="btn-follow following" disabled>Amigos</button>`;
        } else if (u.status_conexao === 'pendente') {
            btnHtml = `<button class="btn-follow following" disabled>Pendente</button>`;
        } else {
            btnHtml = `<button class="btn-follow" onclick="enviarPedidoConexao(${u.id}, this)">Conectar</button>`;
        }

        container.innerHTML += `
            <div class="friend-card">
                <div class="friend-info-left">
                    <img src="${u.foto_perfil}" class="friend-avatar">
                    <div class="friend-details">
                        <h4>${u.nome}</h4>
                        <span>⚡ ${u.xp} XP</span>
                    </div>
                </div>
                ${btnHtml}
            </div>`;
    });
}

function enviarPedidoConexao(targetId, btnElement) {
    const fd = new FormData();
    fd.append('action', 'solicitar_conexao');
    fd.append('target_id', targetId);

    fetch('../../back/api_amigos.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btnElement.className = 'btn-follow following';
            btnElement.innerText = 'Pendente';
            btnElement.disabled = true;
            if (typeof opusToast === 'function') opusToast(data.mensagem, 'success');
            else alert(data.mensagem);
        } else {
            if (typeof opusToast === 'function') opusToast(data.mensagem, 'warning');
            else alert(data.mensagem);
        }
    });
}

// ── 8. CHAT E PEDIDOS ──────────────────────────────────────────
function carregarPedidosPendentes() {
    fetch('../../back/api_amigos.php?action=listar_pedidos')
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('listaPedidosPendentes');
        if (!data.success || data.pedidos.length === 0) {
            container.innerHTML = '<div style="color:var(--text-muted); font-size:0.85rem;">Nenhum pedido pendente.</div>';
            return;
        }
        container.innerHTML = '';
        data.pedidos.forEach(p => {
            container.innerHTML += `
                <div class="pedido-card" id="pedidoCard_${p.pedido_id}">
                    <img src="${p.foto_perfil}" class="pedido-avatar">
                    <div class="pedido-info">
                        <h5>${p.nome}</h5>
                        <span style="font-size:0.75rem; color:var(--text-muted)">Quer conectar!</span>
                    </div>
                    <div class="pedido-actions">
                        <button class="btn-pedido aceitar" onclick="responderPedido(${p.pedido_id}, 'aceitar')"><i class="fa-solid fa-check"></i></button>
                        <button class="btn-pedido recusar" onclick="responderPedido(${p.pedido_id}, 'recusar')"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>`;
        });
    });
}

function responderPedido(pedidoId, resposta) {
    const fd = new FormData();
    fd.append('action', 'responder_pedido');
    fd.append('pedido_id', pedidoId);
    fd.append('resposta', resposta);

    fetch('../../back/api_amigos.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`pedidoCard_${pedidoId}`).remove();
            if (resposta === 'aceitar') carregarAmigosChat();
        }
    });
}

function carregarAmigosChat() {
    fetch('../../back/api_amigos.php?action=listar_amigos&tipo=seguindo')
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('listaAmigosChat');
        if (!data.success || data.amigos.length === 0) {
            container.innerHTML = '<div style="color:var(--text-muted); font-size:0.85rem;">Adicione amigos para conversar.</div>';
            return;
        }
        container.innerHTML = '';
        data.amigos.forEach(a => {
            container.innerHTML += `
                <div class="amigo-chat-card" onclick="abrirChatComAmigo(${a.id}, '${a.nome}', '${a.foto_perfil}', this)">
                    <img src="${a.foto_perfil}" class="amigo-chat-avatar">
                    <div class="amigo-chat-info">
                        <h5>${a.nome}</h5>
                    </div>
                </div>`;
        });
    });
}

let amigoChatAtual = 0;
let chatInterval = null;

function abrirChatComAmigo(id, nome, foto, cardElem) {
    document.querySelectorAll('.amigo-chat-card').forEach(c => c.classList.remove('active'));
    if (cardElem) cardElem.classList.add('active');

    amigoChatAtual = id;
    const chatArea = document.getElementById('chatMainArea');
    chatArea.innerHTML = `
        <div class="chat-header">
            <img src="${foto}" style="width:36px; height:36px; border-radius:50%; object-fit:contain; background:#12121a;">
            <h4 style="margin:0; color:#fff; font-size:1rem;">${nome}</h4>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div style="text-align:center; color:var(--text-muted); margin-top:20px;">Carregando mensagens...</div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="inputMsgChat" placeholder="Digite uma mensagem..." onkeyup="if(event.key==='Enter') enviarMensagemChat()">
            <button onclick="enviarMensagemChat()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    `;

    carregarMensagensChat(id);
    if (chatInterval) clearInterval(chatInterval);
    chatInterval = setInterval(() => carregarMensagensChat(id, true), 3000);
}

function carregarMensagensChat(id, silent = false) {
    if (amigoChatAtual !== id) return;
    fetch(`../../back/api_amigos.php?action=listar_mensagens&amigo_id=${id}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        const container = document.getElementById('chatMessages');
        if (!container) return;
        
        let html = '';
        data.mensagens.forEach(m => {
            const cls = m.is_mine ? 'sent' : 'received';
            let iconLeitura = '';
            if (m.is_mine) {
                if (m.status === 'lido') iconLeitura = '<i class="fa-solid fa-check-double" style="color:#58cc02"></i>';
                else if (m.status === 'entregue') iconLeitura = '<i class="fa-solid fa-check-double"></i>';
                else iconLeitura = '<i class="fa-solid fa-check"></i>';
            }
            html += `
                <div class="chat-bubble ${cls}">
                    ${m.mensagem}
                    <div class="chat-status">${iconLeitura}</div>
                </div>`;
        });
        
        if (html === '') html = '<div style="text-align:center; color:var(--text-muted); margin-top:20px;">Diga olá!</div>';
        
        const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 10;
        container.innerHTML = html;
        if (isScrolledToBottom || !silent) container.scrollTop = container.scrollHeight;
    });
}

function enviarMensagemChat() {
    if (amigoChatAtual <= 0) return;
    const input = document.getElementById('inputMsgChat');
    const msg = input.value.trim();
    if (!msg) return;

    input.value = '';
    const fd = new FormData();
    fd.append('action', 'enviar_mensagem');
    fd.append('destinatario_id', amigoChatAtual);
    fd.append('mensagem', msg);

    fetch('../../back/api_amigos.php', { method: 'POST', body: fd })
    .then(() => carregarMensagensChat(amigoChatAtual));
}

// Sobrescrever provocarRival para enviar via chat
window.provocarRival = function() {
    const rivalNome = document.getElementById('p2Nome').innerText;
    // Precisamos do ID do rival. Ele não está salvo numa variável global, mas podemos pegar da div do player.
    // Vamos buscar a lista de batalhas e pegar o rivalId do card selecionado.
    const selectedCard = document.querySelector('.batalha-1v1-card.selected');
    if (selectedCard) {
        const onclickAttr = selectedCard.getAttribute('onclick'); // Ex: selecionarRival1v1(4, this)
        const match = onclickAttr.match(/\d+/);
        if (match) {
            const rivalId = parseInt(match[0]);
            const fd = new FormData();
            fd.append('action', 'enviar_mensagem');
            fd.append('destinatario_id', rivalId);
            fd.append('mensagem', `Seu duelo tá fraco, ${rivalNome}! Quero ver me passar! 😎🔥`);
            fetch('../../back/api_amigos.php', { method: 'POST', body: fd });
            if (typeof opusAlerta === 'function') {
                opusAlerta({
                    tipo: 'success',
                    titulo: 'Provocação Enviada! 🔥',
                    mensagem: `Você provocou <strong>${rivalNome}</strong>! A mensagem foi enviada no chat.`
                });
            } else {
                alert(`🔥 Provocação enviada no chat para ${rivalNome}!`);
            }
            return;
        }
    }
    alert(`🔥 Provocação enviada para ${rivalNome}!`);
};
