// ranking.js
        // Função para resolver a foto (Base64 ou Padrão)
        function obterFoto(fotoBase64) {
            if (fotoBase64 && fotoBase64.startsWith('data:image')) {
                return fotoBase64;
            }
            return '../assets/img/opi pulando feliz.png'; // Imagem padrão
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetch('../../back/api_ranking.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error === "unauthorized") { window.location.href = "auth.html"; return; }

                    if (data.minha_posicao > 0) {
                        const statusDiv = document.getElementById('my-status');
                        statusDiv.innerText = `Você está atualmente na ${data.minha_posicao}ª posição!`;
                        statusDiv.style.display = 'block';
                    }

                    let podiumHTML = '';
                    
                    // Segundo Colocado
                    if (data.top3[1]) {
                        podiumHTML += `<div class="podium-item rank-2"><div class="podium-avatar"><img src="${obterFoto(data.top3[1].foto_perfil)}" class="img-cover"></div><div class="podium-name">${data.top3[1].nome}</div><div class="podium-xp">${Number(data.top3[1].xp).toLocaleString('pt-BR')} XP</div><div class="podium-bar">2</div></div>`;
                    }
                    
                    // Primeiro Colocado (Rei do pedaço!)
                    if (data.top3[0]) {
                        podiumHTML += `<div class="podium-item rank-1"><div class="podium-avatar"><i class="fa-solid fa-crown crown"></i><img src="${obterFoto(data.top3[0].foto_perfil)}" class="img-cover"></div><div class="podium-name">${data.top3[0].nome}</div><div class="podium-xp">${Number(data.top3[0].xp).toLocaleString('pt-BR')} XP</div><div class="podium-bar">1</div></div>`;
                    }
                    
                    // Terceiro Colocado
                    if (data.top3[2]) {
                        podiumHTML += `<div class="podium-item rank-3"><div class="podium-avatar"><img src="${obterFoto(data.top3[2].foto_perfil)}" class="img-cover"></div><div class="podium-name">${data.top3[2].nome}</div><div class="podium-xp">${Number(data.top3[2].xp).toLocaleString('pt-BR')} XP</div><div class="podium-bar">3</div></div>`;
                    }
                    
                    document.getElementById('podium-container').innerHTML = podiumHTML;

                    // Lista do Restante
                    let listHTML = '';
                    data.lista_resto.forEach(user => {
                        listHTML += `<div class="ranking-row ${user.is_me ? 'is-me' : ''}"><div class="row-pos">${user.posicao}º</div><div class="row-avatar"><img src="${obterFoto(user.foto_perfil)}" class="img-cover"></div><div class="row-name">${user.nome}${user.is_me ? ' (Você)' : ''}</div><div class="row-xp">${Number(user.xp).toLocaleString('pt-BR')} XP</div></div>`;
                    });
                    
                    document.getElementById('ranking-list').innerHTML = listHTML;
                })
                .catch(err => console.error("Erro:", err));
        });
