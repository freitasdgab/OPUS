// ligas.js - logica da pagina de Ligas
        function obterFoto(fotoBase64) {
            if (fotoBase64 && fotoBase64.startsWith('data:image')) return fotoBase64;
            return '../assets/img/opi pulando feliz.png';
        }

        function formatarTempo(segundos) {
            const d = Math.floor(segundos / 86400);
            const h = Math.floor((segundos % 86400) / 3600);
            const m = Math.floor((segundos % 3600) / 60);
            return `${d}d ${String(h).padStart(2,'0')}h ${String(m).padStart(2,'0')}m`;
        }

        function mostrarErro(msg) {
            document.getElementById('badge-nome').innerText = 'Erro ao carregar';
            document.getElementById('badge-sub').innerText = msg;
            document.getElementById('liga-list').innerHTML =
                `<div style="padding:20px;text-align:center;color:#ff8080">${msg}</div>`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetch('../../back/api_ligas.php')
                .then(r => r.json())
                .then(data => {
                    if (data.error === "unauthorized") { window.location.href = "auth.html"; return; }
                    if (data.error) { mostrarErro(data.mensagem || 'Erro desconhecido no servidor.'); return; }

                    document.getElementById('badge-icon').style.background = data.divisao_cor;
                    document.getElementById('badge-nome').innerText = data.divisao_nome;
                    document.getElementById('badge-sub').innerText =
                        `${data.minha_posicao}º lugar de ${data.tamanho_grupo} · ${data.meu_xp_semana} XP nesta semana`;
                    document.getElementById('timer-valor').innerText = formatarTempo(data.segundos_restantes);

                    let html = '';
                    data.membros.forEach(m => {
                        let icon = '';
                        if (m.zona === 'sobe') icon = '<i class="fa-solid fa-arrow-up" style="color:#58cc02"></i>';
                        if (m.zona === 'desce') icon = '<i class="fa-solid fa-arrow-down" style="color:#ff4b4b"></i>';
                        html += `<div class="liga-row ${m.zona} ${m.is_me ? 'is-me' : ''}">
                            <div class="row-pos">${m.posicao}º</div>
                            <div class="row-avatar"><img src="${obterFoto(m.foto_perfil)}" class="img-cover"></div>
                            <div class="row-name">${m.nome}${m.is_me ? ' (Você)' : ''}</div>
                            <div class="row-xp">${m.xp} XP</div>
                            <div class="row-icon">${icon}</div>
                        </div>`;
                    });
                    document.getElementById('liga-list').innerHTML = html;
                })
                .catch(err => mostrarErro('Não foi possível conectar à API (' + err.message + ').'));
        });
