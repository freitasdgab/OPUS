/**
 * OPUS Web Audio API Synthesizer (Sound Design Gamificado Estilo Duolingo)
 * 
 * 100% nativo com síntese Web Audio API. Sem dependência de arquivos de áudio externos,
 * garantindo latência zero, compatibilidade total offline e feedback tátil imediato.
 */

(function(window) {
    'use strict';

    let audioCtx = null;
    const STORAGE_KEY = 'opus_sound_muted';

    function getAudioContext() {
        if (!audioCtx) {
            const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
            if (AudioCtxClass) {
                audioCtx = new AudioCtxClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume().catch(() => {});
        }
        return audioCtx;
    }

    // Desbloqueia AudioContext na primeira interação do usuário (requisito dos navegadores)
    function unlockAudio() {
        const ctx = getAudioContext();
        if (ctx && ctx.state === 'suspended') {
            ctx.resume().catch(() => {});
        }
        document.removeEventListener('pointerdown', unlockAudio);
        document.removeEventListener('keydown', unlockAudio);
    }
    document.addEventListener('pointerdown', unlockAudio, { passive: true });
    document.addEventListener('keydown', unlockAudio, { passive: true });

    const OpusAudio = {
        /**
         * Verifica se o áudio está silenciado
         */
        isMuted: function() {
            try {
                return localStorage.getItem(STORAGE_KEY) === 'true';
            } catch (e) {
                return false;
            }
        },

        /**
         * Define estado de silenciado
         */
        setMuted: function(muted) {
            try {
                localStorage.setItem(STORAGE_KEY, muted ? 'true' : 'false');
            } catch (e) {}
            // Dispara evento global para sincronizar UI
            window.dispatchEvent(new CustomEvent('opus:sound-toggle', { detail: { muted: !!muted } }));
        },

        /**
         * Alterna estado mudo
         */
        toggleMute: function() {
            const next = !this.isMuted();
            this.setMuted(next);
            if (!next) {
                // Toca um clique de confirmação
                this.playClick();
            }
            return next;
        },

        /**
         * 1. CLIQUE TÁTIL / POP (Botões, alternativas, interações rápidas)
         * Curto, suave e satisfatório, simula toque físico estilo mobile do Duolingo.
         */
        playClick: function() {
            if (this.isMuted()) return;
            const ctx = getAudioContext();
            if (!ctx) return;

            try {
                const now = Math.max(ctx.currentTime, 0.001) + 0.005;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                // Rampa rápida de frequência descendente (pop de bolha tátil)
                osc.frequency.setValueAtTime(500, now);
                osc.frequency.exponentialRampToValueAtTime(140, now + 0.045);

                // Micro-ataque suave para evitar estalo/clique elétrico de DC offset
                gain.gain.setValueAtTime(0.001, now);
                gain.gain.linearRampToValueAtTime(0.15, now + 0.005);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.048);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now);
                osc.stop(now + 0.05);
            } catch (e) {
                console.warn('[OpusAudio] Falha ao tocar clique:', e);
            }
        },

        /**
         * 2. EFEITO DE ACERTO (Sucesso estilo Duolingo)
         * Tríade/acorde ascendente alegre e brilhante com harmônicos suaves.
         */
        playSuccess: function() {
            if (this.isMuted()) return;
            const ctx = getAudioContext();
            if (!ctx) return;

            try {
                const now = ctx.currentTime;
                // Notas: C5 (523.25Hz), E5 (659.25Hz), G5 (783.99Hz), C6 (1046.50Hz)
                const notes = [
                    { f: 523.25, time: 0.00, dur: 0.12, vol: 0.18 },
                    { f: 659.25, time: 0.07, dur: 0.14, vol: 0.20 },
                    { f: 783.99, time: 0.14, dur: 0.18, vol: 0.22 },
                    { f: 1046.50, time: 0.22, dur: 0.38, vol: 0.26 }
                ];

                notes.forEach(n => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'triangle'; // Onda triangle para timbre doce e cartoon
                    osc.frequency.setValueAtTime(n.f, now + n.time);

                    gain.gain.setValueAtTime(0.001, now + n.time);
                    gain.gain.linearRampToValueAtTime(n.vol, now + n.time + 0.015);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + n.time + n.dur);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(now + n.time);
                    osc.stop(now + n.time + n.dur + 0.02);

                    // Harmônico secundário para dar brilho de sino/chime
                    if (n.f === 1046.50) {
                        const chime = ctx.createOscillator();
                        const chimeGain = ctx.createGain();
                        chime.type = 'sine';
                        chime.frequency.setValueAtTime(2093.00, now + n.time); // C7 (oitava acima)

                        chimeGain.gain.setValueAtTime(0.001, now + n.time);
                        chimeGain.gain.linearRampToValueAtTime(0.08, now + n.time + 0.015);
                        chimeGain.gain.exponentialRampToValueAtTime(0.001, now + n.time + n.dur * 0.9);

                        chime.connect(chimeGain);
                        chimeGain.connect(ctx.destination);

                        chime.start(now + n.time);
                        chime.stop(now + n.time + n.dur);
                    }
                });
            } catch (e) {
                console.warn('[OpusAudio] Falha ao tocar sucesso:', e);
            }
        },

        /**
         * 3. EFEITO DE ERRO (Buzz/tom descendente amigável estilo Duolingo)
         * Não-punitivo, tom cartoon suave em tom menor descendente com filtro macio.
         */
        playError: function() {
            if (this.isMuted()) return;
            const ctx = getAudioContext();
            if (!ctx) return;

            try {
                const now = ctx.currentTime;
                // Dois tons descendentes suaves: Eb3 (155.56Hz) -> C3 (130.81Hz)
                const notes = [
                    { f: 185.00, time: 0.00, dur: 0.16, vol: 0.22 },
                    { f: 130.81, time: 0.12, dur: 0.32, vol: 0.20 }
                ];

                notes.forEach(n => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    const filter = ctx.createBiquadFilter();

                    // Filtro passa-baixa para cortar agudos estridentes e manter o som fofo
                    filter.type = 'lowpass';
                    filter.frequency.setValueAtTime(650, now + n.time);

                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(n.f, now + n.time);
                    // Leve queda sutil de pitch no final de cada tom
                    osc.frequency.linearRampToValueAtTime(n.f * 0.92, now + n.time + n.dur);

                    gain.gain.setValueAtTime(0.001, now + n.time);
                    gain.gain.linearRampToValueAtTime(n.vol, now + n.time + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + n.time + n.dur);

                    osc.connect(filter);
                    filter.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(now + n.time);
                    osc.stop(now + n.time + n.dur + 0.02);
                });
            } catch (e) {
                console.warn('[OpusAudio] Falha ao tocar erro:', e);
            }
        },

        /**
         * 4. EFEITO DE LIÇÃO FINALIZADA / COMEMORAÇÃO (Fanfarra estilo Duolingo)
         * Arpejo vitorioso com acorde brilhante e sustentação triunfante.
         */
        playLessonComplete: function() {
            if (this.isMuted()) return;
            const ctx = getAudioContext();
            if (!ctx) return;

            try {
                const now = ctx.currentTime;
                // Notas da fanfarra: G4, C5, E5, G5, high C6 com acorde sustentado
                const arpeggio = [
                    { f: 392.00, time: 0.00, dur: 0.12, vol: 0.18 }, // G4
                    { f: 523.25, time: 0.10, dur: 0.12, vol: 0.20 }, // C5
                    { f: 659.25, time: 0.20, dur: 0.14, vol: 0.22 }, // E5
                    { f: 783.99, time: 0.30, dur: 0.20, vol: 0.24 }, // G5
                    { f: 1046.50, time: 0.44, dur: 0.65, vol: 0.28 }, // C6 (fanfarra)
                    { f: 1318.51, time: 0.44, dur: 0.65, vol: 0.18 }  // E6 (harmonia de terça)
                ];

                arpeggio.forEach(n => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(n.f, now + n.time);

                    gain.gain.setValueAtTime(0.001, now + n.time);
                    gain.gain.linearRampToValueAtTime(n.vol, now + n.time + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + n.time + n.dur);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(now + n.time);
                    osc.stop(now + n.time + n.dur + 0.05);
                });
            } catch (e) {
                console.warn('[OpusAudio] Falha ao tocar comemoração:', e);
            }
        },

        /**
         * 5. EFEITO DE SUSPENSE DO BAÚ (Vibração e antecipação antes de abrir)
         */
        playChestRumble: function() {
            if (this.isMuted()) return;
            const ctx = getAudioContext();
            if (!ctx) return;

            try {
                const now = ctx.currentTime;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(90, now);
                osc.frequency.exponentialRampToValueAtTime(260, now + 0.45);

                gain.gain.setValueAtTime(0.001, now);
                gain.gain.linearRampToValueAtTime(0.14, now + 0.35);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.48);

                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.5);
            } catch (e) {}
        },

        /**
         * 5.1 EFEITO DE BAÚ / FANFARRA TRIUNFAL (Chime mágico ascendente + acorde triunfal)
         */
        playChest: function() {
            if (this.isMuted()) return;
            const ctx = getAudioContext();
            if (!ctx) return;

            try {
                const now = ctx.currentTime;
                // Arpeggio brilhante em Dó Maior 7M / Nono (C5, E5, G5, B5, C6, E6, G6)
                const freqs = [523.25, 659.25, 783.99, 987.77, 1046.50, 1318.51, 1567.98];
                freqs.forEach((f, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    const t = now + i * 0.055;
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(f, t);

                    gain.gain.setValueAtTime(0.001, t);
                    gain.gain.linearRampToValueAtTime(0.22, t + 0.015);
                    gain.gain.exponentialRampToValueAtTime(0.001, t + 0.45);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(t);
                    osc.stop(t + 0.48);
                });

                // Acorde final de sustentação dourada (brilho mágico)
                const finalChord = [523.25, 659.25, 1046.50, 1567.98];
                const tFinal = now + freqs.length * 0.055;
                finalChord.forEach(f => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(f, tFinal);

                    gain.gain.setValueAtTime(0.001, tFinal);
                    gain.gain.linearRampToValueAtTime(0.12, tFinal + 0.03);
                    gain.gain.exponentialRampToValueAtTime(0.001, tFinal + 0.85);

                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(tFinal);
                    osc.stop(tFinal + 0.9);
                });
            } catch (e) {
                console.warn('[OpusAudio] Falha ao tocar baú:', e);
            }
        },

        /**
         * 6. EFEITO DE LIÇÃO NÃO CONCLUÍDA / DERROTA SUAVE (Mascote triste, 0 acertos)
         * Amigável e reconfortante estilo Duolingo, tom cartoon suave em modo menor sem ser agressivo.
         */
        playLessonDefeat: function() {
            if (this.isMuted()) return;
            const ctx = getAudioContext();
            if (!ctx) return;

            try {
                const now = Math.max(ctx.currentTime, 0.001) + 0.01;
                // Notas suaves descendentes: G4 (392Hz) -> F4 (349Hz) -> Eb4 (311Hz) -> D4 (293Hz)
                const tones = [
                    { f: 392.00, time: 0.00, dur: 0.22, vol: 0.16 },
                    { f: 349.23, time: 0.18, dur: 0.24, vol: 0.15 },
                    { f: 311.13, time: 0.36, dur: 0.26, vol: 0.14 },
                    { f: 293.66, time: 0.54, dur: 0.50, vol: 0.16 }
                ];

                tones.forEach(n => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    const filter = ctx.createBiquadFilter();

                    filter.type = 'lowpass';
                    filter.frequency.setValueAtTime(800, now + n.time);

                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(n.f, now + n.time);
                    osc.frequency.linearRampToValueAtTime(n.f * 0.96, now + n.time + n.dur);

                    gain.gain.setValueAtTime(0.001, now + n.time);
                    gain.gain.linearRampToValueAtTime(n.vol, now + n.time + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + n.time + n.dur);

                    osc.connect(filter);
                    filter.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(now + n.time);
                    osc.stop(now + n.time + n.dur + 0.03);
                });
            } catch (e) {
                console.warn('[OpusAudio] Falha ao tocar som de derrota:', e);
            }
        },

        // Aliases para máxima compatibilidade entre lições e desafios
        playCorrect: function() {
            return this.playSuccess();
        },
        playVictory: function() {
            return this.playLessonComplete();
        },
        playLessonVictory: function() {
            return this.playLessonComplete();
        }
    };

    window.OpusAudio = OpusAudio;
})(window);
