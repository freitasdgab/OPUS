/**
 * OPUS - Tour Guiado Interativo com o Mascote Opi (Estilo Duolingo)
 * Guia o aluno automaticamente por todas as telas do site (Aprender, Desafios,
 * Ranking, Ligas, Dicionário, Chat e Perfil) com balão de tamanho fixo,
 * spotlight animado e o mascote Opi azul em todos os passos.
 */
(function (window, document) {
    'use strict';

    const cfg = window.OPUS_TOUR_CONFIG || {};
    const USER_ID = cfg.userId || 0;
    const USER_NAME = cfg.userName || 'Estudante';
    const CURRENT_PAGE = cfg.page || '';
    const IMG_BASE = cfg.imgBase || '../assets/img/';
    const API_URL = cfg.apiUrl || '../../back/api/tour_status.php';

    const STORAGE_KEY_MAIN = 'opus_tour_principal_u' + USER_ID;
    const STORAGE_KEY_PERFIL = 'opus_tour_perfil_u' + USER_ID;
    const STORAGE_KEY_LICAO1 = 'opus_tour_licao1_u' + USER_ID;
    const STORAGE_KEY_RESULTADO1 = 'opus_tour_resultado1_u' + USER_ID;
    const STORAGE_KEY_FIM_CURSO = 'opus_tour_fim_curso_u' + USER_ID;
    const SESSION_KEY_CONTINUO = 'opus_tour_continuo_u' + USER_ID;

    const FULL_TOUR_STAGES = [
        { page: 'dashboard.php',  label: 'Aprender',   count: 6 },
        { page: 'desafios.php',   label: 'Desafios',   count: 3 },
        { page: 'ranking.php',    label: 'Ranking',    count: 3 },
        { page: 'ligas.php',      label: 'Ligas',      count: 3 },
        { page: 'dicionario.php', label: 'Dicionário', count: 3 },
        { page: 'chat.php',       label: 'Chat',       count: 3 },
        { page: 'perfil.php',     label: 'Perfil',     count: 6 }
    ];
    const FULL_TOUR_TOTAL_STEPS = FULL_TOUR_STAGES.reduce((sum, s) => sum + s.count, 0);

    const MASCOT_SCALES = {
        'opi_ranking.png': 0.98,
        'opi_dicionario.png': 1.0,
        'vistodecimaazul.png': 1.14,
        'azulcarta.png': 1.28,
        'explicando2.png': 1.32,
        'acertoumetade.png': 1.32
    };

    const root = document.getElementById('opiTourRoot');
    if (!root) return;

    const spotlight = document.getElementById('opiTourSpotlight');
    const pointerTag = document.getElementById('opiTourPointerTag');
    const pointerLabel = document.getElementById('opiTourPointerLabel');
    const stage = document.getElementById('opiTourStage');
    const mascotWrap = document.getElementById('opiTourMascotWrap');
    const mascotImg = document.getElementById('opiTourMascotImg');
    const badgeEl = document.getElementById('opiTourBadge');
    const counterEl = document.getElementById('opiTourCounter');
    const titleEl = document.getElementById('opiTourTitle');
    const bubbleEl = document.getElementById('opiTourBubble');
    const trophyCardEl = document.getElementById('opiTourTrophyCard');
    const trophyImgEl = document.getElementById('opiTourTrophyImg');
    const trophyNameEl = document.getElementById('opiTourTrophyName');
    const trophyDescEl = document.getElementById('opiTourTrophyDesc');
    const speechBox = document.getElementById('opiTourSpeechBox');
    const textEl = document.getElementById('opiTourText');
    const progressFill = document.getElementById('opiTourProgressFill');
    const dotsContainer = document.getElementById('opiTourDots');

    const skipBtn = document.getElementById('opiTourSkipBtn');
    const prevBtn = document.getElementById('opiTourPrevBtn');
    const nextBtn = document.getElementById('opiTourNextBtn');
    const nextLabel = document.getElementById('opiTourNextLabel');
    const extraBtn = document.getElementById('opiTourExtraBtn');
    const closeBtn = document.getElementById('opiTourCloseBtn');

    // Estado interno
    let isOpen = false;
    let activeTourType = 'principal';
    let activeTourMode = 'site'; // 'site' | 'perfil' | 'licao1_expl' | 'licao1_quiz' | 'resultado1' | 'trofeu' | 'resultado_manual'
    let activePage = CURRENT_PAGE || 'dashboard.php';
    let isFullTour = false;
    let stepOffset = 0;
    let overallTotal = 0;
    let isTransitioning = false;
    let stepRenderToken = 0;
    let steps = [];
    let currentIndex = 0;
    let typingTimer = null;
    let isTyping = false;
    let currentFullText = '';
    let rafId = null;
    let lastFocusedEl = null;
    let licao1ExplDoneInSession = false;
    let licao1SkippedInSession = false;
    let forceLicao1QuizTour = false;

    const prefersReducedMotion = () =>
        window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function getDefaultStageOffset(page) {
        let offset = 0;
        for (const s of FULL_TOUR_STAGES) {
            if (s.page === page) return offset;
            offset += s.count;
        }
        return 0;
    }

    function resolvePageFromTarget(target) {
        if (!target || target === 'principal') return 'dashboard.php';
        if (target === 'perfil') return 'perfil.php';
        return target;
    }

    // =========================================================================
    // ROTEIROS POR PÁGINA (100% MASCOTE AZUL + NAVEGAÇÃO AUTOMÁTICA)
    // =========================================================================
    function buildDashboardSteps(fullMode) {
        const list = [
            {
                selector: null,
                badge: fullMode ? 'TOUR COMPLETO OPUS' : 'ABA APRENDER',
                title: 'Oi, ' + USER_NAME + '! Eu sou o Opi!',
                text: fullMode
                    ? 'Que alegria te receber no **OPUS**! Eu vou te guiar automaticamente por **todas as áreas do site** — Aprender, Desafios, Ranking, Ligas, Dicionário, Chat e seu Perfil. Vamos começar?'
                    : 'Aqui é a sua **Trilha de Java**! Vou te mostrar os principais controles desta tela para você aproveitar ao máximo.',
                mascot: 'vistodecimaazul.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4',
                nextText: 'Vamos lá!',
                skipText: 'Agora não'
            },
            {
                selector: '.topbar-stats',
                pointer: 'Painel Rápido',
                badge: 'SEU STATUS AO VIVO',
                title: 'Barra Superior',
                text: 'Aqui em cima fica o seu painel rápido: botão deste **Tour**, **Chat**, **Notificações**, sua **Ofensiva de Fogo**, seu **XP total** e suas **3 Vidas**!',
                mascot: 'acertoumetade.png',
                accent: '#ff9600',
                accentDark: '#cc7800'
            },
            {
                selector: '#currentLessonNode',
                fallbackSelector: '#woofzIslandTopbar',
                pointer: 'Comece aqui!',
                badge: 'ABA APRENDER',
                title: 'Trilha das Ilhas',
                text: 'É aqui na aba **Aprender** que você percorre **3 ilhas**, **40 seções** e **200 lições** de Java, abre **baús de recompensa** e acessa o **Guia** de cada unidade!',
                mascot: 'opi_ranking.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4'
            },
            {
                selector: '#themeToggleBtn',
                pointer: 'Tema',
                badge: 'APARÊNCIA DO SITE',
                title: 'Modo Claro e Escuro',
                text: 'Prefere estudar no claro ou no escuro? Clicando aqui você alterna entre o **Modo Claro** e o **Modo Escuro** na hora!',
                mascot: 'vistodecimaazul.png',
                accent: '#ffc800',
                accentDark: '#c99700'
            },
            {
                selector: '#opiChatTrigger',
                pointer: 'Opi IA',
                badge: 'INTELIGÊNCIA ARTIFICIAL',
                title: 'Assistente Opi IA',
                text: 'Travou em alguma lição ou quer tirar uma dúvida sobre **Java**? Clique aqui no botão **IA** para conversar comigo no **Opi IA** e acompanhar seu progresso!',
                mascot: 'opi_dicionario.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4',
                nextText: fullMode ? 'Próximo' : 'Concluir!'
            }
        ];

        if (fullMode) {
            list.push({
                selector: '.sidebar a[href="desafios.php"]',
                pointer: 'Desafios',
                badge: 'PRÓXIMA PARADA',
                title: 'Vamos para Desafios!',
                text: 'Agora vou clicar automaticamente na aba **Desafios** para te mostrar o laboratório onde você programa projetos práticos em **Java**!',
                mascot: 'explicando2.png',
                accent: '#ce82ff',
                accentDark: '#9b4de0',
                autoNavigate: {
                    page: 'desafios.php',
                    selector: '.sidebar a[href="desafios.php"]'
                },
                nextText: 'Próximo'
            });
        }

        return list;
    }

    function buildDesafiosSteps(fullMode) {
        const list = [
            {
                selector: '.desafios-stats-grid',
                fallbackSelector: '.desafios-hero',
                pointer: 'Resumo',
                badge: 'LABORATÓRIO JAVA',
                title: 'Projetos Práticos & Desafios',
                text: 'Chegamos nos **Desafios**! Aqui você acompanha quantos projetos existem, quantos já foram **desbloqueados** conforme avança na trilha e sua taxa de conclusão.',
                mascot: 'acertoumetade.png',
                accent: '#ce82ff',
                accentDark: '#9b4de0'
            },
            {
                selector: '.desafios-grid .desafio-card',
                fallbackSelector: '.desafios-filter-bar',
                pointer: 'Mão na Massa',
                badge: 'IDE INTEGRADA',
                title: 'Resolva na IDE Java',
                text: 'Use os filtros para ver desafios **Disponíveis**, **Concluídos** ou **Bloqueados**, e clique em **Iniciar Desafio** para abrir nossa **IDE Java** e validar seu código!',
                mascot: 'opi_dicionario.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4',
                nextText: fullMode ? 'Próximo' : 'Concluir!'
            }
        ];

        if (fullMode) {
            list.push({
                selector: '.sidebar a[href="ranking.php"]',
                pointer: 'Ranking',
                badge: 'PRÓXIMA PARADA',
                title: 'Vamos para o Ranking!',
                text: 'Agora vou clicar automaticamente na aba **Ranking** para te mostrar o placar global e o pódio da comunidade OPUS!',
                mascot: 'explicando2.png',
                accent: '#ffc800',
                accentDark: '#c99700',
                autoNavigate: {
                    page: 'ranking.php',
                    selector: '.sidebar a[href="ranking.php"]'
                },
                nextText: 'Próximo'
            });
        }

        return list;
    }

    function buildRankingSteps(fullMode) {
        const list = [
            {
                selector: '#podium-container',
                fallbackSelector: '.ranking-hero',
                waitForDynamic: true,
                pointer: 'Top 3',
                badge: 'PLACAR GLOBAL',
                title: 'Pódio do Ranking',
                text: 'Bem-vindo ao **Ranking Global**! Aqui no pódio ficam os **3 melhores alunos** em XP acumulado, com coroa para o líder e destaque para sua posição.',
                mascot: 'opi_ranking.png',
                accent: '#ffc800',
                accentDark: '#c99700'
            },
            {
                selector: '#ranking-list',
                fallbackSelector: '.ranking-hero',
                waitForDynamic: true,
                pointer: 'Classificação',
                badge: 'COMUNIDADE OPUS',
                title: 'Lista Geral & Perfis',
                text: 'Abaixo do pódio você vê toda a classificação da comunidade e pode clicar em **Ver Perfil** para conhecer outros alunos e fazer novas conexões!',
                mascot: 'vistodecimaazul.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4',
                nextText: fullMode ? 'Próximo' : 'Concluir!'
            }
        ];

        if (fullMode) {
            list.push({
                selector: '.sidebar a[href="ligas.php"]',
                pointer: 'Ligas',
                badge: 'PRÓXIMA PARADA',
                title: 'Vamos para as Ligas!',
                text: 'Agora vou clicar automaticamente na aba **Ligas** para te mostrar como funciona a competição semanal entre divisões!',
                mascot: 'explicando2.png',
                accent: '#ff9600',
                accentDark: '#cc7800',
                autoNavigate: {
                    page: 'ligas.php',
                    selector: '.sidebar a[href="ligas.php"]'
                },
                nextText: 'Próximo'
            });
        }

        return list;
    }

    function buildLigasSteps(fullMode) {
        const list = [
            {
                selector: '#league-track',
                fallbackSelector: '.liga-header',
                waitForDynamic: true,
                pointer: 'Divisões',
                badge: 'COMPETIÇÃO SEMANAL',
                title: 'Bronze até Mestre',
                text: 'Aqui ficam as **Ligas**! Toda semana você compete com um grupo de alunos para subir pelas divisões **Bronze, Prata, Ouro, Diamante e Mestre**.',
                mascot: 'acertoumetade.png',
                accent: '#ff9600',
                accentDark: '#cc7800'
            },
            {
                selector: '.liga-header',
                fallbackSelector: '.liga-legenda',
                pointer: 'Cronômetro',
                badge: 'VIRADA DA SEMANA',
                title: 'Tempo e Zonas de Subida',
                text: 'Fique de olho no **cronômetro da semana** e na **zona de subida** em verde: termine entre os primeiros colocados antes do tempo zerar para avançar de divisão!',
                mascot: 'opi_ranking.png',
                accent: '#58cc02',
                accentDark: '#46a302',
                nextText: fullMode ? 'Próximo' : 'Concluir!'
            }
        ];

        if (fullMode) {
            list.push({
                selector: '.sidebar a[href="dicionario.php"]',
                pointer: 'Dicionário',
                badge: 'PRÓXIMA PARADA',
                title: 'Vamos para o Dicionário!',
                text: 'Agora vou clicar automaticamente na aba **Dicionário** para te mostrar o códex rápido de comandos em **Java**!',
                mascot: 'explicando2.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4',
                autoNavigate: {
                    page: 'dicionario.php',
                    selector: '.sidebar a[href="dicionario.php"]'
                },
                nextText: 'Próximo'
            });
        }

        return list;
    }

    function buildDicionarioSteps(fullMode) {
        const list = [
            {
                selector: '.codex-hero',
                pointer: 'Consulta Rápida',
                badge: 'CÓDEX JAVA',
                title: 'Dicionário de Comandos',
                text: 'Este é o **Dicionário de Java**! Sempre que bater dúvida durante uma lição ou desafio, você pode consultar tudo organizado por assunto aqui.',
                mascot: 'opi_dicionario.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4'
            },
            {
                selector: '.categoria-section .comandos-grid',
                fallbackSelector: '.categoria-section',
                pointer: 'Passe o mouse!',
                badge: 'EXPLICAÇÃO NA HORA',
                title: 'Inspecione Cada Comando',
                text: 'Passe o mouse ou toque em qualquer comando — como **System.out.println()**, **Scanner**, **if/else** ou **ArrayList** — para ler a explicação prática na hora!',
                mascot: 'acertoumetade.png',
                accent: '#ffc800',
                accentDark: '#c99700',
                nextText: fullMode ? 'Próximo' : 'Concluir!'
            }
        ];

        if (fullMode) {
            list.push({
                selector: '#btn-chat',
                pointer: 'Chat',
                badge: 'PRÓXIMA PARADA',
                title: 'Vamos para o Chat!',
                text: 'Agora vou clicar automaticamente no ícone de **Chat** aqui no topo para te mostrar onde você conversa e faz amigos no OPUS!',
                mascot: 'azulcarta.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4',
                autoNavigate: {
                    page: 'chat.php',
                    selector: '#btn-chat'
                },
                nextText: 'Próximo'
            });
        }

        return list;
    }

    function buildChatSteps(fullMode) {
        const list = [
            {
                selector: '.chat-search',
                fallbackSelector: '.chat-sidebar',
                pointer: 'Encontrar Alunos',
                badge: 'MENSAGENS DIRETAS',
                title: 'Busque e Convide Amigos',
                text: 'Chegamos no **Chat**! Aqui você pesquisa outros estudantes por nome ou e-mail e também pode copiar seu **link de convite** no botão **Convidar**.',
                mascot: 'vistodecimaazul.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4'
            },
            {
                selector: '.chat-tabs',
                fallbackSelector: '.chat-sidebar',
                pointer: 'Abas do Chat',
                badge: 'CONEXÕES',
                title: 'Conversas e Notificações',
                text: 'Alterne entre suas **Conversas** diretas com amigos e a aba **Notificações** para aceitar novos pedidos de amizade em tempo real!',
                mascot: 'azulcarta.png',
                accent: '#ce82ff',
                accentDark: '#9b4de0',
                nextText: fullMode ? 'Próximo' : 'Concluir!'
            }
        ];

        if (fullMode) {
            list.push({
                selector: '.sidebar a[href="perfil.php"]',
                pointer: 'Seu Perfil',
                badge: 'ÚLTIMA PARADA',
                title: 'Vamos para o seu Perfil!',
                text: 'Por último, vou clicar automaticamente no seu **Perfil** para te mostrar onde personalizar foto, cor, redes sociais, preferências e conferir seus troféus!',
                mascot: 'explicando2.png',
                accent: '#58cc02',
                accentDark: '#46a302',
                autoNavigate: {
                    page: 'perfil.php',
                    selector: '.sidebar a[href="perfil.php"]'
                },
                nextText: 'Próximo'
            });
        }

        return list;
    }

    function buildPerfilSteps(fullMode) {
        const stepsList = [];

        if (!fullMode) {
            stepsList.push({
                selector: null,
                badge: 'TOUR DO PERFIL',
                title: 'Bem-vindo ao seu Perfil, ' + USER_NAME + '!',
                text: 'Chegamos no seu espaço pessoal! Deixa eu te mostrar rapidamente tudo o que você pode personalizar e acompanhar aqui no seu **Perfil**.',
                mascot: 'opi_ranking.png',
                accent: '#58cc02',
                accentDark: '#46a302',
                nextText: 'Mostrar tudo!',
                skipText: 'Pular'
            });
        }

        stepsList.push(
            {
                selector: '.profile-avatar-wrapper',
                pointer: 'Foto e Cor',
                badge: 'SUA IDENTIDADE',
                title: 'Foto e Cor',
                text: fullMode
                    ? 'Chegamos no seu **Perfil**, ' + USER_NAME + '! Clique na sua **foto** ou no **lápis** ao lado para enviar uma imagem e trocar a **cor da borda**!'
                    : 'Clique na sua **foto** ou no **lápis** ao lado para enviar uma imagem de perfil e trocar a **cor da borda**!',
                mascot: 'vistodecimaazul.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4'
            },
            {
                selector: '.social-links',
                pointer: 'Conexões',
                badge: 'REDES & AMIGOS',
                title: 'Redes Sociais e Conexões',
                text: 'Aqui você adiciona links das suas redes (**GitHub, LinkedIn, Instagram** e mais) e acompanha quantas pessoas você está **seguindo**, seus **seguidores** e **amigos**.',
                mascot: 'explicando2.png',
                accent: '#ce82ff',
                accentDark: '#9b4de0'
            },
            {
                selector: '.stats-grid',
                pointer: 'Estatísticas',
                badge: 'SEU DESEMPENHO',
                title: 'Estatísticas do Aluno',
                text: 'Acompanhe seu resumo em tempo real: **dias seguidos** de ofensiva, seu **total de XP** conquistado e a sua **divisão atual** nas ligas semanais!',
                mascot: 'acertoumetade.png',
                accent: '#ffc800',
                accentDark: '#c99700'
            },
            {
                selector: '.interact-card',
                pointer: 'Amigos',
                badge: 'COMUNIDADE',
                title: 'Convidar e Encontrar Amigos',
                text: 'Aprender com amigos motiva muito mais! Use estes botões para copiar seu **link de convite** ou **encontrar outros alunos** e conversar no chat.',
                mascot: 'azulcarta.png',
                accent: '#ff9600',
                accentDark: '#cc7800'
            },
            {
                selector: '#trophy-container',
                fallbackSelector: '.profile-full-width',
                waitForDynamic: true,
                pointer: 'Troféus',
                badge: fullMode ? 'TUDO PRONTO!' : 'CONQUISTAS',
                title: 'Sala de Troféus',
                text: fullMode
                    ? 'E aqui embaixo ficam todas as suas **Conquistas**! Agora você já conhece todo o **OPUS**. Quando quiser rever o tour, use o ícone de **bússola** no topo ou estes botões aqui no Perfil. Bons estudos, **' + USER_NAME + '**!'
                    : 'E aqui embaixo ficam todas as suas **Conquistas**! Conclua unidades, acerte lições perfeitas e clique em qualquer troféu para admirá-lo em tela cheia. Boa jornada, **' + USER_NAME + '**!',
                mascot: 'opi_ranking.png',
                accent: '#ffc800',
                accentDark: '#c99700',
                nextText: 'Finalizar Tour!',
                extraAction: (fullMode && !cfg.tourLicao1Visto) ? {
                    label: 'Fazer 1ª Lição',
                    icon: 'fa-play',
                    onClick: function () {
                        closeTour(true);
                        window.location.href = 'licao.php?cap=1&licao=1';
                    }
                } : null
            }
        );

        return stepsList;
    }

    function buildConquistasSteps() {
        return [
            {
                selector: '.header-conquistas',
                pointer: 'Troféus',
                badge: 'CONQUISTAS',
                title: 'Sala de Troféus',
                text: 'Aqui você acompanha todas as suas **conquistas** no OPUS! Complete lições, mantenha sua ofensiva acesa e desbloqueie cada medalha.',
                mascot: 'opi_ranking.png',
                accent: '#ffc800',
                accentDark: '#c99700'
            },
            {
                selector: '#trophy-container',
                fallbackSelector: '.page-container',
                waitForDynamic: true,
                pointer: 'Suas Medalhas',
                badge: 'GALERIA',
                title: 'Suas Conquistas',
                text: 'Clique em qualquer troféu desbloqueado para ver os detalhes da sua conquista em destaque!',
                mascot: 'vistodecimaazul.png',
                accent: '#58cc02',
                accentDark: '#46a302',
                nextText: 'Concluir!'
            }
        ];
    }

    // =========================================================================
    // ROTEIROS DO TUTOR OPI: 1ª LIÇÃO, RESULTADO DA 1ª LIÇÃO E TROFÉUS
    // =========================================================================
    function buildLicao1ExplSteps() {
        return [
            {
                selector: null,
                badge: 'TUTOR · LIÇÃO 1',
                title: 'Sua primeira lição em Java!',
                text: 'Oi, **' + USER_NAME + '**! Eu sou o **Opi** e vou te acompanhar passo a passo nesta **1ª lição**! A partir da **Lição 2** você segue livre no seu ritmo e eu volto sempre que você conquistar um **novo troféu**.',
                mascot: 'vistodecimaazul.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4',
                nextText: 'Vamos lá!',
                skipText: 'Agora não'
            },
            {
                selector: '.explanation-card-wrapper',
                fallbackSelector: '.explanation-stage',
                pointer: 'Leia com calma',
                badge: 'FASE 1 · TEORIA',
                title: 'Entendendo o Conceito',
                text: 'Toda lição começa com slides curtinhos de explicação! Leia cada parte com atenção — no último slide eu te mostro um **exemplo prático em Java** antes dos exercícios.',
                mascot: 'explicando2.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4'
            },
            {
                selector: '#btnNextSlide',
                fallbackSelector: '.phase-footer',
                pointer: 'Avançar',
                badge: 'CONTROLES',
                title: 'Navegue pelos Slides',
                text: 'Use **Próximo** (ou as setas do teclado) para avançar entre os slides. Quando chegar ao final, este botão vira **Começar Exercícios** e eu volto rapidinho para te guiar nas perguntas!',
                mascot: 'acertoumetade.png',
                accent: '#58cc02',
                accentDark: '#46a302'
            },
            {
                selector: '#opiChatTrigger',
                pointer: 'Tira-dúvidas',
                badge: 'OPI IA',
                title: 'Precisou de ajuda extra?',
                text: 'Ficou com alguma dúvida sobre **System.out.println()** ou qualquer comando Java? Clique no botão do **Opi IA** aqui do lado para conversar comigo a qualquer momento!',
                mascot: 'opi_dicionario.png',
                accent: '#ce82ff',
                accentDark: '#9b4de0',
                nextText: 'Entendi!'
            }
        ];
    }

    function buildLicao1QuizSteps() {
        return [
            {
                selector: '.question-slide[data-index="0"] .duo-options',
                fallbackSelector: '.quiz-question-side',
                pointer: 'Escolha aqui',
                badge: 'FASE 2 · EXERCÍCIOS',
                title: 'Hora de Praticar!',
                text: 'Agora você vai resolver **' + (cfg.totalQuestoes || 6) + ' exercícios interativos** — tem múltipla escolha (ou teclas **1, 2, 3**), ordenar código, completar lacunas, prever saída e caçar bugs! Dica: deixei uma **barra de dicas do Opi** logo acima para te ajudar em cada questão.',
                mascot: 'explicando2.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4'
            },
            {
                selector: '#btnAction',
                fallbackSelector: '#quizFooter',
                pointer: 'Conferir',
                badge: 'FEEDBACK NA HORA',
                title: 'Verificar e Continuar',
                text: 'Depois de selecionar ou montar sua resposta, clique em **VERIFICAR** (ou pressione **Enter**) para conferir na hora e clique em **CONTINUAR** para avançar!',
                mascot: 'acertoumetade.png',
                accent: '#58cc02',
                accentDark: '#46a302'
            },
            {
                selector: '.quiz-progress-bar',
                pointer: 'Seu avanço',
                badge: 'REGRAS & FOCO',
                title: 'Acompanhe sua Barra!',
                text: 'Aqui em cima ficam o seu **progresso** e o controle de **som**. Evite sair pelo **X** no meio dos exercícios para não perder **15 XP**. No final, eu te explico seus resultados e libero a **Lição 2**!',
                mascot: 'opi_ranking.png',
                accent: '#ff9600',
                accentDark: '#cc7800',
                nextText: 'Bora responder!'
            }
        ];
    }

    function buildTrophySteps(trophies) {
        if (!Array.isArray(trophies) || !trophies.length) return [];
        return trophies.map((t, idx) => {
            const isLast = (idx === trophies.length - 1);
            return {
                selector: null,
                trophy: t,
                badge: 'TROFÉU CONQUISTADO!',
                title: trophies.length > 1
                    ? 'Novo troféu desbloqueado!'
                    : 'Você ganhou um troféu, ' + USER_NAME + '!',
                text: t.tutor_texto || ('Parabéns! Você desbloqueou o troféu **' + (t.nome || 'Conquista') + '** — ' + (t.desc || '') + ' Ele já está guardado na sua **Sala de Troféus**!'),
                mascot: 'opi_ranking.png',
                accent: t.cor || '#ffc800',
                accentDark: '#c99700',
                nextText: isLast ? 'Continuar!' : 'Próximo Troféu',
                skipText: 'Fechar',
                extraAction: {
                    label: 'Ver Troféus',
                    icon: 'fa-trophy',
                    onClick: function () {
                        closeTour(true);
                        window.location.href = 'perfil.php#trophy-container';
                    }
                }
            };
        });
    }

    function buildResultado1Steps() {
        const list = [];
        const acertos = typeof cfg.acertos === 'number' ? cfg.acertos : 0;
        const totalQ = typeof cfg.totalQuestoes === 'number' ? cfg.totalQuestoes : 6;
        const xpGanho = typeof cfg.xpGanho === 'number' ? cfg.xpGanho : 50;

        if (cfg.errouTodas) {
            list.push({
                selector: '.duo-stats-grid',
                fallbackSelector: '#duoLessonScreen',
                pointer: 'Seu resumo',
                badge: 'FIM DA LIÇÃO 1',
                title: 'Não desanime, ' + USER_NAME + '!',
                text: 'Errar faz parte de aprender programação! Aqui nos cartões você acompanha os **comandos praticados**, seus **acertos** (**' + acertos + ' / ' + totalQ + '**) e o **tempo** na lição.',
                mascot: 'explicando2.png',
                accent: '#ff9600',
                accentDark: '#cc7800'
            });
            list.push({
                selector: '.topbar-stats',
                fallbackSelector: '.top-bar',
                pointer: 'Vidas & XP',
                badge: 'COMO FUNCIONA',
                title: 'Suas Vidas e Energia',
                text: 'Quando zeramos todas as questões, perdemos **1 coração de vida** (cada coração volta a cada 5 horas). Revise a explicação com calma — na próxima tentativa você já vai mandar bem!',
                mascot: 'acertoumetade.png',
                accent: '#ff4b4b',
                accentDark: '#cc3c3c'
            });
            list.push({
                selector: '#btnDuoContinue',
                fallbackSelector: '#duoLessonScreen',
                pointer: 'Tentar de novo',
                badge: 'RECOMEÇAR',
                title: 'Bora tentar a Lição 1 de novo!',
                text: 'Clique em **Tentar Lição 1** para rever os slides rapidinho e garantir seu primeiro triunfo em Java!',
                mascot: 'vistodecimaazul.png',
                accent: '#58cc02',
                accentDark: '#46a302',
                nextText: 'Tentar Lição 1',
                onComplete: function () {
                    isTransitioning = true;
                    if (spotlight) spotlight.classList.add('is-auto-clicking');
                    const delay = prefersReducedMotion() ? 60 : 360;
                    setTimeout(() => {
                        closeTour(true);
                        window.location.href = 'licao.php?cap=1&licao=1';
                    }, delay);
                }
            });
            return list;
        }

        list.push({
            selector: '.duo-stats-grid',
            fallbackSelector: '#duoLessonScreen',
            pointer: 'Seu desempenho',
            badge: 'FIM DA LIÇÃO 1',
            title: 'Parabéns pela 1ª lição, ' + USER_NAME + '!',
            text: 'Você concluiu sua **1ª lição**! Aqui nos 3 cartões ficam os **comandos aprendidos**, seus **acertos** (**' + acertos + '/' + totalQ + '**, rendendo **+' + xpGanho + ' XP**) e o **tempo praticando**.',
            mascot: 'acertoumetade.png',
            accent: '#58cc02',
            accentDark: '#46a302'
        });

        list.push({
            selector: '.topbar-stats',
            fallbackSelector: '.top-bar',
            pointer: 'Status ao vivo',
            badge: 'COMO FUNCIONA',
            title: 'XP, Ofensiva de Fogo e Vidas',
            text: 'Seu **XP** faz você subir no **Ranking** e nas **Ligas semanais**, a **Ofensiva de Fogo** conta seus dias seguidos praticando e seus **3 corações** protegem suas tentativas!',
            mascot: 'explicando2.png',
            accent: '#ff9600',
            accentDark: '#cc7800'
        });

        if (Array.isArray(cfg.novosTrofeus) && cfg.novosTrofeus.length > 0) {
            cfg.novosTrofeus.forEach((t, idx) => {
                list.push({
                    selector: null,
                    trophy: t,
                    badge: 'TROFÉU DESBLOQUEADO!',
                    title: 'Você ganhou: ' + (t.nome || 'Troféu') + '!',
                    text: (t.tutor_texto || ('Parabéns! Você desbloqueou **' + (t.nome || 'Troféu') + '**!')) + (idx === 0 ? ' Todos os troféus ficam salvos na **Sala de Troféus** do seu Perfil!' : ''),
                    mascot: 'opi_ranking.png',
                    accent: t.cor || '#ffc800',
                    accentDark: '#c99700'
                });
            });
        }

        const proxTitulo = cfg.proxLicaoTitulo ? (' ("' + cfg.proxLicaoTitulo + '")') : '';
        list.push({
            selector: '#btnDuoContinue',
            fallbackSelector: '#duoLessonScreen',
            pointer: 'Lição 2!',
            badge: 'PRÓXIMO PASSO',
            title: 'Hora da Lição 2!',
            text: 'A partir da **Lição 2**' + proxTitulo + ', você já domina o ritmo e segue **livre sem interrupções** — eu só volto para comemorar quando você ganhar um **novo troféu**! Vamos começar a **2ª lição**?',
            mascot: 'vistodecimaazul.png',
            accent: '#1cb0f6',
            accentDark: '#148bc4',
            nextText: 'Iniciar Lição 2!',
            onComplete: function () {
                isTransitioning = true;
                if (!cfg.isTrofeuPreview) {
                    markTourSeen('resultado1');
                }
                if (spotlight) spotlight.classList.add('is-auto-clicking');
                const delay = prefersReducedMotion() ? 60 : 360;
                setTimeout(() => {
                    closeTour(true);
                    const btnContinue = document.getElementById('btnDuoContinue');
                    if (btnContinue) {
                        btnContinue.click();
                    } else {
                        window.location.href = cfg.proxLicaoUrl || 'licao.php?cap=1&licao=2';
                    }
                }, delay);
            }
        });

        return list;
    }

    function buildResultadoManualSteps() {
        return [
            {
                selector: '.duo-stats-grid',
                fallbackSelector: '#duoLessonScreen',
                pointer: 'Resumo',
                badge: 'RESULTADO',
                title: 'Resumo da sua Lição',
                text: 'Aqui você confere os **comandos aprendidos**, os **acertos** da lição (com o **XP ganho**) e o **tempo** que você dedicou aos exercícios.',
                mascot: 'acertoumetade.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4'
            },
            {
                selector: '#btnDuoContinue',
                fallbackSelector: '#duoLessonScreen',
                pointer: 'Avançar',
                badge: 'PRÓXIMA LIÇÃO',
                title: 'Continue sua Trilha!',
                text: 'Clique em **Continuar** para acender sua ofensiva (se for sua primeira lição do dia) e seguir direto para a próxima lição!',
                mascot: 'vistodecimaazul.png',
                accent: '#58cc02',
                accentDark: '#46a302',
                nextText: 'Entendi!'
            }
        ];
    }

    function buildCursoFinalizadoSteps() {
        return [
            {
                selector: null,
                fallbackSelector: '#duoLessonScreen',
                pointer: 'Você venceu!',
                badge: 'CURSO CONCLUÍDO! 🏆',
                title: 'Você Zerou o Curso, ' + USER_NAME + '! 🎓',
                text: 'É OFICIAL! Você completou **todas as 40 seções** e alcançou a linha de chegada da nossa formação completa em programação! Pouquíssimos estudantes chegam até aqui — você agora é um mestre!',
                mascot: 'mascote_vitoria_cipo_transparente.png',
                accent: '#1db954',
                accentDark: '#15883e',
                nextText: 'Rever Minha Jornada'
            },
            {
                selector: null,
                fallbackSelector: '#duoLessonScreen',
                pointer: 'Início',
                badge: 'RETROSPECTIVA: CAPÍTULO 1 🚀',
                title: 'Do "Olá Mundo" aos Fundamentos',
                text: 'Lembra quando você começou lá no **Capítulo 1 (Fundamentos)**? Variáveis, tipos de dados primitivos, operadores aritméticos e estruturas condicionais `if/else`... Naquele momento você deu o primeiro grande passo!',
                mascot: 'fundamentos-capitulo1.png',
                accent: '#1cb0f6',
                accentDark: '#148bc4',
                nextText: 'Avançar'
            },
            {
                selector: null,
                fallbackSelector: '#duoLessonScreen',
                pointer: 'Evolução',
                badge: 'RETROSPECTIVA: CAPÍTULO 2 ⚡',
                title: 'Caminhos Lógicos & Estruturas',
                text: 'Depois veio o **Capítulo 2 (Caminhos Lógicos)**: laços de repetição `while` e `for`, vetores (arrays), manipulação de dados e fluxos complexos. Você transformou desafios difíceis em códigos limpos e funcionais!',
                mascot: 'caminhoslogicos-capitulo2.png',
                accent: '#ff527b',
                accentDark: '#d13b60',
                nextText: 'Avançar'
            },
            {
                selector: null,
                fallbackSelector: '#duoLessonScreen',
                pointer: 'Prática Real',
                badge: 'RETROSPECTIVA: CAPÍTULO 3 💻',
                title: 'Projetos Reais no Terminal!',
                text: 'No **Capítulo 3 (Arquiteto Java)**, você não ficou só na teoria: abriu o **terminal interativo**, escreveu códigos de verdade, corrigiu bugs e colocou algoritmos completos para rodar com sucesso!',
                mascot: 'arquitetojava.png',
                accent: '#ff9600',
                accentDark: '#cc7800',
                nextText: 'Avançar'
            },
            {
                selector: null,
                fallbackSelector: '#duoLessonScreen',
                pointer: 'Seu Presente',
                badge: 'SEU SPOTIFY WRAPPED 🎧',
                title: 'Seu Cartão Oficial de Formatura!',
                text: 'Para coroar essa conquista histórica, preparei o seu **Cartão Especial estilo Spotify Wrapped**! Ele reúne suas estatísticas completas e está pronto para você **baixar em alta resolução** ou **compartilhar direto**!',
                mascot: 'mascote_vitoria_cipo_transparente.png',
                accent: '#1db954',
                accentDark: '#15883e',
                nextText: 'Abrir Meu Card Spotify! 🎧',
                onComplete: function () {
                    isTransitioning = true;
                    if (spotlight) spotlight.classList.add('is-auto-clicking');
                    const delay = prefersReducedMotion() ? 60 : 360;
                    setTimeout(() => {
                        closeTour(true);
                        if (typeof window.abrirSpotifyWrappedModal === 'function') {
                            window.abrirSpotifyWrappedModal();
                        }
                    }, delay);
                }
            }
        ];
    }

    function buildPageSteps(page, fullMode) {
        switch (page) {
            case 'dashboard.php':
                return buildDashboardSteps(fullMode);
            case 'desafios.php':
                return buildDesafiosSteps(fullMode);
            case 'ranking.php':
                return buildRankingSteps(fullMode);
            case 'ligas.php':
                return buildLigasSteps(fullMode);
            case 'dicionario.php':
                return buildDicionarioSteps(fullMode);
            case 'chat.php':
                return buildChatSteps(fullMode);
            case 'perfil.php':
                return buildPerfilSteps(fullMode);
            case 'conquistas.php':
                return buildConquistasSteps();
            default:
                return buildDashboardSteps(fullMode);
        }
    }

    // =========================================================================
    // MOTOR DE DIGITAÇÃO EM TEMPO REAL (TYPEWRITER COM SUPORTE A **NEGRITO**)
    // =========================================================================
    function parseSegments(raw) {
        const segments = [];
        const regex = /\*\*(.+?)\*\*/g;
        let lastIndex = 0;
        let match;
        while ((match = regex.exec(raw)) !== null) {
            if (match.index > lastIndex) {
                segments.push({ text: raw.slice(lastIndex, match.index), bold: false });
            }
            segments.push({ text: match[1], bold: true });
            lastIndex = regex.lastIndex;
        }
        if (lastIndex < raw.length) {
            segments.push({ text: raw.slice(lastIndex), bold: false });
        }
        return segments;
    }

    function renderInstant(raw) {
        clearTimeout(typingTimer);
        typingTimer = null;
        isTyping = false;
        mascotWrap.classList.remove('is-talking');
        textEl.classList.remove('is-typing');
        textEl.innerHTML = '';

        const segments = parseSegments(raw);
        segments.forEach(seg => {
            if (seg.bold) {
                const strong = document.createElement('strong');
                strong.textContent = seg.text;
                textEl.appendChild(strong);
            } else {
                textEl.appendChild(document.createTextNode(seg.text));
            }
        });
    }

    function startTypewriter(raw) {
        clearTimeout(typingTimer);
        typingTimer = null;
        currentFullText = raw;

        if (prefersReducedMotion()) {
            renderInstant(raw);
            return;
        }

        const segments = parseSegments(raw);
        const chars = [];
        segments.forEach(seg => {
            for (const ch of seg.text) {
                chars.push({ ch, bold: seg.bold });
            }
        });

        textEl.innerHTML = '';
        textEl.classList.add('is-typing');
        mascotWrap.classList.add('is-talking');
        isTyping = true;

        let idx = 0;
        let currentNode = null;
        let currentBold = null;

        function tick() {
            if (!isOpen || !isTyping) return;
            if (idx >= chars.length) {
                isTyping = false;
                textEl.classList.remove('is-typing');
                mascotWrap.classList.remove('is-talking');
                return;
            }

            const item = chars[idx];
            if (item.bold !== currentBold) {
                currentBold = item.bold;
                if (item.bold) {
                    const strong = document.createElement('strong');
                    textEl.appendChild(strong);
                    currentNode = document.createTextNode('');
                    strong.appendChild(currentNode);
                } else {
                    currentNode = document.createTextNode('');
                    textEl.appendChild(currentNode);
                }
            }
            currentNode.nodeValue += item.ch;

            idx++;
            let delay = 22;
            if (item.ch === '.' || item.ch === '!' || item.ch === '?') {
                delay = 115;
            } else if (item.ch === ',' || item.ch === ':' || item.ch === ';') {
                delay = 65;
            }
            typingTimer = setTimeout(tick, delay);
        }

        tick();
    }

    // =========================================================================
    // POSICIONAMENTO INTELIGENTE DO SPOTLIGHT E DO BALÃO
    // =========================================================================
    function hasVisibleBox(selector) {
        if (!selector) return false;
        const el = document.querySelector(selector);
        if (!el) return false;
        const rect = el.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    }

    function findTargetElement(selector, fallbackSelector) {
        if (!selector) return null;
        const el = document.querySelector(selector);
        if (el) {
            const rect = el.getBoundingClientRect();
            if (rect.width > 0 && rect.height > 0) return el;
        }
        if (fallbackSelector) {
            const fb = document.querySelector(fallbackSelector);
            if (fb) {
                const fbRect = fb.getBoundingClientRect();
                if (fbRect.width > 0 && fbRect.height > 0) return fb;
            }
        }
        return null;
    }

    function waitForTargetReady(step, token, callback) {
        if (!step.waitForDynamic || !step.selector || hasVisibleBox(step.selector)) {
            callback();
            return;
        }

        let elapsed = 0;
        const interval = 90;
        const maxWait = 900;

        function check() {
            if (!isOpen || token !== stepRenderToken) return;
            if (hasVisibleBox(step.selector) || elapsed >= maxWait) {
                callback();
                return;
            }
            elapsed += interval;
            setTimeout(check, interval);
        }

        setTimeout(check, interval);
    }

    function positionStageAndSpotlight(step) {
        const target = findTargetElement(step.selector, step.fallbackSelector);

        if (!target) {
            root.classList.remove('has-spotlight');
            root.classList.add('no-spotlight');
            stage.classList.add('is-centered');
            stage.classList.remove('is-reversed');
            stage.style.top = '';
            stage.style.left = '';
            return;
        }

        const rect = target.getBoundingClientRect();
        const pad = 8;
        const spTop = Math.max(4, rect.top - pad);
        const spLeft = Math.max(4, rect.left - pad);
        const spWidth = Math.min(window.innerWidth - spLeft - 4, rect.width + pad * 2);
        const spHeight = Math.min(window.innerHeight - spTop - 4, rect.height + pad * 2);

        const computedRadius = parseFloat(window.getComputedStyle(target).borderRadius) || 14;
        spotlight.style.top = spTop + 'px';
        spotlight.style.left = spLeft + 'px';
        spotlight.style.width = spWidth + 'px';
        spotlight.style.height = spHeight + 'px';
        spotlight.style.borderRadius = Math.min(28, Math.max(12, computedRadius + 4)) + 'px';

        // Etiqueta "Aqui!"
        if (step.pointer) {
            pointerLabel.textContent = step.pointer;
            pointerTag.style.display = 'inline-flex';
            pointerTag.classList.toggle('is-bottom', spTop < 54);
        } else {
            pointerTag.style.display = 'none';
        }

        root.classList.add('has-spotlight');
        root.classList.remove('no-spotlight');
        stage.classList.remove('is-centered');

        const vw = window.innerWidth;
        const vh = window.innerHeight;
        const margin = 18;

        // Modo mobile / telas estreitas: empilha acima ou abaixo do alvo
        if (vw <= 768) {
            stage.classList.remove('is-reversed');
            stage.style.left = '';
            const stageH = stage.offsetHeight || 454;
            const spaceBelow = vh - (spTop + spHeight);
            const spaceAbove = spTop;

            let topPos;
            if (spaceBelow >= stageH + margin) {
                topPos = spTop + spHeight + 16;
            } else if (spaceAbove >= stageH + margin) {
                topPos = Math.max(margin, spTop - stageH - 16);
            } else {
                topPos = Math.max(margin, vh - stageH - margin);
            }
            stage.style.top = Math.round(topPos) + 'px';
            return;
        }

        // Modo desktop: mede o palco e posiciona sem sobrepor o elemento destacado
        const stageW = Math.min(vw - margin * 2, stage.offsetWidth || 646);
        const stageH = stage.offsetHeight || 344;

        const spaceRight = vw - (spLeft + spWidth);
        const spaceLeft = spLeft;
        const spaceBelow = vh - (spTop + spHeight);
        const spaceAbove = spTop;

        let leftPos = margin;
        let topPos = margin;
        let reversed = false;

        if (spaceRight >= stageW + 24) {
            leftPos = spLeft + spWidth + 24;
            topPos = spTop + (spHeight / 2) - (stageH / 2);
            reversed = false;
        } else if (spaceLeft >= stageW + 24) {
            leftPos = spLeft - stageW - 24;
            topPos = spTop + (spHeight / 2) - (stageH / 2);
            reversed = true;
        } else if (spaceBelow >= stageH + 24) {
            leftPos = spLeft + (spWidth / 2) - (stageW / 2);
            topPos = spTop + spHeight + 28;
            reversed = false;
        } else if (spaceAbove >= stageH + 24) {
            leftPos = spLeft + (spWidth / 2) - (stageW / 2);
            topPos = spTop - stageH - 28;
            reversed = false;
        } else {
            leftPos = (vw - stageW) / 2;
            topPos = Math.max(margin, vh - stageH - margin);
            reversed = false;
        }

        // Clamping rigoroso dentro da viewport
        leftPos = Math.max(margin, Math.min(vw - stageW - margin, leftPos));
        topPos = Math.max(margin, Math.min(vh - stageH - margin, topPos));

        stage.classList.toggle('is-reversed', reversed);
        stage.style.left = Math.round(leftPos) + 'px';
        stage.style.top = Math.round(topPos) + 'px';
    }

    function ensureTargetVisible(step, token, callback) {
        const target = findTargetElement(step.selector, step.fallbackSelector);
        if (!target) {
            callback();
            return;
        }
        const rect = target.getBoundingClientRect();
        const isInView = rect.top >= 16 && rect.bottom <= window.innerHeight - 16;
        if (!isInView) {
            try {
                target.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth', block: 'center', inline: 'nearest' });
            } catch (e) {}
            setTimeout(function () {
                if (isOpen && token === stepRenderToken) callback();
            }, prefersReducedMotion() ? 30 : 340);
        } else {
            callback();
        }
    }

    // =========================================================================
    // RENDERIZAÇÃO DE CADA PASSO
    // =========================================================================
    function getCurrentStageIndex(globalIndex) {
        let acc = 0;
        for (let i = 0; i < FULL_TOUR_STAGES.length; i++) {
            acc += FULL_TOUR_STAGES[i].count;
            if (globalIndex < acc) return i;
        }
        return FULL_TOUR_STAGES.length - 1;
    }

    function renderDots(globalIndex, total) {
        dotsContainer.innerHTML = '';
        if (isFullTour) {
            const stageIdx = getCurrentStageIndex(globalIndex);
            FULL_TOUR_STAGES.forEach((st, i) => {
                const dot = document.createElement('span');
                dot.className = 'opi-tour-dot';
                dot.title = st.label;
                if (i < stageIdx) dot.classList.add('is-done');
                if (i === stageIdx) dot.classList.add('is-current');
                dotsContainer.appendChild(dot);
            });
            return;
        }

        for (let i = 0; i < total; i++) {
            const dot = document.createElement('span');
            dot.className = 'opi-tour-dot';
            if (i < globalIndex) dot.classList.add('is-done');
            if (i === globalIndex) dot.classList.add('is-current');
            dotsContainer.appendChild(dot);
        }
    }

    function renderStep(index) {
        if (index < 0 || index >= steps.length) return;
        currentIndex = index;
        const token = ++stepRenderToken;
        const step = steps[index];
        const globalIndex = stepOffset + index;
        const total = overallTotal || steps.length;

        speechBox.scrollTop = 0;

        // Cor de destaque dinâmica do passo
        root.style.setProperty('--tour-accent', step.accent || '#1cb0f6');
        root.style.setProperty('--tour-accent-dark', step.accentDark || '#148bc4');

        // Atualiza pose do mascote azul com escala normalizada e micro-animação pop
        const mascotFile = step.mascot || 'explicando2.png';
        const nextSrc = IMG_BASE + mascotFile;
        mascotImg.style.setProperty('--mascot-scale', String(MASCOT_SCALES[mascotFile] || 1));
        if (mascotImg.getAttribute('data-pose') !== nextSrc) {
            mascotImg.src = nextSrc;
            mascotImg.setAttribute('data-pose', nextSrc);
            mascotWrap.classList.remove('is-swapping');
            void mascotWrap.offsetWidth;
            mascotWrap.classList.add('is-swapping');
            setTimeout(() => mascotWrap.classList.remove('is-swapping'), 380);
        }

        // Cartão de Troféu Conquistado (quando o passo celebra um troféu)
        if (trophyCardEl && bubbleEl) {
            if (step.trophy) {
                if (trophyImgEl) {
                    trophyImgEl.src = IMG_BASE + encodeURI(step.trophy.imagem || 'medalha.png');
                    trophyImgEl.alt = step.trophy.nome || 'Troféu conquistado';
                }
                if (trophyNameEl) trophyNameEl.textContent = step.trophy.nome || 'Troféu';
                if (trophyDescEl) trophyDescEl.textContent = step.trophy.desc || '';
                trophyCardEl.style.display = 'flex';
                bubbleEl.classList.add('has-trophy');
                root.classList.add('is-trophy-step');
                if (window.OpusAudio && typeof window.OpusAudio.playSuccess === 'function') {
                    try { window.OpusAudio.playSuccess(); } catch (e) {}
                }
            } else {
                trophyCardEl.style.display = 'none';
                bubbleEl.classList.remove('has-trophy');
                root.classList.remove('is-trophy-step');
            }
        }

        // Textos do cabeçalho
        badgeEl.textContent = step.badge || 'TOUR OPUS';
        counterEl.textContent = (globalIndex + 1) + ' / ' + total;
        titleEl.textContent = step.title || '';

        // Progresso
        const pct = Math.round(((globalIndex + 1) / total) * 100);
        progressFill.style.width = pct + '%';
        renderDots(globalIndex, total);

        // Botões
        prevBtn.style.display = index > 0 ? 'inline-flex' : 'none';
        const isLastOverall = (globalIndex === total - 1);
        skipBtn.style.display = isLastOverall ? 'none' : 'inline-flex';
        skipBtn.textContent = step.skipText || 'Pular';

        nextLabel.textContent = step.nextText || (isLastOverall ? 'Concluir!' : 'Próximo');
        const nextIcon = nextBtn.querySelector('i');
        if (nextIcon) {
            nextIcon.className = isLastOverall ? 'fa-solid fa-check' : 'fa-solid fa-arrow-right';
        }

        if (step.extraAction) {
            extraBtn.style.display = 'inline-flex';
            extraBtn.textContent = '';
            if (step.extraAction.icon) {
                const iconEl = document.createElement('i');
                iconEl.className = 'fa-solid ' + step.extraAction.icon;
                extraBtn.appendChild(iconEl);
                extraBtn.appendChild(document.createTextNode(' '));
            }
            extraBtn.appendChild(document.createTextNode(step.extraAction.label || ''));
            extraBtn.onclick = step.extraAction.onClick;
        } else {
            extraBtn.style.display = 'none';
            extraBtn.onclick = null;
        }

        // Aguarda conteúdo dinâmico (se aplicável), garante visibilidade e começa a digitar
        waitForTargetReady(step, token, function () {
            if (!isOpen || token !== stepRenderToken) return;
            ensureTargetVisible(step, token, function () {
                if (!isOpen || token !== stepRenderToken) return;
                positionStageAndSpotlight(step);
                requestAnimationFrame(function () {
                    if (isOpen && token === stepRenderToken) {
                        positionStageAndSpotlight(step);
                    }
                });
                startTypewriter(step.text);
            });
        });
    }

    // =========================================================================
    // PERSISTÊNCIA (LOCALSTORAGE + API PHP)
    // =========================================================================
    function getStorageKeyForType(type) {
        if (type === 'perfil') return STORAGE_KEY_PERFIL;
        if (type === 'licao1') return STORAGE_KEY_LICAO1;
        if (type === 'resultado1') return STORAGE_KEY_RESULTADO1;
        if (type === 'fim_curso') return STORAGE_KEY_FIM_CURSO;
        return STORAGE_KEY_MAIN;
    }

    function markTourSeen(type) {
        const key = getStorageKeyForType(type);
        try {
            localStorage.setItem(key, '1');
        } catch (e) {}

        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tour: type, visto: 1 }),
            keepalive: true
        }).catch(() => {});
    }

    // =========================================================================
    // CONTROLES PÚBLICOS: START / STOP / NAVEGAÇÃO AUTOMÁTICA
    // =========================================================================
    function filterAvailableSteps(rawSteps) {
        return rawSteps.filter(step => {
            if (!step.selector) return true;
            return !!(document.querySelector(step.selector) || (step.fallbackSelector && document.querySelector(step.fallbackSelector)));
        });
    }

    function openCustomTour(rawSteps, mode, options) {
        isTransitioning = false;
        stepRenderToken++;

        const list = filterAvailableSteps(Array.isArray(rawSteps) ? rawSteps : []);
        if (!list.length) return;

        activePage = CURRENT_PAGE || 'dashboard.php';
        activeTourMode = mode || 'trofeu';
        activeTourType = (mode === 'licao1_expl' || mode === 'licao1_quiz')
            ? 'licao1'
            : (mode === 'resultado1' ? 'resultado1' : activeTourMode);
        isFullTour = false;
        steps = list;
        stepOffset = (options && typeof options.stepOffset === 'number') ? options.stepOffset : 0;
        overallTotal = (options && typeof options.totalSteps === 'number') ? options.totalSteps : steps.length;

        lastFocusedEl = document.activeElement;
        isOpen = true;

        root.setAttribute('aria-hidden', 'false');
        root.classList.add('is-open');

        renderStep(0);
        setTimeout(() => {
            try { nextBtn.focus({ preventScroll: true }); } catch (e) {}
        }, 120);
    }

    function openTour(target, options) {
        isTransitioning = false;
        stepRenderToken++;

        const page = resolvePageFromTarget(target);
        activePage = page;
        activeTourType = (page === 'perfil.php') ? 'perfil' : 'principal';
        isFullTour = !!(options && options.isFullTour);
        activeTourMode = (page === 'perfil.php' && !isFullTour) ? 'perfil' : 'site';

        const rawSteps = buildPageSteps(page, isFullTour);
        steps = filterAvailableSteps(rawSteps);
        if (!steps.length) return;

        if (options && typeof options.stepOffset === 'number') {
            stepOffset = options.stepOffset;
        } else {
            stepOffset = isFullTour ? getDefaultStageOffset(page) : 0;
        }

        if (options && typeof options.totalSteps === 'number') {
            overallTotal = options.totalSteps;
        } else {
            overallTotal = isFullTour ? FULL_TOUR_TOTAL_STEPS : steps.length;
        }

        lastFocusedEl = document.activeElement;
        isOpen = true;

        root.setAttribute('aria-hidden', 'false');
        root.classList.add('is-open');

        renderStep(0);
        setTimeout(() => {
            try { nextBtn.focus({ preventScroll: true }); } catch (e) {}
        }, 120);
    }

    function closeTour(completed) {
        if (!isOpen) return;
        const wasCompleted = (completed !== false);
        const modeOnClose = activeTourMode;
        const pageOnClose = activePage;
        const fullOnClose = isFullTour;

        isOpen = false;
        isTransitioning = false;
        stepRenderToken++;
        clearTimeout(typingTimer);
        typingTimer = null;
        isTyping = false;

        try {
            sessionStorage.removeItem(SESSION_KEY_CONTINUO);
        } catch (e) {}

        if (spotlight) spotlight.classList.remove('is-auto-clicking');
        if (trophyCardEl) trophyCardEl.style.display = 'none';
        if (bubbleEl) bubbleEl.classList.remove('has-trophy');
        mascotWrap.classList.remove('is-talking');
        textEl.classList.remove('is-typing');
        root.classList.remove('is-open', 'has-spotlight', 'no-spotlight', 'is-trophy-step');
        root.setAttribute('aria-hidden', 'true');

        if (modeOnClose === 'site') {
            if (fullOnClose || pageOnClose === 'dashboard.php') {
                markTourSeen('principal');
            }
            if (pageOnClose === 'perfil.php') {
                markTourSeen('perfil');
            }
        } else if (modeOnClose === 'perfil') {
            markTourSeen('perfil');
        } else if (modeOnClose === 'licao1_expl') {
            if (wasCompleted) {
                licao1ExplDoneInSession = true;
            } else {
                licao1SkippedInSession = true;
                markTourSeen('licao1');
            }
        } else if (modeOnClose === 'licao1_quiz') {
            markTourSeen('licao1');
        } else if (modeOnClose === 'resultado1') {
            if (!cfg.errouTodas && !cfg.isTrofeuPreview) {
                markTourSeen('resultado1');
            }
        } else if (modeOnClose === 'fim_curso') {
            if (!cfg.isFimCursoPreview) {
                markTourSeen('fim_curso');
            }
            if (typeof window.abrirSpotifyWrappedModal === 'function') {
                setTimeout(() => window.abrirSpotifyWrappedModal(), 300);
            }
        }
        // Modos 'trofeu' e 'resultado_manual' não sobrescrevem flags do tour principal/lição

        if (lastFocusedEl && typeof lastFocusedEl.focus === 'function') {
            try { lastFocusedEl.focus({ preventScroll: true }); } catch (e) {}
        }
    }

    function triggerAutoNavigate(step) {
        if (isTransitioning || !step || !step.autoNavigate) return;
        const nav = step.autoNavigate;
        isTransitioning = true;
        stepRenderToken++;
        clearTimeout(typingTimer);
        typingTimer = null;
        isTyping = false;
        mascotWrap.classList.remove('is-talking');
        textEl.classList.remove('is-typing');

        if (CURRENT_PAGE === 'dashboard.php') {
            markTourSeen('principal');
        }

        const nextOffset = stepOffset + steps.length;
        const total = overallTotal || FULL_TOUR_TOTAL_STEPS;

        if (CURRENT_PAGE === nav.page) {
            openTour(nav.page, {
                isFullTour: true,
                stepOffset: nextOffset,
                totalSteps: total
            });
            return;
        }

        try {
            sessionStorage.setItem(SESSION_KEY_CONTINUO, JSON.stringify({
                fullTour: true,
                nextPage: nav.page,
                stepOffset: nextOffset,
                totalSteps: total,
                ts: Date.now()
            }));
        } catch (e) {}

        const linkEl = document.querySelector(nav.selector || step.selector);
        if (spotlight) spotlight.classList.add('is-auto-clicking');
        if (linkEl) linkEl.classList.add('opi-tour-link-clicking');

        let targetUrl = nav.page + '?tour=continuo';
        if (linkEl && linkEl.getAttribute('href')) {
            const href = linkEl.getAttribute('href');
            targetUrl = href + (href.indexOf('?') === -1 ? '?' : '&') + 'tour=continuo';
        }

        const delay = prefersReducedMotion() ? 60 : 460;
        setTimeout(function () {
            window.location.href = targetUrl;
        }, delay);
    }

    function handleNext() {
        if (isTransitioning) return;
        if (isTyping) {
            renderInstant(currentFullText);
            return;
        }
        const currentStep = steps[currentIndex];
        if (currentStep && currentStep.autoNavigate) {
            triggerAutoNavigate(currentStep);
            return;
        }
        if (currentIndex < steps.length - 1) {
            renderStep(currentIndex + 1);
        } else if (currentStep && typeof currentStep.onComplete === 'function') {
            currentStep.onComplete();
        } else {
            closeTour(true);
        }
    }

    function handlePrev() {
        if (isTransitioning) return;
        if (currentIndex > 0) {
            renderStep(currentIndex - 1);
        }
    }

    // =========================================================================
    // EVENTOS DE INTERAÇÃO E TECLADO
    // =========================================================================
    nextBtn.addEventListener('click', handleNext);
    prevBtn.addEventListener('click', handlePrev);
    skipBtn.addEventListener('click', () => {
        if (!isTransitioning) closeTour(false);
    });
    closeBtn.addEventListener('click', () => {
        if (!isTransitioning) closeTour(false);
    });

    speechBox.addEventListener('click', function () {
        if (isTyping) {
            renderInstant(currentFullText);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (!isOpen || isTransitioning) return;
        if (e.key === 'Escape') {
            e.preventDefault();
            e.stopPropagation();
            closeTour(false);
        } else if (e.key === 'ArrowRight' || e.key === 'Enter') {
            if (e.key === 'Enter' && document.activeElement && document.activeElement.classList.contains('opi-tour-btn') && document.activeElement !== nextBtn) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            handleNext();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            e.stopPropagation();
            handlePrev();
        } else if (e.key === ' ' && isTyping) {
            e.preventDefault();
            e.stopPropagation();
            renderInstant(currentFullText);
        }
    });

    function scheduleReposition() {
        if (!isOpen) return;
        if (rafId) cancelAnimationFrame(rafId);
        rafId = requestAnimationFrame(function () {
            if (isOpen && steps[currentIndex]) {
                positionStageAndSpotlight(steps[currentIndex]);
            }
        });
    }

    window.addEventListener('resize', scheduleReposition);
    window.addEventListener('scroll', scheduleReposition, true);

    function startLessonTutor(phase) {
        const targetPhase = phase || (function () {
            const qp = document.getElementById('quizPhase');
            return (qp && qp.style.display !== 'none') ? 'quiz' : 'expl';
        })();

        if (targetPhase === 'quiz') {
            openCustomTour(buildLicao1QuizSteps(), 'licao1_quiz');
        } else {
            openCustomTour(buildLicao1ExplSteps(), 'licao1_expl');
        }
    }

    function startResultadoTutor() {
        openCustomTour(buildResultado1Steps(), 'resultado1');
    }

    function startCursoFinalizadoTutor() {
        openCustomTour(buildCursoFinalizadoSteps(), 'fim_curso');
    }

    function celebrateTrophies(trophies) {
        if (!Array.isArray(trophies) || !trophies.length) return;
        openCustomTour(buildTrophySteps(trophies), 'trofeu');
    }

    // =========================================================================
    // API GLOBAL + BOTÃO DA TOPBAR + AUTO-INÍCIO / CONTINUAÇÃO CONTÍNUA
    // =========================================================================
    window.OpusTour = {
        isOpen: function () {
            return isOpen;
        },
        start: function (forceType) {
            if (forceType === 'perfil') {
                openTour('perfil.php', { isFullTour: false });
            } else if (forceType === 'principal' || (!forceType && CURRENT_PAGE === 'dashboard.php')) {
                openTour('dashboard.php', { isFullTour: true });
            } else if (CURRENT_PAGE === 'licao.php') {
                if (cfg.isPrimeiraLicao) {
                    startLessonTutor();
                } else {
                    openTour('dashboard.php', { isFullTour: false });
                }
            } else if (CURRENT_PAGE === 'resultado.php') {
                if (cfg.isCursoFinalizado) {
                    startCursoFinalizadoTutor();
                } else if (cfg.isPrimeiraLicao) {
                    startResultadoTutor();
                } else if (Array.isArray(cfg.novosTrofeus) && cfg.novosTrofeus.length > 0) {
                    celebrateTrophies(cfg.novosTrofeus);
                } else {
                    openCustomTour(buildResultadoManualSteps(), 'resultado_manual');
                }
            } else {
                openTour(CURRENT_PAGE || 'dashboard.php', { isFullTour: false });
            }
        },
        startPrincipal: function () {
            if (CURRENT_PAGE === 'dashboard.php') {
                openTour('dashboard.php', { isFullTour: true });
            } else {
                window.location.href = 'dashboard.php?tour=1';
            }
        },
        startPerfil: function () {
            if (CURRENT_PAGE === 'perfil.php') {
                openTour('perfil.php', { isFullTour: false });
            } else {
                window.location.href = 'perfil.php?tour=1';
            }
        },
        startLessonTutor: startLessonTutor,
        startResultadoTutor: startResultadoTutor,
        startCursoFinalizadoTutor: startCursoFinalizadoTutor,
        celebrateTrophies: celebrateTrophies,
        stop: function () {
            closeTour(false);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        const topbarTourBtn = document.getElementById('btnAbrirTourOpi');
        if (topbarTourBtn) {
            topbarTourBtn.addEventListener('click', function (e) {
                e.preventDefault();
                window.OpusTour.start();
            });
        }

        const btnTutorLicao1Expl = document.getElementById('btnTutorLicao1Expl');
        if (btnTutorLicao1Expl) {
            btnTutorLicao1Expl.addEventListener('click', function (e) {
                e.preventDefault();
                startLessonTutor('expl');
            });
        }

        const btnTutorLicao1Quiz = document.getElementById('btnTutorLicao1Quiz');
        if (btnTutorLicao1Quiz) {
            btnTutorLicao1Quiz.addEventListener('click', function (e) {
                e.preventDefault();
                startLessonTutor('quiz');
            });
        }

        const params = new URLSearchParams(window.location.search);
        const tourParam = params.get('tour');

        // Fluxo dedicado da 1ª lição (licao.php?cap=1&licao=1)
        if (CURRENT_PAGE === 'licao.php') {
            if (tourParam === '1') {
                params.delete('tour');
                const newQuery = params.toString();
                const cleanUrl = window.location.pathname + (newQuery ? '?' + newQuery : '') + window.location.hash;
                try {
                    window.history.replaceState({}, document.title, cleanUrl);
                } catch (e) {}
            }

            if (cfg.isPrimeiraLicao) {
                let localLicao1Seen = false;
                try { localLicao1Seen = localStorage.getItem(STORAGE_KEY_LICAO1) === '1'; } catch (e) {}

                const initialPhase = (cfg.totalSlides && cfg.totalSlides > 0) ? 'expl' : 'quiz';
                if (tourParam === '1') {
                    forceLicao1QuizTour = true;
                    setTimeout(() => startLessonTutor(initialPhase), 480);
                } else if (!cfg.tourLicao1Visto && !localLicao1Seen) {
                    setTimeout(() => startLessonTutor(initialPhase), 520);
                }

                window.addEventListener('opus:lesson-quiz-start', function () {
                    let seenNow = false;
                    try { seenNow = localStorage.getItem(STORAGE_KEY_LICAO1) === '1'; } catch (e) {}
                    if (forceLicao1QuizTour || licao1ExplDoneInSession || (!cfg.tourLicao1Visto && !seenNow && !licao1SkippedInSession)) {
                        forceLicao1QuizTour = false;
                        setTimeout(() => startLessonTutor('quiz'), 420);
                    }
                });
            }
            return;
        }

        // Fluxo dedicado do resultado: explica a 1ª lição ou comemora troféus a partir da 2ª
        if (CURRENT_PAGE === 'resultado.php') {
            if (tourParam === '1') {
                params.delete('tour');
                const newQuery = params.toString();
                const cleanUrl = window.location.pathname + (newQuery ? '?' + newQuery : '') + window.location.hash;
                try {
                    window.history.replaceState({}, document.title, cleanUrl);
                } catch (e) {}
            }

            let localFimCursoSeen = false;
            try { localFimCursoSeen = localStorage.getItem(STORAGE_KEY_FIM_CURSO) === '1'; } catch (e) {}

            if (cfg.isCursoFinalizado) {
                if (tourParam === '1' || cfg.isFimCursoPreview || (!cfg.tourFimCursoVisto && !localFimCursoSeen)) {
                    setTimeout(() => startCursoFinalizadoTutor(), 650);
                } else {
                    setTimeout(() => {
                        if (typeof window.abrirSpotifyWrappedModal === 'function') {
                            window.abrirSpotifyWrappedModal();
                        }
                    }, 500);
                }
                return;
            }

            let localRes1Seen = false;
            try { localRes1Seen = localStorage.getItem(STORAGE_KEY_RESULTADO1) === '1'; } catch (e) {}

            if (cfg.isPrimeiraLicao && (tourParam === '1' || (!cfg.tourResultado1Visto && !localRes1Seen))) {
                setTimeout(() => startResultadoTutor(), 650);
            } else if (Array.isArray(cfg.novosTrofeus) && cfg.novosTrofeus.length > 0) {
                setTimeout(() => celebrateTrophies(cfg.novosTrofeus), 650);
            }
            return;
        }

        // Na IDE de Desafios não inicia nada automaticamente (abre apenas via celebrateTrophies)
        if (CURRENT_PAGE === 'desafio_ide.php') {
            return;
        }

        // Continuação automática em qualquer etapa da sequência do tour completo
        let savedContinuo = null;
        try {
            const rawContinuo = sessionStorage.getItem(SESSION_KEY_CONTINUO);
            if (rawContinuo) {
                const parsed = JSON.parse(rawContinuo);
                if (parsed && parsed.ts && (Date.now() - parsed.ts < 30000)) {
                    if (!parsed.nextPage || parsed.nextPage === CURRENT_PAGE) {
                        savedContinuo = parsed;
                    }
                }
            }
            if (tourParam === 'continuo' || savedContinuo) {
                sessionStorage.removeItem(SESSION_KEY_CONTINUO);
            }
        } catch (e) {}

        if (tourParam === 'continuo' || savedContinuo) {
            if (tourParam) {
                params.delete('tour');
                const newQuery = params.toString();
                const cleanUrl = window.location.pathname + (newQuery ? '?' + newQuery : '') + window.location.hash;
                try {
                    window.history.replaceState({}, document.title, cleanUrl);
                } catch (e) {}
            }

            const offset = (savedContinuo && typeof savedContinuo.stepOffset === 'number')
                ? savedContinuo.stepOffset
                : getDefaultStageOffset(CURRENT_PAGE);
            const total = (savedContinuo && typeof savedContinuo.totalSteps === 'number')
                ? savedContinuo.totalSteps
                : FULL_TOUR_TOTAL_STEPS;

            setTimeout(() => {
                openTour(CURRENT_PAGE, {
                    isFullTour: true,
                    stepOffset: offset,
                    totalSteps: total
                });
            }, 420);
            return;
        }

        // Verifica parâmetro ?tour=1 na URL
        if (tourParam === '1') {
            params.delete('tour');
            const newQuery = params.toString();
            const cleanUrl = window.location.pathname + (newQuery ? '?' + newQuery : '') + window.location.hash;
            try {
                window.history.replaceState({}, document.title, cleanUrl);
            } catch (e) {}

            setTimeout(() => {
                openTour(CURRENT_PAGE || 'dashboard.php', {
                    isFullTour: CURRENT_PAGE === 'dashboard.php'
                });
            }, 450);
            return;
        }

        // Auto-início na primeira entrada no dashboard.php
        if (CURRENT_PAGE === 'dashboard.php') {
            let localSeen = false;
            try { localSeen = localStorage.getItem(STORAGE_KEY_MAIN) === '1'; } catch (e) {}
            if (!cfg.tourVisto && !localSeen) {
                setTimeout(() => openTour('dashboard.php', { isFullTour: true }), 650);
            }
        }

        // Auto-início na primeira entrada em perfil.php
        if (CURRENT_PAGE === 'perfil.php') {
            let localPerfilSeen = false;
            try { localPerfilSeen = localStorage.getItem(STORAGE_KEY_PERFIL) === '1'; } catch (e) {}
            if (!cfg.tourPerfilVisto && !localPerfilSeen) {
                setTimeout(() => openTour('perfil.php', { isFullTour: false }), 650);
            }
        }
    });
})(window, document);
