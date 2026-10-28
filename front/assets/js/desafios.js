/**
 * DESAFIOS.JS - Interatividade da Aba de Desafios & Projetos
 */

document.addEventListener('DOMContentLoaded', () => {
    const tabBtns = document.querySelectorAll('.desafios-tab-btn');
    const cards = document.querySelectorAll('.desafio-card');
    const modalBloqueado = document.getElementById('modalBloqueado');

    // Filtros de Categoria/Status
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            if (typeof OpusAudio !== 'undefined') {
                OpusAudio.playClick();
            }

            const filtro = btn.dataset.filtro;

            cards.forEach(card => {
                const status = card.dataset.status;
                if (filtro === 'todos') {
                    card.style.display = 'flex';
                } else if (filtro === 'disponivel') {
                    card.style.display = (status === 'disponivel') ? 'flex' : 'none';
                } else if (filtro === 'concluido') {
                    card.style.display = (status === 'concluido') ? 'flex' : 'none';
                } else if (filtro === 'bloqueado') {
                    card.style.display = (status === 'bloqueado') ? 'flex' : 'none';
                }
            });
        });
    });

    // Fechar modal ao clicar fora da caixa
    if (modalBloqueado) {
        modalBloqueado.addEventListener('click', (e) => {
            if (e.target === modalBloqueado) {
                fecharModalBloqueado();
            }
        });
    }
});

/**
 * Abre modal didático informando o requisito necessário para desbloquear o desafio
 */
function abrirModalBloqueado(titulo, requisitoTitulo, secaoReq, secaoAtual) {
    if (typeof OpusAudio !== 'undefined') {
        OpusAudio.playError();
    }

    const modal = document.getElementById('modalBloqueado');
    const elTitulo = document.getElementById('modalTitulo');
    const elDesc = document.getElementById('modalDesc');

    if (!modal) return;

    elTitulo.innerHTML = `<i class="fa-solid fa-lock" style="color: #f59e0b;"></i> ${titulo}`;
    elDesc.innerHTML = `
        Para desbloquear este projeto, você precisa primeiro aprender o conteúdo de <strong>${requisitoTitulo}</strong>!<br><br>
        Você está atualmente na <strong>Seção ${secaoAtual}</strong> e o desafio é liberado após a <strong>Seção ${secaoReq}</strong>.<br>
        Complete as lições na Trilha Principal para liberar e começar a codificar!
    `;

    modal.style.display = 'flex';
}

/**
 * Fecha o modal de bloqueio
 */
function fecharModalBloqueado() {
    const modal = document.getElementById('modalBloqueado');
    if (modal) {
        modal.style.display = 'none';
    }
}
