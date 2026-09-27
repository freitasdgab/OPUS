/**
 * ==========================================================================
 * OPUS - SISTEMA DE AVISOS E NOTIFICAÇÕES PERSONALIZADAS (ALERTS & TOASTS)
 * Substitui os alertas nativos e erros do sistema por avisos estilizados na tela.
 * ==========================================================================
 */

(function() {
    'use strict';

    // ── Garantir que o container de Toasts exista no DOM ────────────────────
    function getOrCreateToastContainer() {
        let container = document.getElementById('opus-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'opus-toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    // ── Garantir que o Modal de Alerta exista no DOM ─────────────────────────
    function getOrCreateModalContainer() {
        let modalOverlay = document.getElementById('opus-modal-overlay');
        if (!modalOverlay) {
            modalOverlay = document.createElement('div');
            modalOverlay.id = 'opus-modal-overlay';
            modalOverlay.className = 'opus-modal-overlay';
            modalOverlay.innerHTML = `
                <div class="opus-modal-box" id="opus-modal-box">
                    <button class="opus-modal-close-btn" id="opus-modal-close-btn" title="Fechar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <div class="opus-modal-icon-wrap" id="opus-modal-icon-wrap">
                        <i class="fa-solid fa-info" id="opus-modal-icon"></i>
                    </div>
                    <h3 class="opus-modal-title" id="opus-modal-title">Aviso</h3>
                    <div class="opus-modal-message" id="opus-modal-message"></div>
                    <ul class="opus-modal-details-list" id="opus-modal-details-list" style="display: none;"></ul>
                    <div class="opus-modal-copy-box" id="opus-modal-copy-box" style="display: none;">
                        <input type="text" class="opus-modal-copy-input" id="opus-modal-copy-input" readonly>
                        <button type="button" class="opus-modal-copy-btn" id="opus-modal-copy-btn">
                            <i class="fa-solid fa-copy"></i> Copiar
                        </button>
                    </div>
                    <div class="opus-modal-actions" id="opus-modal-actions">
                        <button type="button" class="opus-modal-btn opus-modal-btn-secondary" id="opus-modal-btn-cancel" style="display: none;">CANCELAR</button>
                        <button type="button" class="opus-modal-btn opus-modal-btn-primary" id="opus-modal-btn-confirm">ENTENDIDO</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modalOverlay);
        }
        return modalOverlay;
    }

    // ── Mapa de Ícones Padrão ───────────────────────────────────────────────
    const ICON_MAP = {
        success: 'fa-solid fa-check',
        error: 'fa-solid fa-triangle-exclamation',
        warning: 'fa-solid fa-exclamation',
        info: 'fa-solid fa-circle-info',
    };

    const TITLE_MAP = {
        success: 'Sucesso!',
        error: 'Atenção!',
        warning: 'Aviso Importante',
        info: 'Informação',
    };

    // ── TOAST PERSONALIZADO ──────────────────────────────────────────────────
    /**
     * Exibe uma notificação flutuante temporária.
     * @param {string} mensagem - Texto da notificação
     * @param {string} tipo - 'success' | 'error' | 'warning' | 'info'
     * @param {number} duracao - Tempo em ms (padrão: 4500)
     * @param {string|null} titulo - Título customizado opcional
     */
    function opusToast(mensagem, tipo = 'info', duracao = 4500, titulo = null) {
        const container = getOrCreateToastContainer();
        const toast = document.createElement('div');
        const tipoFormatado = ['success', 'error', 'warning', 'info'].includes(tipo) ? tipo : 'info';
        
        toast.className = `opus-toast toast-${tipoFormatado}`;

        const iconClass = ICON_MAP[tipoFormatado] || ICON_MAP.info;
        const toastTitulo = titulo || TITLE_MAP[tipoFormatado];

        toast.innerHTML = `
            <div class="opus-toast-icon">
                <i class="${iconClass}"></i>
            </div>
            <div class="opus-toast-content">
                <div class="opus-toast-title">${toastTitulo}</div>
                <div class="opus-toast-message">${mensagem}</div>
            </div>
            <button class="opus-toast-close" title="Fechar">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="opus-toast-progress"></div>
        `;

        container.appendChild(toast);

        // Animação de entrada
        requestAnimationFrame(() => {
            toast.classList.add('opus-toast-show');
            const progress = toast.querySelector('.opus-toast-progress');
            if (progress && duracao > 0) {
                progress.style.transition = `transform ${duracao}ms linear`;
                progress.style.transform = 'scaleX(0)';
            }
        });

        let timer = null;

        function closeToast() {
            if (timer) clearTimeout(timer);
            toast.classList.remove('opus-toast-show');
            toast.classList.add('opus-toast-hide');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 350);
        }

        toast.querySelector('.opus-toast-close').addEventListener('click', closeToast);

        if (duracao > 0) {
            timer = setTimeout(closeToast, duracao);
        }

        return toast;
    }

    // ── MODAL DE ALERTA PERSONALIZADO ────────────────────────────────────────
    /**
     * Exibe um modal de aviso moderno no centro da tela.
     * @param {Object|string} options - Configurações do aviso ou mensagem direta
     */
    function opusAlerta(options) {
        if (typeof options === 'string') {
            options = { mensagem: options };
        }

        const {
            titulo = null,
            mensagem = '',
            tipo = 'info',
            icone = null,
            botaoTexto = 'ENTENDIDO',
            onConfirm = null,
            cancelTexto = null,
            onCancel = null,
            detalhes = null,
            linkCopia = null,
        } = options;

        const tipoFormatado = ['success', 'error', 'warning', 'info'].includes(tipo) ? tipo : 'info';
        const overlay = getOrCreateModalContainer();
        const box = document.getElementById('opus-modal-box');
        const iconWrap = document.getElementById('opus-modal-icon-wrap');
        const iconElem = document.getElementById('opus-modal-icon');
        const titleElem = document.getElementById('opus-modal-title');
        const msgElem = document.getElementById('opus-modal-message');
        const detailsElem = document.getElementById('opus-modal-details-list');
        const copyBoxElem = document.getElementById('opus-modal-copy-box');
        const copyInputElem = document.getElementById('opus-modal-copy-input');
        const copyBtnElem = document.getElementById('opus-modal-copy-btn');
        const btnConfirm = document.getElementById('opus-modal-btn-confirm');
        const btnCancel = document.getElementById('opus-modal-btn-cancel');
        const btnClose = document.getElementById('opus-modal-close-btn');

        // Reset classes
        box.className = `opus-modal-box modal-type-${tipoFormatado}`;

        // Configura Ícone
        const iconClass = icone || ICON_MAP[tipoFormatado] || ICON_MAP.info;
        iconElem.className = iconClass;

        // Configura Título e Mensagem
        titleElem.textContent = titulo || TITLE_MAP[tipoFormatado];
        msgElem.innerHTML = mensagem;

        // Lista de Detalhes (se fornecida, ex: lista de erros)
        if (Array.isArray(detalhes) && detalhes.length > 0) {
            detailsElem.innerHTML = detalhes.map(item => `<li><i class="fa-solid fa-circle-xmark"></i> <span>${item}</span></li>`).join('');
            detailsElem.style.display = 'flex';
        } else {
            detailsElem.style.display = 'none';
            detailsElem.innerHTML = '';
        }

        // Link de Cópia (se fornecido)
        if (linkCopia) {
            copyInputElem.value = linkCopia;
            copyBoxElem.style.display = 'flex';
            copyBtnElem.onclick = function() {
                copyInputElem.select();
                navigator.clipboard.writeText(linkCopia).then(() => {
                    copyBtnElem.innerHTML = '<i class="fa-solid fa-check"></i> Copiado!';
                    setTimeout(() => {
                        copyBtnElem.innerHTML = '<i class="fa-solid fa-copy"></i> Copiar';
                    }, 2000);
                }).catch(() => {
                    document.execCommand('copy');
                    copyBtnElem.innerHTML = '<i class="fa-solid fa-check"></i> Copiado!';
                });
            };
        } else {
            copyBoxElem.style.display = 'none';
        }

        // Botão de Confirmação
        btnConfirm.textContent = botaoTexto;

        // Botão de Cancelamento
        if (cancelTexto) {
            btnCancel.textContent = cancelTexto;
            btnCancel.style.display = 'inline-flex';
        } else {
            btnCancel.style.display = 'none';
        }

        return new Promise((resolve) => {
            function fecharModal(confirmado) {
                overlay.classList.remove('opus-modal-active');
                if (confirmado && typeof onConfirm === 'function') onConfirm();
                if (!confirmado && typeof onCancel === 'function') onCancel();
                resolve(confirmado);
            }

            btnConfirm.onclick = () => fecharModal(true);
            btnCancel.onclick = () => fecharModal(false);
            btnClose.onclick = () => fecharModal(false);

            overlay.onclick = (e) => {
                if (e.target === overlay) fecharModal(false);
            };

            // Abre o modal
            overlay.classList.add('opus-modal-active');
        });
    }

    // ── Atalhos Globais ──────────────────────────────────────────────────────
    window.opusToast = opusToast;
    window.opusAlerta = opusAlerta;
    window.mostrarAviso = (msg, tipo = 'info', titulo = null) => {
        if (tipo === 'success') {
            opusToast(msg, 'success', 4000, titulo);
        } else {
            opusAlerta({ mensagem: msg, tipo, titulo });
        }
    };
    window.opusSucesso = (msg, titulo = 'Sucesso!') => opusAlerta({ mensagem: msg, tipo: 'success', titulo });
    window.opusErro = (msg, titulo = 'Atenção!') => opusAlerta({ mensagem: msg, tipo: 'error', titulo });
    window.opusAviso = (msg, titulo = 'Aviso') => opusAlerta({ mensagem: msg, tipo: 'warning', titulo });
    window.opusInfo = (msg, titulo = 'Informação') => opusAlerta({ mensagem: msg, tipo: 'info', titulo });

    // ── Substituição Segura do window.alert Padrão do Navegador ───────────────
    // Qualquer script legado que chame alert("...") agora mostrará o modal do OPUS!
    const _nativeAlert = window.alert;
    window.alert = function(message) {
        let msgStr = String(message || '');
        let tipo = 'info';
        let titulo = 'Aviso';

        if (msgStr.includes('❌') || msgStr.toLowerCase().includes('erro') || msgStr.toLowerCase().includes('incorret')) {
            tipo = 'error';
            titulo = 'Atenção';
        } else if (msgStr.includes('✅') || msgStr.includes('🎉') || msgStr.toLowerCase().includes('sucesso') || msgStr.toLowerCase().includes('excelente')) {
            tipo = 'success';
            titulo = 'Muito Bem!';
        } else if (msgStr.includes('⚠️') || msgStr.includes('🔥') || msgStr.toLowerCase().includes('aviso')) {
            tipo = 'warning';
            titulo = 'Aviso';
        }

        // Limpa emojis decorativos redundantes do corpo se houver
        const msgFormatada = msgStr.replace(/^[✅❌⚠️🎉🔥ℹ️\s]+/, '').replace(/\n/g, '<br>');

        opusAlerta({
            mensagem: msgFormatada,
            tipo: tipo,
            titulo: titulo
        });
    };

    // ── Detecção Automática de Mensagens na URL (?erro=... / ?sucesso=...) ─────
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        let paramErro = urlParams.get('erro') || urlParams.get('error');
        let paramSucesso = urlParams.get('sucesso') || urlParams.get('success');
        let paramAviso = urlParams.get('aviso') || urlParams.get('warning') || urlParams.get('msg');

        if (paramErro) {
            setTimeout(() => {
                opusAlerta({
                    mensagem: decodeURIComponent(paramErro),
                    tipo: 'error',
                    titulo: 'Erro ao Prosseguir'
                });
            }, 300);
        } else if (paramSucesso) {
            setTimeout(() => {
                opusToast(decodeURIComponent(paramSucesso), 'success', 5000);
            }, 300);
        } else if (paramAviso) {
            setTimeout(() => {
                opusAlerta({
                    mensagem: decodeURIComponent(paramAviso),
                    tipo: 'warning',
                    titulo: 'Aviso'
                });
            }, 300);
        }

        // Limpa os parâmetros de erro/sucesso da URL para não reaparecer no refresh
        if (paramErro || paramSucesso || paramAviso) {
            urlParams.delete('erro');
            urlParams.delete('error');
            urlParams.delete('sucesso');
            urlParams.delete('success');
            urlParams.delete('aviso');
            urlParams.delete('warning');
            urlParams.delete('msg');
            const newQuery = urlParams.toString();
            const newUrl = window.location.pathname + (newQuery ? '?' + newQuery : '') + window.location.hash;
            window.history.replaceState({}, document.title, newUrl);
        }
    });

})();
