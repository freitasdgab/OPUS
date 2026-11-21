/**
 * OPUS SPOTIFY WRAPPED - CONTROLADOR DO WIDGET & EXPORTAÇÃO EM PNG
 * Gerenciamento interativo do card com áudio sintetizado, download em alta resolução e compartilhamento.
 */

(function (window, document) {
    'use strict';

    let audioContext = null;
    let isPlayingSynth = false;
    let synthInterval = null;

    function getAudioContext() {
        if (!audioContext) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) audioContext = new AudioCtx();
        }
        if (audioContext && audioContext.state === 'suspended') {
            audioContext.resume().catch(() => {});
        }
        return audioContext;
    }

    // Melodia alegre e comemorativa sintetizada estilo 8-bit / Synthwave
    function playCelebratoryMelody() {
        try {
            const ctx = getAudioContext();
            if (!ctx) return;

            // Notas da vitória em frequência (Hz): C5, E5, G5, A5, C6 (Acorde Maior Festivo)
            const notes = [
                { f: 523.25, d: 0.12, pause: 0.05 },
                { f: 659.25, d: 0.12, pause: 0.05 },
                { f: 783.99, d: 0.12, pause: 0.05 },
                { f: 880.00, d: 0.15, pause: 0.06 },
                { f: 1046.50, d: 0.45, pause: 0.10 }
            ];

            let currTime = ctx.currentTime + 0.05;
            notes.forEach(n => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(n.f, currTime);

                gain.gain.setValueAtTime(0.001, currTime);
                gain.gain.exponentialRampToValueAtTime(0.22, currTime + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, currTime + n.d);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(currTime);
                osc.stop(currTime + n.d + 0.05);

                currTime += n.d + n.pause;
            });
        } catch (e) {
            console.warn('Erro ao sintetizar áudio comemorativo:', e);
        }
    }

    // Loop comemorativo sintetizado para o botão Play do Spotify
    function startSpotifySynthLoop() {
        if (isPlayingSynth) return;
        isPlayingSynth = true;

        const loopNotes = [523.25, 659.25, 783.99, 1046.50, 880.00, 783.99, 659.25, 783.99];
        let idx = 0;

        playCelebratoryMelody();

        synthInterval = setInterval(() => {
            if (!isPlayingSynth) return;
            try {
                const ctx = getAudioContext();
                if (!ctx) return;
                const freq = loopNotes[idx % loopNotes.length];
                idx++;

                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, ctx.currentTime);

                gain.gain.setValueAtTime(0.001, ctx.currentTime);
                gain.gain.linearRampToValueAtTime(0.12, ctx.currentTime + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.22);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + 0.25);
            } catch (e) {}
        }, 280);
    }

    function stopSpotifySynthLoop() {
        isPlayingSynth = false;
        if (synthInterval) {
            clearInterval(synthInterval);
            synthInterval = null;
        }
    }

    // =========================================================================
    // CONFETES CELEBRATÓRIOS NATIVOS
    // =========================================================================
    function launchConfettiBurst() {
        const colors = ['#1db954', '#8b5cf6', '#1cb0f6', '#ffc800', '#ff527b', '#ffffff'];
        const container = document.createElement('div');
        container.style.position = 'fixed';
        container.style.inset = '0';
        container.style.pointerEvents = 'none';
        container.style.zIndex = '200005';
        container.style.overflow = 'hidden';
        document.body.appendChild(container);

        const count = 75;
        for (let i = 0; i < count; i++) {
            const piece = document.createElement('div');
            const color = colors[Math.floor(Math.random() * colors.length)];
            const size = Math.floor(Math.random() * 8) + 6;
            const startX = Math.random() * 100;

            piece.style.position = 'absolute';
            piece.style.top = '-20px';
            piece.style.left = startX + 'vw';
            piece.style.width = size + 'px';
            piece.style.height = (size * (Math.random() > 0.5 ? 1 : 2.5)) + 'px';
            piece.style.backgroundColor = color;
            piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            piece.style.opacity = (Math.random() * 0.7 + 0.3).toString();
            piece.style.transform = `rotate(${Math.random() * 360}deg)`;

            const duration = Math.random() * 2.5 + 2;
            const delay = Math.random() * 0.8;

            piece.style.transition = `transform ${duration}s cubic-bezier(0.25, 0.46, 0.45, 0.94) ${delay}s, top ${duration}s ease-in ${delay}s, opacity ${duration}s ease-out ${delay}s`;

            container.appendChild(piece);

            setTimeout(() => {
                const endX = (Math.random() - 0.5) * 200;
                piece.style.top = '105vh';
                piece.style.transform = `translateX(${endX}px) rotate(${Math.random() * 1080}deg)`;
                piece.style.opacity = '0';
            }, 40);
        }

        setTimeout(() => {
            if (container.parentNode) container.parentNode.removeChild(container);
        }, 5000);
    }

    // =========================================================================
    // EXIBIÇÃO DE TOAST
    // =========================================================================
    function showToast(message) {
        let toast = document.getElementById('spToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'spToast';
            toast.className = 'sp-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> <span>${message}</span>`;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3200);
    }

    // =========================================================================
    // EXPORTAÇÃO CANVAS 2D ULTRA DEFINIÇÃO (1080 x 1350 PNG)
    // =========================================================================
    function generateSpotifyCardCanvas() {
        return new Promise((resolve) => {
            const cardEl = document.getElementById('spotifyCardExport');
            const width = 1080;
            const height = 1350;
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');

            // 1. Fundo Gradiente Spotify Wrapped Pro
            const bgGrad = ctx.createLinearGradient(0, 0, width, height);
            bgGrad.addColorStop(0, '#151920');
            bgGrad.addColorStop(0.5, '#0e1115');
            bgGrad.addColorStop(1, '#080a0c');
            ctx.fillStyle = bgGrad;
            ctx.fillRect(0, 0, width, height);

            // Auras de iluminação neon (Verde Spotify, Roxo Opus, Azul)
            const radGreen = ctx.createRadialGradient(220, 260, 40, 220, 260, 420);
            radGreen.addColorStop(0, 'rgba(29, 185, 84, 0.35)');
            radGreen.addColorStop(1, 'transparent');
            ctx.fillStyle = radGreen;
            ctx.fillRect(0, 0, width, height);

            const radPurple = ctx.createRadialGradient(880, 1100, 60, 880, 1100, 500);
            radPurple.addColorStop(0, 'rgba(139, 92, 246, 0.38)');
            radPurple.addColorStop(1, 'transparent');
            ctx.fillStyle = radPurple;
            ctx.fillRect(0, 0, width, height);

            // Moldura externa com cantos arredondados
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
            ctx.lineWidth = 4;
            if (typeof ctx.roundRect === 'function') {
                ctx.roundRect(40, 40, width - 80, height - 80, 44);
                ctx.stroke();
            } else {
                ctx.strokeRect(40, 40, width - 80, height - 80);
            }

            // 2. Cabeçalho do Card
            ctx.fillStyle = '#ffffff';
            ctx.font = '900 36px "Nunito", sans-serif';
            ctx.fillText('OPUS WRAPPED', 100, 125);

            // Ponto verde Spotify
            ctx.fillStyle = '#1db954';
            ctx.beginPath();
            ctx.arc(385, 115, 8, 0, Math.PI * 2);
            ctx.fill();

            // Pílula "CURSO FINALIZADO"
            const pillX = 660;
            const pillY = 90;
            const pillW = 320;
            const pillH = 50;
            ctx.fillStyle = '#1db954';
            if (typeof ctx.roundRect === 'function') {
                ctx.beginPath();
                ctx.roundRect(pillX, pillY, pillW, pillH, 25);
                ctx.fill();
            }
            ctx.fillStyle = '#ffffff';
            ctx.font = '900 22px "Nunito", sans-serif';
            ctx.fillText('CURSO CONCLUÍDO • 100%', pillX + 24, pillY + 34);

            // 3. Moldura Central / Capa de Disco (Cover Stage)
            const coverX = 140;
            const coverY = 180;
            const coverSize = 800;

            const coverGrad = ctx.createLinearGradient(coverX, coverY, coverX + coverSize, coverY + coverSize);
            coverGrad.addColorStop(0, '#1c222b');
            coverGrad.addColorStop(1, '#11141a');
            ctx.fillStyle = coverGrad;
            if (typeof ctx.roundRect === 'function') {
                ctx.beginPath();
                ctx.roundRect(coverX, coverY, coverSize, 560, 36);
                ctx.fill();
            }

            // Círculos concêntricos do disco de vinil no fundo da capa
            const vinylCenterX = coverX + (coverSize / 2);
            const vinylCenterY = coverY + 280;
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)';
            ctx.lineWidth = 3;
            for (let r = 70; r < 240; r += 26) {
                ctx.beginPath();
                ctx.arc(vinylCenterX, vinylCenterY, r, 0, Math.PI * 2);
                ctx.stroke();
            }

            // Foto ou Avatar do Aluno no Miolo
            const avatarImg = document.getElementById('spAvatarImg');
            const avatarR = 110;

            ctx.save();
            ctx.beginPath();
            ctx.arc(vinylCenterX, vinylCenterY, avatarR, 0, Math.PI * 2);
            ctx.closePath();
            ctx.clip();

            if (avatarImg && avatarImg.complete && avatarImg.naturalWidth > 0) {
                try {
                    ctx.drawImage(avatarImg, vinylCenterX - avatarR, vinylCenterY - avatarR, avatarR * 2, avatarR * 2);
                } catch (e) {
                    ctx.fillStyle = '#1cb0f6';
                    ctx.fillRect(vinylCenterX - avatarR, vinylCenterY - avatarR, avatarR * 2, avatarR * 2);
                }
            } else {
                ctx.fillStyle = '#1cb0f6';
                ctx.fillRect(vinylCenterX - avatarR, vinylCenterY - avatarR, avatarR * 2, avatarR * 2);
            }
            ctx.restore();

            // Borda branca brilhante ao redor do avatar
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 8;
            ctx.beginPath();
            ctx.arc(vinylCenterX, vinylCenterY, avatarR, 0, Math.PI * 2);
            ctx.stroke();

            // Mascote Opi no canto da capa
            const opiImg = document.getElementById('spOpiImg');
            if (opiImg && opiImg.complete && opiImg.naturalWidth > 0) {
                try {
                    ctx.drawImage(opiImg, coverX + coverSize - 190, coverY + 380, 160, 160);
                } catch (e) {}
            }

            // 4. Detalhes da Faixa Musical
            const studentName = (document.getElementById('spStudentName')?.textContent || 'Estudante Opus').trim();
            const studentUser = (document.getElementById('spStudentUser')?.textContent || '@estudante').trim();

            ctx.fillStyle = '#ffffff';
            ctx.font = '900 48px "Nunito", sans-serif';
            ctx.fillText('Graduado em Java & Lógica', 100, 815);

            ctx.fillStyle = '#b3b3b3';
            ctx.font = '700 32px "Nunito", sans-serif';
            ctx.fillText(`${studentName} (${studentUser}) • Turma 2026`, 100, 865);

            ctx.fillStyle = '#71767b';
            ctx.font = '600 24px "Nunito", sans-serif';
            ctx.fillText('OPUS Bootcamp: Edição Completa • 40 Seções Concluídas', 100, 905);

            // Coração verde Spotify
            ctx.fillStyle = '#1db954';
            ctx.font = '36px "Font Awesome 6 Free", sans-serif';
            ctx.fillText('❤', 940, 825);

            // 5. Barra do Player (100%)
            const barY = 945;
            ctx.fillStyle = '#333842';
            if (typeof ctx.roundRect === 'function') {
                ctx.beginPath();
                ctx.roundRect(100, barY, 880, 12, 6);
                ctx.fill();
            }

            // Progresso verde cheio
            const fillGrad = ctx.createLinearGradient(100, barY, 980, barY);
            fillGrad.addColorStop(0, '#1cb0f6');
            fillGrad.addColorStop(0.5, '#8b5cf6');
            fillGrad.addColorStop(1, '#1db954');
            ctx.fillStyle = fillGrad;
            if (typeof ctx.roundRect === 'function') {
                ctx.beginPath();
                ctx.roundRect(100, barY, 880, 12, 6);
                ctx.fill();
            }

            // Tempos
            ctx.fillStyle = '#b3b3b3';
            ctx.font = '700 20px "Nunito", sans-serif';
            ctx.fillText('40:00', 100, barY + 36);
            ctx.fillText('40:00 (100%)', 870, barY + 36);

            // 6. Grade de Estatísticas
            const stats = [
                { val: document.getElementById('spValXp')?.textContent || '14.500 XP', lbl: 'XP TOTAL' },
                { val: document.getElementById('spValStreak')?.textContent || '21 Dias', lbl: 'OFENSIVA' },
                { val: document.getElementById('spValTrophy')?.textContent || '15 Troféus', lbl: 'TROFÉUS' },
                { val: document.getElementById('spValLessons')?.textContent || '200 / 200', lbl: 'LIÇÕES FEITAS' }
            ];

            const gridY = 1010;
            const colW = 425;
            const rowH = 95;

            stats.forEach((st, idx) => {
                const col = idx % 2;
                const row = Math.floor(idx / 2);
                const sX = 100 + col * (colW + 30);
                const sY = gridY + row * (rowH + 20);

                ctx.fillStyle = 'rgba(255, 255, 255, 0.05)';
                ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
                ctx.lineWidth = 2;
                if (typeof ctx.roundRect === 'function') {
                    ctx.beginPath();
                    ctx.roundRect(sX, sY, colW, rowH, 20);
                    ctx.fill();
                    ctx.stroke();
                }

                ctx.fillStyle = '#ffffff';
                ctx.font = '900 30px "Nunito", sans-serif';
                ctx.fillText(st.val, sX + 30, sY + 45);

                ctx.fillStyle = '#1db954';
                ctx.font = '800 18px "Nunito", sans-serif';
                ctx.fillText(st.lbl, sX + 30, sY + 75);
            });

            // 7. Código de Barras Sonoro Spotify
            const codeY = 1245;
            ctx.fillStyle = '#05070a';
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
            ctx.lineWidth = 2;
            if (typeof ctx.roundRect === 'function') {
                ctx.beginPath();
                ctx.roundRect(100, codeY, 880, 56, 18);
                ctx.fill();
                ctx.stroke();
            }

            ctx.fillStyle = '#1db954';
            ctx.font = '900 24px "Nunito", sans-serif';
            ctx.fillText('OPUS', 130, codeY + 36);

            // Barras verticais do código
            const barHeights = [10, 24, 38, 14, 32, 42, 18, 30, 44, 22, 12, 36, 40, 16, 28, 44, 20, 14, 34, 42, 26, 12, 28, 40, 18, 32, 10];
            const startBarX = 250;
            ctx.fillStyle = '#ffffff';
            barHeights.forEach((bh, i) => {
                const bX = startBarX + i * 16;
                const bY = codeY + (56 - bh) / 2;
                if (typeof ctx.roundRect === 'function') {
                    ctx.beginPath();
                    ctx.roundRect(bX, bY, 6, bh, 3);
                    ctx.fill();
                } else {
                    ctx.fillRect(bX, bY, 6, bh);
                }
            });

            ctx.fillStyle = '#a0aec0';
            ctx.font = '800 18px "Nunito", sans-serif';
            ctx.fillText('OPUS.DEV/FORMATURA', 740, codeY + 36);

            resolve(canvas);
        });
    }

    // =========================================================================
    // BAIXAR CARD (PNG)
    // =========================================================================
    async function baixarSpotifyCard() {
        try {
            const btn = document.getElementById('spBtnDownload');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gerando Imagem...';
                btn.disabled = true;
            }

            const canvas = await generateSpotifyCardCanvas();
            const studentUser = (document.getElementById('spStudentUser')?.textContent || 'estudante').replace('@', '').trim();
            const fileName = `opus-spotify-wrapped-${studentUser || 'formando'}.png`;

            // Download automático via elemento âncora
            const imageURL = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.download = fileName;
            link.href = imageURL;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showToast('Card baixado com sucesso em alta resolução!');

            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-download"></i> Baixar Card (PNG)';
                btn.disabled = false;
            }
        } catch (e) {
            console.error('Erro ao baixar card:', e);
            showToast('Erro ao gerar imagem. Tente novamente.');
            const btn = document.getElementById('spBtnDownload');
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-download"></i> Baixar Card (PNG)';
                btn.disabled = false;
            }
        }
    }

    // =========================================================================
    // COMPARTILHAR CARD (WEB SHARE API + CLIPBOARD)
    // =========================================================================
    async function compartilharSpotifyCard() {
        const studentName = (document.getElementById('spStudentName')?.textContent || 'Estudante').trim();
        const shareText = `🎓 Concluí o curso completo de programação do OPUS! 40 seções zeradas, desafios práticos no terminal e 100% de aproveitamento. Confira meu Spotify Wrapped de formatura! 🚀 #OPUS #Java #Dev`;
        const shareUrl = window.location.origin + window.location.pathname;

        try {
            // Tenta compartilhar nativamente com imagem caso suportado
            const canvas = await generateSpotifyCardCanvas();
            canvas.toBlob(async (blob) => {
                if (blob && navigator.canShare && navigator.canShare({ files: [new File([blob], 'opus-formatura.png', { type: 'image/png' })] })) {
                    try {
                        const file = new File([blob], 'opus-spotify-wrapped.png', { type: 'image/png' });
                        await navigator.share({
                            title: 'OPUS Wrapped - Curso Concluído!',
                            text: shareText,
                            files: [file]
                        });
                        showToast('Compartilhado com sucesso!');
                        return;
                    } catch (err) {
                        // Se o usuário cancelou o share nativo, não faz nada
                        if (err.name === 'AbortError') return;
                    }
                }

                // Fallback: Compartilhamento de texto nativo
                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: 'OPUS Wrapped - Curso Concluído!',
                            text: shareText,
                            url: shareUrl
                        });
                        showToast('Compartilhado com sucesso!');
                        return;
                    } catch (err) {
                        if (err.name === 'AbortError') return;
                    }
                }

                // Fallback final: Copiar para área de transferência
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(`${shareText}\n${shareUrl}`);
                    showToast('Texto e link copiados para a área de transferência!');
                } else {
                    showToast('Link do certificado copiado!');
                }
            }, 'image/png');
        } catch (e) {
            console.warn('Erro ao compartilhar:', e);
            showToast('Texto copiado para a área de transferência!');
        }
    }

    // =========================================================================
    // INTERAÇÕES GLOBAIS DO MODAL
    // =========================================================================
    window.abrirSpotifyWrappedModal = function () {
        const modal = document.getElementById('modalSpotifyWrapped');
        if (!modal) return;

        modal.classList.add('is-active');
        document.body.style.overflow = 'hidden';

        launchConfettiBurst();
        playCelebratoryMelody();

        // Inicia tocando o sintetizador festivo automaticamente
        const card = document.getElementById('spotifyCardExport');
        const playBtn = document.getElementById('spBtnPlay');
        if (card && playBtn) {
            card.classList.remove('is-paused');
            playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
            startSpotifySynthLoop();
        }
    };

    window.fecharSpotifyWrappedModal = function () {
        const modal = document.getElementById('modalSpotifyWrapped');
        if (!modal) return;

        modal.classList.remove('is-active');
        document.body.style.overflow = '';
        stopSpotifySynthLoop();
    };

    // =========================================================================
    // INICIALIZAÇÃO DOS EVENTOS DO DOM
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalSpotifyWrapped');
        const closeBtn = document.getElementById('spBtnCloseModal');
        const downloadBtn = document.getElementById('spBtnDownload');
        const shareBtn = document.getElementById('spBtnShare');
        const playBtn = document.getElementById('spBtnPlay');
        const card = document.getElementById('spotifyCardExport');
        const openBannerBtn = document.getElementById('btnAbrirSpotifyBanner');
        const openCardBtn = document.getElementById('btnAbrirSpotifyCardGeral');

        if (closeBtn) closeBtn.addEventListener('click', window.fecharSpotifyWrappedModal);

        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) window.fecharSpotifyWrappedModal();
            });
        }

        if (downloadBtn) downloadBtn.addEventListener('click', baixarSpotifyCard);
        if (shareBtn) shareBtn.addEventListener('click', compartilharSpotifyCard);

        if (openBannerBtn) openBannerBtn.addEventListener('click', window.abrirSpotifyWrappedModal);
        if (openCardBtn) openCardBtn.addEventListener('click', window.abrirSpotifyWrappedModal);

        // Botão Play/Pause do Player
        if (playBtn && card) {
            playBtn.addEventListener('click', function () {
                if (isPlayingSynth) {
                    stopSpotifySynthLoop();
                    card.classList.add('is-paused');
                    playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
                } else {
                    card.classList.remove('is-paused');
                    playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
                    startSpotifySynthLoop();
                }
            });
        }

        // Tecla ESC para fechar modal
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const m = document.getElementById('modalSpotifyWrapped');
                if (m && m.classList.contains('is-active')) {
                    window.fecharSpotifyWrappedModal();
                }
            }
        });
    });

})(window, document);
