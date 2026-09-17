// conquistas.js
        fetch('../../back/api_conquistas.php')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('trophy-container');
            
            data.lista.forEach(t => {
                const isUnlocked = data.conquistados.includes(t.slug);
                const statusIcon = isUnlocked ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-lock"></i>';
                
                // Variável da cor do capítulo para o brilho (default azul)
                const corCapitulo = t.cor ? t.cor : '#1cb0f6';
                
                container.innerHTML += `
                    <div class="trophy-card ${isUnlocked ? 'unlocked' : 'locked'}">
                        <div class="status-badge">${statusIcon}</div>
                        
                        <!-- Contêiner com a Imagem da Medalha -->
                        <div class="medal-container" style="--cor-tema: ${corCapitulo};">
                            <div class="medal-glow"></div>
                            <img src="../assets/img/medalha.png" alt="Medalha" class="medal-img">
                        </div>

                        <h3>${t.nome}</h3>
                        <p>${t.desc}</p>
                    </div>`;
            });
        })
        .catch(error => console.error('Erro ao carregar conquistas:', error));
