// ranking.js
        // Função para resolver a foto (Base64 ou Padrão)
        function obterFoto(fotoBase64) {
            if (fotoBase64 && fotoBase64.startsWith('data:image')) {
                return fotoBase64;
            }
            return '../assets/img/opi pulando feliz.png'; // Imagem padrão
        }

// Função para resolver a bandeira do país
function obterBandeira(codigo, nome) {
    const code = (codigo || 'br').toLowerCase();
    const countryName = nome || 'Brasil';
    return `<img src="https://flagcdn.com/24x18/${code}.png" srcset="https://flagcdn.com/48x36/${code}.png 2x" width="20" height="15" alt="${countryName}" title="${countryName}" class="ranking-flag">`;
}

document.addEventListener('DOMContentLoaded', () => {
    fetch('../../back/api/ranking.php')
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
                const u2 = data.top3[1];
                podiumHTML += `<div class="podium-item rank-2">
                    <div class="podium-avatar"><img src="${obterFoto(u2.foto_perfil)}" class="img-cover"></div>
                    <div class="podium-xp">${Number(u2.xp).toLocaleString('pt-BR')} XP</div>
                    <div class="podium-name" title="${u2.nome} (${u2.pais || 'Brasil'})">
                        <span class="podium-user-text">${u2.nome}</span>
                        ${obterBandeira(u2.pais_codigo, u2.pais)}
                    </div>
                    <div class="podium-country-badge">${u2.pais || 'Brasil'}</div>
                    <div class="podium-bar">2</div>
                </div>`;
            }
            
            // Primeiro Colocado (Rei do pedaço!)
            if (data.top3[0]) {
                const u1 = data.top3[0];
                podiumHTML += `<div class="podium-item rank-1">
                    <div class="podium-avatar"><i class="fa-solid fa-crown crown"></i><img src="${obterFoto(u1.foto_perfil)}" class="img-cover"></div>
                    <div class="podium-xp">${Number(u1.xp).toLocaleString('pt-BR')} XP</div>
                    <div class="podium-name" title="${u1.nome} (${u1.pais || 'Brasil'})">
                        <span class="podium-user-text">${u1.nome}</span>
                        ${obterBandeira(u1.pais_codigo, u1.pais)}
                    </div>
                    <div class="podium-country-badge">${u1.pais || 'Brasil'}</div>
                    <div class="podium-bar">1</div>
                </div>`;
            }
            
            // Terceiro Colocado
            if (data.top3[2]) {
                const u3 = data.top3[2];
                podiumHTML += `<div class="podium-item rank-3">
                    <div class="podium-avatar"><img src="${obterFoto(u3.foto_perfil)}" class="img-cover"></div>
                    <div class="podium-xp">${Number(u3.xp).toLocaleString('pt-BR')} XP</div>
                    <div class="podium-name" title="${u3.nome} (${u3.pais || 'Brasil'})">
                        <span class="podium-user-text">${u3.nome}</span>
                        ${obterBandeira(u3.pais_codigo, u3.pais)}
                    </div>
                    <div class="podium-country-badge">${u3.pais || 'Brasil'}</div>
                    <div class="podium-bar">3</div>
                </div>`;
            }
            
            document.getElementById('podium-container').innerHTML = podiumHTML;

            // Lista do Restante
            let listHTML = '';
            data.lista_resto.forEach(user => {
                listHTML += `<div class="ranking-row ${user.is_me ? 'is-me' : ''}">
                    <div class="row-pos">${user.posicao}º</div>
                    <div class="row-avatar"><img src="${obterFoto(user.foto_perfil)}" class="img-cover"></div>
                    <div class="row-name">
                        <span class="row-user-text">${user.nome}${user.is_me ? ' (Você)' : ''}</span>
                        <span class="user-country-tag" title="${user.pais || 'Brasil'}">
                            ${obterBandeira(user.pais_codigo, user.pais)}
                            <span class="country-name-label">${user.pais || 'Brasil'}</span>
                        </span>
                    </div>
                    <div class="row-xp">${Number(user.xp).toLocaleString('pt-BR')} XP</div>
                </div>`;
            });
            
            document.getElementById('ranking-list').innerHTML = listHTML;
        })
        .catch(err => console.error("Erro:", err));
});
