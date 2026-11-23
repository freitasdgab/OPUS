/**
 * OPUS CERTIFICADO OFICIAL DE CONCLUSÃO - CONTROLADOR & EXPORTAÇÃO HD
 * Gerenciamento do modal <dialog>, troca de tema, impressão PDF,
 * compartilhamento e renderização em Canvas 2D (1600 x 1131 px - A4 Paisagem).
 */

(function (window, document) {
    'use strict';

    let audioContext = null;
    let currentTheme = 'ivory';
    let isCertificateIssued = false;
    let nameFieldEdited = false;
    let formSubmitAttempted = false;

    const CERT_THEMES = {
        ivory: {
            bgCenter: '#fffdf8',
            bgEdge: '#f3ead6',
            outerBand: '#0f1d3a',
            ink: '#0f1d3a',
            inkSoft: '#43506b',
            accent: '#1453b8',
            gold: '#b8892c',
            goldStrong: '#8c6417',
            goldBright: '#f4da84',
            metricBg: 'rgba(15, 29, 58, 0.035)',
            metricBorder: 'rgba(184, 137, 44, 0.38)',
            watermarkAlpha: 0.055,
            stripeAlpha: 0.025
        },
        obsidian: {
            bgCenter: '#142240',
            bgEdge: '#070d1b',
            outerBand: '#1a2c50',
            ink: '#fbf6eb',
            inkSoft: '#b8c5dc',
            accent: '#56c8ff',
            gold: '#e2bd62',
            goldStrong: '#f3d98a',
            goldBright: '#fff1bc',
            metricBg: 'rgba(255, 255, 255, 0.045)',
            metricBorder: 'rgba(226, 189, 98, 0.38)',
            watermarkAlpha: 0.075,
            stripeAlpha: 0.03
        }
    };

    // =========================================================================
    // ÁUDIO CERIMONIAL (FANFARRA BREVE E ELEGANTE)
    // =========================================================================
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

    function playCelebratoryMelody() {
        try {
            const ctx = getAudioContext();
            if (!ctx) return;

            const notes = [
                { f: 523.25, d: 0.14, pause: 0.04 },
                { f: 659.25, d: 0.14, pause: 0.04 },
                { f: 783.99, d: 0.14, pause: 0.04 },
                { f: 987.77, d: 0.16, pause: 0.05 },
                { f: 1046.50, d: 0.55, pause: 0.1 }
            ];

            let currTime = ctx.currentTime + 0.04;
            notes.forEach((n) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'triangle';
                osc.frequency.setValueAtTime(n.f, currTime);

                gain.gain.setValueAtTime(0.001, currTime);
                gain.gain.exponentialRampToValueAtTime(0.18, currTime + 0.025);
                gain.gain.exponentialRampToValueAtTime(0.0001, currTime + n.d);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(currTime);
                osc.stop(currTime + n.d + 0.04);

                currTime += n.d + n.pause;
            });
        } catch (e) {
            console.warn('Erro ao sintetizar fanfarra comemorativa:', e);
        }
    }

    // =========================================================================
    // CONFETES CERIMONIAIS (DENTRO DO TOP LAYER DO DIALOG)
    // =========================================================================
    function launchConfettiBurst(hostEl) {
        const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion) return;

        const targetHost = hostEl || document.body;
        const colors = ['#ebd07f', '#d4af37', '#fff4cc', '#1d63dc', '#56c8ff', '#ffffff'];
        const container = document.createElement('div');
        container.style.position = 'fixed';
        container.style.inset = '0';
        container.style.pointerEvents = 'none';
        container.style.zIndex = '50';
        container.style.overflow = 'hidden';
        targetHost.appendChild(container);

        const count = 64;
        for (let i = 0; i < count; i++) {
            const piece = document.createElement('div');
            const color = colors[Math.floor(Math.random() * colors.length)];
            const size = Math.floor(Math.random() * 7) + 5;
            const startX = Math.random() * 100;

            piece.style.position = 'absolute';
            piece.style.top = '-20px';
            piece.style.left = startX + 'vw';
            piece.style.width = size + 'px';
            piece.style.height = (size * (Math.random() > 0.45 ? 1 : 2.2)) + 'px';
            piece.style.backgroundColor = color;
            piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            piece.style.opacity = (Math.random() * 0.65 + 0.35).toString();
            piece.style.transform = `rotate(${Math.random() * 360}deg)`;

            const duration = Math.random() * 2.3 + 1.9;
            const delay = Math.random() * 0.7;

            piece.style.transition = `transform ${duration}s cubic-bezier(0.25, 0.46, 0.45, 0.94) ${delay}s, top ${duration}s ease-in ${delay}s, opacity ${duration}s ease-out ${delay}s`;
            container.appendChild(piece);

            setTimeout(() => {
                const endX = (Math.random() - 0.5) * 180;
                piece.style.top = '105vh';
                piece.style.transform = `translateX(${endX}px) rotate(${Math.random() * 960}deg)`;
                piece.style.opacity = '0';
            }, 35);
        }

        setTimeout(() => {
            if (container.parentNode) container.parentNode.removeChild(container);
        }, 4600);
    }

    // =========================================================================
    // TOAST DE NOTIFICAÇÃO
    // =========================================================================
    let toastTimer = null;

    function showToast(message) {
        const modal = document.getElementById('modalSpotifyWrapped');
        let toast = document.getElementById('spToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'spToast';
            toast.className = 'sp-toast';
            toast.setAttribute('role', 'status');
            toast.setAttribute('aria-live', 'polite');
            (modal && modal.open ? modal : document.body).appendChild(toast);
        }
        toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> <span>${message}</span>`;
        toast.classList.add('show');

        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3200);
    }

    // =========================================================================
    // UTILITÁRIOS DE RENDERIZAÇÃO NO CANVAS 2D
    // =========================================================================
    async function waitForCertificateFonts() {
        if (!document.fonts || typeof document.fonts.load !== 'function') return;
        const specs = [
            '700 72px "Cinzel"',
            '800 26px "Cinzel"',
            '700 70px "Playfair Display"',
            'italic 500 25px "Playfair Display"',
            '400 54px "Great Vibes"',
            '400 22px "Poppins"',
            '700 18px "Poppins"'
        ];
        const timeout = new Promise((resolve) => setTimeout(resolve, 2500));
        await Promise.race([
            Promise.all([
                document.fonts.ready,
                ...specs.map((s) => document.fonts.load(s).catch(() => null))
            ]),
            timeout
        ]);
    }

    function loadLogoImage() {
        return new Promise((resolve) => {
            const existing = document.getElementById('certLogoImg');
            if (existing && existing.complete && existing.naturalWidth > 0) {
                resolve(existing);
                return;
            }
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = () => resolve(null);
            img.src = existing ? existing.src : '../assets/img/LOGO.png';
        });
    }

    function makeGoldGradient(ctx, x0, y0, x1, y1) {
        const g = ctx.createLinearGradient(x0, y0, x1, y1);
        g.addColorStop(0, '#9e7322');
        g.addColorStop(0.28, '#f4da84');
        g.addColorStop(0.52, '#c89b3c');
        g.addColorStop(0.76, '#fff2bd');
        g.addColorStop(1, '#9a6f1d');
        return g;
    }

    function measureTrackedText(ctx, text, spacing) {
        if (!text) return 0;
        let total = 0;
        for (let i = 0; i < text.length; i++) {
            total += ctx.measureText(text[i]).width;
            if (i < text.length - 1) total += spacing;
        }
        return total;
    }

    function fillTrackedText(ctx, text, centerX, y, spacing) {
        const total = measureTrackedText(ctx, text, spacing);
        let cursorX = centerX - total / 2;
        const prevAlign = ctx.textAlign;
        ctx.textAlign = 'left';
        for (let i = 0; i < text.length; i++) {
            const ch = text[i];
            ctx.fillText(ch, cursorX, y);
            cursorX += ctx.measureText(ch).width + spacing;
        }
        ctx.textAlign = prevAlign;
    }

    function drawFittedName(ctx, text, centerX, baselineY, maxWidth) {
        let fontSize = 72;
        const minFontSize = 40;
        ctx.textAlign = 'center';

        while (fontSize > minFontSize) {
            ctx.font = `700 ${fontSize}px "Playfair Display", Georgia, serif`;
            if (ctx.measureText(text).width <= maxWidth) {
                ctx.fillText(text, centerX, baselineY);
                return;
            }
            fontSize -= 2;
        }

        // Fallback em duas linhas se o nome for muito extenso
        const words = text.split(/\s+/);
        if (words.length > 1) {
            const mid = Math.ceil(words.length / 2);
            const line1 = words.slice(0, mid).join(' ');
            const line2 = words.slice(mid).join(' ');
            ctx.font = `700 42px "Playfair Display", Georgia, serif`;
            ctx.fillText(line1, centerX, baselineY - 26);
            ctx.fillText(line2, centerX, baselineY + 20);
            return;
        }

        ctx.font = `700 ${minFontSize}px "Playfair Display", Georgia, serif`;
        ctx.fillText(text, centerX, baselineY, maxWidth);
    }

    function drawGuillocheWatermark(ctx, cx, cy, color, alpha) {
        ctx.save();
        ctx.translate(cx, cy);
        ctx.globalAlpha = alpha;
        ctx.strokeStyle = color;
        ctx.lineWidth = 1.4;

        [270, 232, 188, 144].forEach((r, idx) => {
            ctx.save();
            if (idx % 2 === 1) ctx.setLineDash([7, 7]);
            ctx.beginPath();
            ctx.arc(0, 0, r, 0, Math.PI * 2);
            ctx.stroke();
            ctx.restore();
        });

        for (let deg = 0; deg < 180; deg += 22.5) {
            ctx.save();
            ctx.rotate((deg * Math.PI) / 180);
            ctx.beginPath();
            ctx.ellipse(0, 0, 260, 86, 0, 0, Math.PI * 2);
            ctx.stroke();
            ctx.restore();
        }
        ctx.restore();
    }

    function drawCornerOrnament(ctx, x, y, sx, sy, goldStroke) {
        ctx.save();
        ctx.translate(x, y);
        ctx.scale(sx, sy);
        ctx.strokeStyle = goldStroke;
        ctx.fillStyle = goldStroke;

        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(0, 86);
        ctx.lineTo(0, 14);
        ctx.quadraticCurveTo(0, 0, 14, 0);
        ctx.lineTo(86, 0);
        ctx.stroke();

        ctx.lineWidth = 1.6;
        ctx.beginPath();
        ctx.moveTo(10, 72);
        ctx.lineTo(10, 20);
        ctx.quadraticCurveTo(10, 10, 20, 10);
        ctx.lineTo(72, 10);
        ctx.stroke();

        ctx.lineWidth = 1.8;
        ctx.beginPath();
        ctx.moveTo(0, 42);
        ctx.bezierCurveTo(18, 42, 42, 18, 42, 0);
        ctx.stroke();

        // Losango decorativo do canto
        ctx.beginPath();
        ctx.moveTo(22, 12);
        ctx.lineTo(28, 18);
        ctx.lineTo(22, 24);
        ctx.lineTo(16, 18);
        ctx.closePath();
        ctx.fill();

        ctx.beginPath();
        ctx.arc(10, 10, 4, 0, Math.PI * 2);
        ctx.fill();

        ctx.restore();
    }

    function drawCurvedTextTop(ctx, text, cx, cy, radius, spacingRad) {
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const angles = [];
        let totalAngle = 0;
        for (let i = 0; i < text.length; i++) {
            const w = ctx.measureText(text[i]).width;
            const a = w / radius;
            angles.push(a);
            totalAngle += a + (i < text.length - 1 ? spacingRad : 0);
        }

        let angle = -Math.PI / 2 - totalAngle / 2;
        for (let i = 0; i < text.length; i++) {
            const a = angles[i];
            const mid = angle + a / 2;
            ctx.save();
            ctx.translate(cx, cy);
            ctx.rotate(mid + Math.PI / 2);
            ctx.fillText(text[i], 0, -radius);
            ctx.restore();
            angle += a + spacingRad;
        }
        ctx.restore();
    }

    function drawCurvedTextBottom(ctx, text, cx, cy, radius, spacingRad) {
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        const angles = [];
        let totalAngle = 0;
        for (let i = 0; i < text.length; i++) {
            const w = ctx.measureText(text[i]).width;
            const a = w / radius;
            angles.push(a);
            totalAngle += a + (i < text.length - 1 ? spacingRad : 0);
        }

        let angle = Math.PI / 2 + totalAngle / 2;
        for (let i = 0; i < text.length; i++) {
            const a = angles[i];
            const mid = angle - a / 2;
            ctx.save();
            ctx.translate(cx, cy);
            ctx.rotate(mid - Math.PI / 2);
            ctx.fillText(text[i], 0, radius);
            ctx.restore();
            angle -= a + spacingRad;
        }
        ctx.restore();
    }

    function drawStar(ctx, cx, cy, outerR, innerR, points) {
        ctx.beginPath();
        for (let i = 0; i < points * 2; i++) {
            const r = i % 2 === 0 ? outerR : innerR;
            const ang = (i * Math.PI) / points - Math.PI / 2;
            const px = cx + Math.cos(ang) * r;
            const py = cy + Math.sin(ang) * r;
            if (i === 0) ctx.moveTo(px, py);
            else ctx.lineTo(px, py);
        }
        ctx.closePath();
    }

    function drawEmbossedGoldSeal(ctx, cx, cy, yearText) {
        ctx.save();

        // 1. Fitas azul-marinho reais com corte em V
        const drawRibbon = (sideSign) => {
            ctx.save();
            ctx.shadowColor = 'rgba(0, 0, 0, 0.35)';
            ctx.shadowBlur = 12;
            ctx.shadowOffsetY = 5;

            const grad = ctx.createLinearGradient(cx - 50, cy + 40, cx + 50, cy + 150);
            grad.addColorStop(0, '#173c85');
            grad.addColorStop(0.55, '#0f2859');
            grad.addColorStop(1, '#09193a');

            ctx.fillStyle = grad;
            ctx.strokeStyle = '#d4af37';
            ctx.lineWidth = 1.6;

            ctx.beginPath();
            if (sideSign < 0) {
                ctx.moveTo(cx - 34, cy + 48);
                ctx.lineTo(cx - 5, cy + 56);
                ctx.lineTo(cx - 28, cy + 148);
                ctx.lineTo(cx - 48, cy + 128);
                ctx.lineTo(cx - 72, cy + 140);
            } else {
                ctx.moveTo(cx + 34, cy + 48);
                ctx.lineTo(cx + 5, cy + 56);
                ctx.lineTo(cx + 28, cy + 148);
                ctx.lineTo(cx + 48, cy + 128);
                ctx.lineTo(cx + 72, cy + 140);
            }
            ctx.closePath();
            ctx.fill();
            ctx.stroke();

            // Frisos dourados internos na fita
            ctx.shadowColor = 'transparent';
            ctx.strokeStyle = 'rgba(244, 218, 132, 0.85)';
            ctx.lineWidth = 1.4;
            ctx.beginPath();
            if (sideSign < 0) {
                ctx.moveTo(cx - 38, cy + 68);
                ctx.lineTo(cx - 59, cy + 131);
                ctx.moveTo(cx - 18, cy + 72);
                ctx.lineTo(cx - 38, cy + 137);
            } else {
                ctx.moveTo(cx + 38, cy + 68);
                ctx.lineTo(cx + 59, cy + 131);
                ctx.moveTo(cx + 18, cy + 72);
                ctx.lineTo(cx + 38, cy + 137);
            }
            ctx.stroke();
            ctx.restore();
        };

        drawRibbon(-1);
        drawRibbon(1);

        // 2. Rosácea serrilhada dourada de 28 pontas
        ctx.save();
        ctx.shadowColor = 'rgba(0, 0, 0, 0.34)';
        ctx.shadowBlur = 14;
        ctx.shadowOffsetY = 6;

        const pts = 28;
        const outerR = 88;
        const innerR = 80;
        ctx.beginPath();
        for (let i = 0; i < pts * 2; i++) {
            const r = i % 2 === 0 ? outerR : innerR;
            const ang = (i * Math.PI) / pts;
            const px = cx + Math.cos(ang) * r;
            const py = cy + Math.sin(ang) * r;
            if (i === 0) ctx.moveTo(px, py);
            else ctx.lineTo(px, py);
        }
        ctx.closePath();
        ctx.fillStyle = makeGoldGradient(ctx, cx - outerR, cy - outerR, cx + outerR, cy + outerR);
        ctx.fill();
        ctx.strokeStyle = '#7a5310';
        ctx.lineWidth = 1.5;
        ctx.stroke();
        ctx.restore();

        // 3. Face metálica e anéis
        const faceGrad = ctx.createLinearGradient(cx - 65, cy - 65, cx + 65, cy + 65);
        faceGrad.addColorStop(0, '#ebd077');
        faceGrad.addColorStop(0.48, '#c6932e');
        faceGrad.addColorStop(1, '#976a18');

        ctx.beginPath();
        ctx.arc(cx, cy, 72, 0, Math.PI * 2);
        ctx.fillStyle = faceGrad;
        ctx.fill();
        ctx.strokeStyle = '#fff3c4';
        ctx.lineWidth = 2;
        ctx.stroke();

        ctx.save();
        ctx.setLineDash([3, 4]);
        ctx.beginPath();
        ctx.arc(cx, cy, 67, 0, Math.PI * 2);
        ctx.strokeStyle = '#6c480c';
        ctx.lineWidth = 1.2;
        ctx.stroke();
        ctx.restore();

        // Medalhão interno azul-marinho
        ctx.beginPath();
        ctx.arc(cx, cy, 46, 0, Math.PI * 2);
        ctx.fillStyle = '#0f2859';
        ctx.fill();
        ctx.strokeStyle = makeGoldGradient(ctx, cx - 46, cy - 46, cx + 46, cy + 46);
        ctx.lineWidth = 3.2;
        ctx.stroke();

        ctx.beginPath();
        ctx.arc(cx, cy, 41, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(244, 217, 129, 0.72)';
        ctx.lineWidth = 1;
        ctx.stroke();

        // 4. Texto curvado do selo
        ctx.fillStyle = '#382304';
        ctx.font = '800 10.5px "Cinzel", Georgia, serif';
        drawCurvedTextTop(ctx, 'OPUS • CERTIFICADO OFICIAL', cx, cy, 57, 0.032);
        drawCurvedTextBottom(ctx, '100% CONCLUÍDO', cx, cy, 58, 0.036);

        // 5. Estrela e Ano centrais
        drawStar(ctx, cx, cy - 12, 16, 7, 5);
        ctx.fillStyle = makeGoldGradient(ctx, cx - 16, cy - 28, cx + 16, cy + 4);
        ctx.fill();

        ctx.fillStyle = '#f7e29c';
        ctx.font = '800 15px "Cinzel", Georgia, serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        fillTrackedText(ctx, yearText, cx, cy + 20, 2);

        ctx.restore();
    }

    // =========================================================================
    // MOTOR DE RENDERIZAÇÃO CANVAS 2D (1600 x 1131 - A4 PAISAGEM)
    // =========================================================================
    async function generateCertificateCanvas(themeOverride) {
        await waitForCertificateFonts();
        const logoImg = await loadLogoImage();

        const sheetEl = document.getElementById('spotifyCardExport');
        const activeTheme = themeOverride || (sheetEl && sheetEl.getAttribute('data-cert-theme')) || currentTheme || 'ivory';
        const palette = CERT_THEMES[activeTheme] || CERT_THEMES.ivory;

        const width = 1600;
        const height = 1131;
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');

        // 1. Faixa Externa e Fundo Pergaminho
        ctx.fillStyle = palette.outerBand;
        ctx.fillRect(0, 0, width, height);

        const paperGrad = ctx.createRadialGradient(width / 2, height * 0.45, 60, width / 2, height / 2, 950);
        paperGrad.addColorStop(0, palette.bgCenter);
        paperGrad.addColorStop(1, palette.bgEdge);
        ctx.fillStyle = paperGrad;
        ctx.fillRect(22, 22, width - 44, height - 44);

        // Textura diagonal sutil de segurança
        ctx.save();
        ctx.beginPath();
        ctx.rect(22, 22, width - 44, height - 44);
        ctx.clip();
        ctx.strokeStyle = activeTheme === 'obsidian'
            ? `rgba(226, 189, 98, ${palette.stripeAlpha})`
            : `rgba(184, 137, 44, ${palette.stripeAlpha})`;
        ctx.lineWidth = 1;
        for (let offset = -height; offset < width + height; offset += 24) {
            ctx.beginPath();
            ctx.moveTo(offset, 22);
            ctx.lineTo(offset - height, height - 22);
            ctx.stroke();
        }
        ctx.restore();

        // Marca d'água guilhochê central
        drawGuillocheWatermark(ctx, width / 2, 520, palette.gold, palette.watermarkAlpha);

        // Molduras duplas douradas
        const frameGold = makeGoldGradient(ctx, 36, 36, width - 36, height - 36);
        ctx.strokeStyle = frameGold;
        ctx.lineWidth = 4;
        ctx.strokeRect(36, 36, width - 72, height - 72);

        ctx.strokeStyle = activeTheme === 'obsidian' ? 'rgba(226, 189, 98, 0.55)' : 'rgba(184, 137, 44, 0.65)';
        ctx.lineWidth = 1.6;
        ctx.strokeRect(48, 48, width - 96, height - 96);

        // 4 Ornamentos de canto
        drawCornerOrnament(ctx, 54, 54, 1, 1, frameGold);
        drawCornerOrnament(ctx, width - 54, 54, -1, 1, frameGold);
        drawCornerOrnament(ctx, 54, height - 54, 1, -1, frameGold);
        drawCornerOrnament(ctx, width - 54, height - 54, -1, -1, frameGold);

        // 2. Cabeçalho Institucional Centralizado Dinamicamente
        const headerY = 120;
        const logoSize = 42;
        ctx.font = '800 24px "Cinzel", Georgia, serif';
        const brandW = measureTrackedText(ctx, 'OPUS', 4);
        ctx.font = '600 15px "Poppins", sans-serif';
        const subW = measureTrackedText(ctx, 'ACADEMIA DE PROGRAMAÇÃO', 2.6);
        const gap = 14;
        const totalHeaderW = logoSize + gap + brandW + gap + 2 + gap + subW;
        let hx = (width - totalHeaderW) / 2;

        if (logoImg) {
            ctx.save();
            ctx.beginPath();
            if (typeof ctx.roundRect === 'function') {
                ctx.roundRect(hx, headerY - logoSize / 2, logoSize, logoSize, 9);
            } else {
                ctx.rect(hx, headerY - logoSize / 2, logoSize, logoSize);
            }
            ctx.clip();
            ctx.drawImage(logoImg, hx, headerY - logoSize / 2, logoSize, logoSize);
            ctx.restore();

            ctx.strokeStyle = palette.gold;
            ctx.lineWidth = 2;
            ctx.beginPath();
            if (typeof ctx.roundRect === 'function') {
                ctx.roundRect(hx, headerY - logoSize / 2, logoSize, logoSize, 9);
            } else {
                ctx.rect(hx, headerY - logoSize / 2, logoSize, logoSize);
            }
            ctx.stroke();
        }
        hx += logoSize + gap;

        ctx.fillStyle = palette.ink;
        ctx.textBaseline = 'middle';
        ctx.font = '800 24px "Cinzel", Georgia, serif';
        fillTrackedText(ctx, 'OPUS', hx + brandW / 2, headerY + 1, 4);
        hx += brandW + gap;

        ctx.fillStyle = palette.gold;
        ctx.fillRect(hx, headerY - 12, 1.8, 24);
        hx += 2 + gap;

        ctx.fillStyle = palette.inkSoft;
        ctx.font = '600 15px "Poppins", sans-serif';
        fillTrackedText(ctx, 'ACADEMIA DE PROGRAMAÇÃO', hx + subW / 2, headerY + 1, 2.6);

        // 3. Título Cerimonial & Subtítulo
        ctx.textBaseline = 'alphabetic';
        ctx.fillStyle = palette.ink;
        ctx.font = '700 72px "Cinzel", Georgia, serif';
        fillTrackedText(ctx, 'CERTIFICADO', width / 2, 228, 13);

        ctx.font = '700 18px "Poppins", sans-serif';
        const subTitle = 'DE CONCLUSÃO DE CURSO';
        const subTitleW = measureTrackedText(ctx, subTitle, 4.5);
        ctx.fillStyle = palette.goldStrong;
        fillTrackedText(ctx, subTitle, width / 2, 272, 4.5);

        const lineSpan = 120;
        const leftLineEnd = width / 2 - subTitleW / 2 - 22;
        const rightLineStart = width / 2 + subTitleW / 2 + 22;

        const lgLeft = ctx.createLinearGradient(leftLineEnd - lineSpan, 266, leftLineEnd, 266);
        lgLeft.addColorStop(0, 'transparent');
        lgLeft.addColorStop(1, palette.gold);
        ctx.fillStyle = lgLeft;
        ctx.fillRect(leftLineEnd - lineSpan, 265, lineSpan, 2);

        const lgRight = ctx.createLinearGradient(rightLineStart, 266, rightLineStart + lineSpan, 266);
        lgRight.addColorStop(0, palette.gold);
        lgRight.addColorStop(1, 'transparent');
        ctx.fillStyle = lgRight;
        ctx.fillRect(rightLineStart, 265, lineSpan, 2);

        // 4. Destinatário
        const studentName = (document.getElementById('spStudentName')?.textContent || 'Estudante Opus').trim();
        const studentUser = (document.getElementById('spStudentUser')?.textContent || '@estudante').trim();
        const issueDateText = (document.getElementById('certIssueDate')?.textContent || '').trim();
        const cohortText = (document.getElementById('certCohortYear')?.textContent || '').trim();
        const yearMatch = issueDateText.match(/\d{4}/);
        const yearText = cohortText || (yearMatch ? yearMatch[0] : String(new Date().getFullYear()));

        ctx.fillStyle = palette.inkSoft;
        ctx.textAlign = 'center';
        ctx.font = 'italic 500 25px "Playfair Display", Georgia, serif';
        ctx.fillText('Certificamos para os devidos fins que', width / 2, 352);

        ctx.fillStyle = palette.ink;
        drawFittedName(ctx, studentName, width / 2, 430, 1160);

        // Regra ornamental com losango central
        const ruleGrad = ctx.createLinearGradient(440, 460, 1160, 460);
        ruleGrad.addColorStop(0, 'transparent');
        ruleGrad.addColorStop(0.5, palette.gold);
        ruleGrad.addColorStop(1, 'transparent');
        ctx.fillStyle = ruleGrad;
        ctx.fillRect(440, 459, 720, 2);

        ctx.save();
        ctx.translate(width / 2, 460);
        ctx.rotate(Math.PI / 4);
        ctx.fillStyle = palette.gold;
        ctx.fillRect(-5, -5, 10, 10);
        ctx.restore();

        // Usuário e Turma
        ctx.font = '700 20px "Poppins", sans-serif';
        const userPart = studentUser;
        const turmaPart = `  ·  Turma ${yearText}`;
        const userW = ctx.measureText(userPart).width;
        ctx.font = '600 20px "Poppins", sans-serif';
        const turmaW = ctx.measureText(turmaPart).width;
        const metaStartX = width / 2 - (userW + turmaW) / 2;

        ctx.textAlign = 'left';
        ctx.fillStyle = palette.accent;
        ctx.font = '700 20px "Poppins", sans-serif';
        ctx.fillText(userPart, metaStartX, 498);
        ctx.fillStyle = palette.inkSoft;
        ctx.font = '600 20px "Poppins", sans-serif';
        ctx.fillText(turmaPart, metaStartX + userW, 498);

        // 5. Citação Formal da Formação
        ctx.textAlign = 'center';
        ctx.fillStyle = palette.inkSoft;
        ctx.font = '400 22px "Poppins", sans-serif';
        ctx.fillText('concluiu com êxito e 100% de aproveitamento a formação completa', width / 2, 558);

        ctx.fillStyle = palette.accent;
        ctx.font = '700 30px "Playfair Display", Georgia, serif';
        ctx.fillText('Java & Lógica de Programação — OPUS Bootcamp', width / 2, 602);

        ctx.fillStyle = palette.inkSoft;
        ctx.font = '400 20px "Poppins", sans-serif';
        ctx.fillText('cumprindo integralmente todas as 40 seções, 200 lições interativas e desafios práticos em terminal.', width / 2, 644);

        // 6. Faixa Gravada de Mérito
        const stripX = 200;
        const stripY = 690;
        const stripW = 1200;
        const stripH = 94;

        ctx.fillStyle = palette.metricBg;
        ctx.fillRect(stripX, stripY, stripW, stripH);

        ctx.fillStyle = palette.metricBorder;
        ctx.fillRect(stripX, stripY, stripW, 1.5);
        ctx.fillRect(stripX, stripY + stripH, stripW, 1.5);

        const metrics = [
            { val: (document.getElementById('spValXp')?.textContent || '14.500 XP').trim(), lbl: 'XP ACUMULADO' },
            { val: (document.getElementById('spValStreak')?.textContent || '21 Dias').trim(), lbl: 'OFENSIVA' },
            { val: (document.getElementById('spValTrophy')?.textContent || '15 Troféus').trim(), lbl: 'CONQUISTAS' },
            { val: (document.getElementById('spValLessons')?.textContent || '200 / 200').trim(), lbl: 'LIÇÕES (100%)' }
        ];

        const colWidth = stripW / metrics.length;
        metrics.forEach((m, idx) => {
            const colCenter = stripX + colWidth * (idx + 0.5);

            if (idx > 0) {
                ctx.fillStyle = palette.metricBorder;
                ctx.fillRect(stripX + colWidth * idx, stripY + 18, 1.5, stripH - 36);
            }

            ctx.textAlign = 'center';
            ctx.fillStyle = palette.ink;
            ctx.font = '700 27px "Playfair Display", Georgia, serif';
            ctx.fillText(m.val, colCenter, stripY + 43);

            ctx.fillStyle = palette.inkSoft;
            ctx.font = '700 13px "Poppins", sans-serif';
            fillTrackedText(ctx, m.lbl, colCenter, stripY + 71, 1.8);
        });

        // 7. Rodapé Cerimonial: Assinatura, Selo Dourado e Autenticação
        const leftColX = 360;
        const rightColX = 1240;
        const footerRuleY = 954;

        // Coluna esquerda: Assinatura
        ctx.save();
        ctx.translate(leftColX, 926);
        ctx.rotate(-0.035);
        ctx.textAlign = 'center';
        ctx.fillStyle = palette.ink;
        ctx.font = '400 54px "Great Vibes", cursive';
        ctx.fillText('Equipe OPUS', 0, 0);
        ctx.restore();

        const leftRuleGrad = ctx.createLinearGradient(leftColX - 150, footerRuleY, leftColX + 150, footerRuleY);
        leftRuleGrad.addColorStop(0, 'transparent');
        leftRuleGrad.addColorStop(0.5, palette.gold);
        leftRuleGrad.addColorStop(1, 'transparent');
        ctx.fillStyle = leftRuleGrad;
        ctx.fillRect(leftColX - 150, footerRuleY, 300, 1.6);

        ctx.textAlign = 'center';
        ctx.fillStyle = palette.ink;
        ctx.font = '700 14px "Poppins", sans-serif';
        fillTrackedText(ctx, 'DIRETORIA ACADÊMICA OPUS', leftColX, 982, 1.4);

        ctx.fillStyle = palette.inkSoft;
        ctx.font = '500 14px "Poppins", sans-serif';
        ctx.fillText('Coordenação · Java & Lógica', leftColX, 1006);

        // Coluna central: Selo Oficial Dourado com Fitas
        drawEmbossedGoldSeal(ctx, width / 2, 905, yearText);

        // Coluna direita: Rótulo "Emitido em", Data de emissão e Código
        const issueDate = issueDateText;
        const authCode = (document.getElementById('certAuthCode')?.textContent || 'OPUS-2026-CERT').trim();

        ctx.textAlign = 'center';
        ctx.fillStyle = palette.goldStrong;
        ctx.font = '800 12px "Poppins", sans-serif';
        fillTrackedText(ctx, 'EMITIDO EM', rightColX, 902, 2.2);

        ctx.fillStyle = palette.ink;
        ctx.font = '600 22px "Playfair Display", Georgia, serif';
        ctx.fillText(issueDate || `Turma ${yearText}`, rightColX, 934);

        const rightRuleGrad = ctx.createLinearGradient(rightColX - 150, footerRuleY, rightColX + 150, footerRuleY);
        rightRuleGrad.addColorStop(0, 'transparent');
        rightRuleGrad.addColorStop(0.5, palette.gold);
        rightRuleGrad.addColorStop(1, 'transparent');
        ctx.fillStyle = rightRuleGrad;
        ctx.fillRect(rightColX - 150, footerRuleY, 300, 1.6);

        ctx.fillStyle = palette.ink;
        ctx.font = '700 14px "Poppins", sans-serif';
        fillTrackedText(ctx, authCode, rightColX, 982, 1.3);

        ctx.fillStyle = palette.inkSoft;
        ctx.font = '500 14px "Poppins", sans-serif';
        ctx.fillText('opus.dev/certificados', rightColX, 1006);

        return canvas;
    }

    // Exporta no escopo global para facilitar diagnóstico/integração
    window.generateCertificateCanvas = generateCertificateCanvas;
    window.generateSpotifyCardCanvas = generateCertificateCanvas;

    // =========================================================================
    // AÇÕES DO CERTIFICADO: DOWNLOAD PNG, IMPRESSÃO PDF, COMPARTILHAMENTO
    // =========================================================================
    async function baixarCertificadoPNG() {
        const btn = document.getElementById('spBtnDownload');
        const originalHtml = btn ? btn.innerHTML : '';
        try {
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gerando Certificado...';
                btn.disabled = true;
            }

            const canvas = await generateCertificateCanvas();
            const rawUser = (document.getElementById('spStudentUser')?.textContent || 'formando').replace('@', '').trim();
            const safeUser = rawUser.replace(/[^a-zA-Z0-9_-]/g, '') || 'formando';
            const fileName = `certificado-opus-${safeUser}.png`;

            const imageURL = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.download = fileName;
            link.href = imageURL;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            showToast('Certificado baixado em alta resolução!');
        } catch (e) {
            console.error('Erro ao baixar certificado:', e);
            showToast('Erro ao gerar imagem. Tente novamente.');
        } finally {
            if (btn) {
                btn.innerHTML = originalHtml || '<i class="fa-solid fa-download"></i> Baixar PNG (Alta Res.)';
                btn.disabled = false;
            }
        }
    }

    async function imprimirCertificado() {
        const btn = document.getElementById('spBtnPrint');
        const originalHtml = btn ? btn.innerHTML : '';
        try {
            if (btn) {
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Preparando PDF...';
                btn.disabled = true;
            }

            const canvas = await generateCertificateCanvas();
            const dataUrl = canvas.toDataURL('image/png');
            const printWin = window.open('', '_blank', 'width=1100,height=800');

            if (!printWin) {
                showToast('Pop-up bloqueado. Baixe o certificado em PNG para imprimir.');
                return;
            }

            printWin.document.open();
            printWin.document.write(`<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Certificado Oficial OPUS</title>
<style>
  @page { size: A4 landscape; margin: 0; }
  html, body { margin: 0; padding: 0; width: 100%; height: 100%; background: #fff; display: flex; align-items: center; justify-content: center; }
  img { width: 100%; height: 100%; object-fit: contain; display: block; }
</style>
</head>
<body>
  <img src="${dataUrl}" alt="Certificado Oficial OPUS">
  <script>
    window.addEventListener('load', function () {
      setTimeout(function () { window.focus(); window.print(); }, 180);
    });
  <\/script>
</body>
</html>`);
            printWin.document.close();
        } catch (e) {
            console.error('Erro ao preparar impressão:', e);
            showToast('Não foi possível abrir a impressão. Tente baixar o PNG.');
        } finally {
            if (btn) {
                btn.innerHTML = originalHtml || '<i class="fa-solid fa-print"></i> Imprimir / PDF';
                btn.disabled = false;
            }
        }
    }

    async function compartilharCertificado() {
        const shareText = '🎓 Concluí a Formação Completa em Java & Lógica no OPUS! 40 seções, 200 lições práticas e 100% de aproveitamento. Confira meu Certificado Oficial! #OPUS #Java #Dev';
        const shareUrl = window.location.origin + window.location.pathname;

        try {
            const canvas = await generateCertificateCanvas();
            canvas.toBlob(async (blob) => {
                if (blob && navigator.canShare) {
                    const file = new File([blob], 'certificado-opus.png', { type: 'image/png' });
                    if (navigator.canShare({ files: [file] })) {
                        try {
                            await navigator.share({
                                title: 'Certificado Oficial OPUS — Java & Lógica',
                                text: shareText,
                                files: [file]
                            });
                            showToast('Certificado compartilhado com sucesso!');
                            return;
                        } catch (err) {
                            if (err && err.name === 'AbortError') return;
                        }
                    }
                }

                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: 'Certificado Oficial OPUS — Java & Lógica',
                            text: shareText,
                            url: shareUrl
                        });
                        showToast('Compartilhado com sucesso!');
                        return;
                    } catch (err) {
                        if (err && err.name === 'AbortError') return;
                    }
                }

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(`${shareText}\n${shareUrl}`);
                    showToast('Texto e link do certificado copiados!');
                } else {
                    showToast('Link do certificado copiado!');
                }
            }, 'image/png');
        } catch (e) {
            console.warn('Erro ao compartilhar certificado:', e);
            showToast('Link do certificado copiado!');
        }
    }

    // =========================================================================
    // TROCA DE TEMA DO CERTIFICADO
    // =========================================================================
    function applyCertificateTheme(theme) {
        const nextTheme = CERT_THEMES[theme] ? theme : 'ivory';
        currentTheme = nextTheme;

        const sheet = document.getElementById('spotifyCardExport');
        if (sheet) {
            sheet.setAttribute('data-cert-theme', nextTheme);
        }

        document.querySelectorAll('[data-cert-theme-btn]').forEach((btn) => {
            const isMatch = btn.getAttribute('data-cert-theme-btn') === nextTheme;
            btn.classList.toggle('is-active', isMatch);
            btn.setAttribute('aria-pressed', isMatch ? 'true' : 'false');
        });
    }

    // =========================================================================
    // FLUXO EM 2 ETAPAS: SOLICITAÇÃO DO NOME COMPLETO -> CERTIFICADO OFICIAL
    // =========================================================================
    function normalizeFullName(raw) {
        return String(raw || '').replace(/\s+/g, ' ').trim();
    }

    function validateFullName(raw) {
        const clean = normalizeFullName(raw);
        if (!clean) {
            return 'Digite seu nome completo para emitir o certificado.';
        }
        if (clean.length < 5 || clean.length > 120) {
            return 'O nome completo deve ter entre 5 e 120 caracteres.';
        }
        const words = clean.split(' ').filter(Boolean);
        if (words.length < 2) {
            return 'Informe nome e sobrenome completos (pelo menos duas palavras).';
        }
        if (!/^[\p{L}\p{M}\s'.-]+$/u.test(clean)) {
            return 'Use apenas letras, espaços, hífen, ponto ou apóstrofo.';
        }
        return '';
    }

    function setFullNameError(msg) {
        const input = document.getElementById('certFullNameInput');
        const errEl = document.getElementById('certFullNameError');
        if (input) {
            if (msg) {
                input.setAttribute('aria-invalid', 'true');
            } else {
                input.removeAttribute('aria-invalid');
            }
            if (typeof input.setCustomValidity === 'function') {
                input.setCustomValidity(msg || '');
            }
        }
        if (errEl) {
            if (msg) {
                errEl.innerHTML = `<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> <span>${msg}</span>`;
                errEl.hidden = false;
            } else {
                errEl.textContent = '';
                errEl.hidden = true;
            }
        }
    }

    function formatTodayPtBr() {
        try {
            return new Intl.DateTimeFormat('pt-BR', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).format(new Date());
        } catch (e) {
            const now = new Date();
            const meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
            return `${now.getDate()} de ${meses[now.getMonth()]} de ${now.getFullYear()}`;
        }
    }

    function applyCertificateData(payload) {
        if (!payload) return;
        const nameEl = document.getElementById('spStudentName');
        const issueEl = document.getElementById('certIssueDate');
        const previewDateEl = document.getElementById('certFormDatePreview');
        const authEl = document.getElementById('certAuthCode');
        const cohortEl = document.getElementById('certCohortYear');
        const sealYearEl = document.getElementById('certSealYear');
        const nameInput = document.getElementById('certFullNameInput');

        if (nameEl && payload.nome_completo) nameEl.textContent = payload.nome_completo;
        if (nameInput && payload.nome_completo) nameInput.value = payload.nome_completo;
        if (issueEl && payload.data_emissao_extenso) issueEl.textContent = payload.data_emissao_extenso;
        if (previewDateEl && payload.data_emissao_extenso) previewDateEl.textContent = payload.data_emissao_extenso;
        if (authEl && payload.codigo_validacao) authEl.textContent = payload.codigo_validacao;
        if (cohortEl && payload.ano_emissao) cohortEl.textContent = payload.ano_emissao;
        if (sealYearEl && payload.ano_emissao) sealYearEl.textContent = payload.ano_emissao;
    }

    function showStep(stepName, options) {
        const opts = options || {};
        const modal = document.getElementById('modalSpotifyWrapped');
        const formStep = document.getElementById('certStepForm');
        const viewStep = document.getElementById('certStepView');
        const nameInput = document.getElementById('certFullNameInput');
        const trackTitle = document.getElementById('spTrackTitle');

        if (!modal || !viewStep) return;

        const showForm = stepName === 'form' && !!formStep && !isCertificateIssued;
        if (formStep) {
            formStep.hidden = !showForm;
        }
        viewStep.hidden = showForm;
        modal.setAttribute('aria-labelledby', showForm ? 'certFormTitle' : 'spTrackTitle');
        modal.scrollTop = 0;

        if (showForm) {
            if (!formSubmitAttempted) {
                setFullNameError('');
            }
            requestAnimationFrame(() => {
                if (nameInput) nameInput.focus();
            });
        } else {
            if (isCertificateIssued && formStep && formStep.parentNode) {
                formStep.parentNode.removeChild(formStep);
            }
            requestAnimationFrame(() => {
                modal.scrollTop = 0;
                if (trackTitle) trackTitle.focus({ preventScroll: true });
            });
            if (opts.celebrate) {
                launchConfettiBurst(modal);
                playCelebratoryMelody();
            }
        }
    }

    async function handleEmitSubmit(e) {
        e.preventDefault();
        if (isCertificateIssued) {
            showStep('certificate', { celebrate: false });
            return;
        }

        const modal = document.getElementById('modalSpotifyWrapped');
        const nameInput = document.getElementById('certFullNameInput');
        const csrfInput = document.getElementById('certCsrfToken');
        const submitBtn = document.getElementById('certBtnEmitir');
        if (!nameInput || !submitBtn) return;

        const cleanName = normalizeFullName(nameInput.value);
        nameInput.value = cleanName;
        formSubmitAttempted = true;

        const validationError = validateFullName(cleanName);
        if (validationError) {
            setFullNameError(validationError);
            nameInput.focus();
            return;
        }

        setFullNameError('');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> <span>Emitindo certificado...</span>';

        try {
            const resp = await fetch('../../back/api/certificado.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    nome_completo: cleanName,
                    csrf_token: csrfInput ? csrfInput.value : ''
                })
            });

            const data = await resp.json().catch(() => null);
            if (resp.status === 409 && data && data.ja_emitido) {
                isCertificateIssued = true;
                if (modal) modal.setAttribute('data-cert-issued', '1');
                applyCertificateData(data);
                showStep('certificate', { celebrate: false });
                showToast(data.erro || 'O nome do certificado já foi registrado e não pode ser alterado.');
                return;
            }

            if (!resp.ok || !data || !data.ok) {
                const serverErr = (data && data.erro) ? data.erro : 'Não foi possível emitir o certificado. Tente novamente.';
                setFullNameError(serverErr);
                nameInput.focus();
                return;
            }

            isCertificateIssued = true;
            if (modal) modal.setAttribute('data-cert-issued', '1');
            applyCertificateData(data);
            showStep('certificate', { celebrate: true });
            showToast('Certificado oficial emitido com sucesso!');
        } catch (err) {
            console.warn('Falha ao salvar certificado no servidor:', err);
            const fallbackDate = formatTodayPtBr();
            isCertificateIssued = true;
            if (modal) modal.setAttribute('data-cert-issued', '1');
            applyCertificateData({
                nome_completo: cleanName,
                data_emissao_extenso: fallbackDate,
                ano_emissao: String(new Date().getFullYear())
            });
            showStep('certificate', { celebrate: true });
            showToast('Certificado gerado com seu nome e a data de hoje!');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    }

    // =========================================================================
    // ABERTURA & FECHAMENTO DO <dialog> MODAL NATIVO
    // =========================================================================
    window.abrirSpotifyWrappedModal = function () {
        const modal = document.getElementById('modalSpotifyWrapped');
        if (!modal) return;

        isCertificateIssued = modal.getAttribute('data-cert-issued') === '1';

        if (typeof modal.showModal === 'function') {
            if (!modal.open) modal.showModal();
        } else {
            modal.classList.add('is-active');
        }

        if (isCertificateIssued) {
            showStep('certificate', { celebrate: true });
        } else {
            showStep('form', { celebrate: false });
        }
    };

    window.fecharSpotifyWrappedModal = function () {
        const modal = document.getElementById('modalSpotifyWrapped');
        if (!modal) return;

        if (typeof modal.close === 'function') {
            if (modal.open) modal.close();
        } else {
            modal.classList.remove('is-active');
        }
    };

    // =========================================================================
    // INICIALIZAÇÃO DO DOM
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalSpotifyWrapped');
        const downloadBtn = document.getElementById('spBtnDownload');
        const printBtn = document.getElementById('spBtnPrint');
        const shareBtn = document.getElementById('spBtnShare');
        const playBtn = document.getElementById('spBtnPlay');
        const openBannerBtn = document.getElementById('btnAbrirSpotifyBanner');
        const openCardBtn = document.getElementById('btnAbrirSpotifyCardGeral');
        const emitForm = document.getElementById('certEmitForm');
        const nameInput = document.getElementById('certFullNameInput');

        if (modal) {
            isCertificateIssued = modal.getAttribute('data-cert-issued') === '1';
        }

        document.querySelectorAll('[data-cert-close]').forEach((btn) => {
            btn.addEventListener('click', window.fecharSpotifyWrappedModal);
        });

        if (downloadBtn) downloadBtn.addEventListener('click', baixarCertificadoPNG);
        if (printBtn) printBtn.addEventListener('click', imprimirCertificado);
        if (shareBtn) shareBtn.addEventListener('click', compartilharCertificado);
        if (playBtn) playBtn.addEventListener('click', playCelebratoryMelody);

        if (openBannerBtn) openBannerBtn.addEventListener('click', window.abrirSpotifyWrappedModal);
        if (openCardBtn) openCardBtn.addEventListener('click', window.abrirSpotifyWrappedModal);

        if (nameInput) {
            nameInput.addEventListener('input', function () {
                nameFieldEdited = true;
                if (nameInput.getAttribute('aria-invalid') === 'true' || formSubmitAttempted) {
                    const err = validateFullName(nameInput.value);
                    if (!err) {
                        setFullNameError('');
                    }
                }
            });

            nameInput.addEventListener('blur', function () {
                if (!nameFieldEdited && !formSubmitAttempted) return;
                const err = validateFullName(nameInput.value);
                setFullNameError(err);
            });

            nameInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && e.isComposing) {
                    e.preventDefault();
                }
            });
        }

        if (emitForm) {
            emitForm.addEventListener('submit', handleEmitSubmit);
        }

        document.querySelectorAll('[data-cert-theme-btn]').forEach((btn) => {
            btn.addEventListener('click', function () {
                applyCertificateTheme(btn.getAttribute('data-cert-theme-btn'));
            });
        });

        // Fallback de light-dismiss para navegadores sem suporte a <dialog closedby="any">
        if (modal && !('closedBy' in HTMLDialogElement.prototype)) {
            const outside = (e, dialog) => {
                const r = dialog.getBoundingClientRect();
                return e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom;
            };
            let pressedOutside = false;
            modal.addEventListener('pointerdown', (e) => {
                pressedOutside = outside(e, modal);
            });
            modal.addEventListener('pointerup', (e) => {
                if (pressedOutside && outside(e, modal) && modal.open) {
                    modal.close();
                }
            });
        }
    });

})(window, document);
