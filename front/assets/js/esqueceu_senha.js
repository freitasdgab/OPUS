// esqueceu_senha.js
        function mostrarMensagem(texto, tipo) {
            const div = document.getElementById('mensagem');
            div.innerHTML = texto;
            div.className = 'alert ' + tipo;
        }

        function solicitarCodigo() {
            const email = document.getElementById('email').value.trim();
            if(!email) return mostrarMensagem("Por favor, digite seu e-mail.", "error");

            mostrarMensagem("Aguarde, enviando e-mail...", "success");

            fetch('../../back/solicitar_codigo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    mostrarMensagem(data.message, 'success');
                    document.getElementById('etapa-1').style.display = 'none';
                    document.getElementById('etapa-2').style.display = 'block';
                } else {
                    mostrarMensagem(data.message, 'error');
                }
            }).catch(() => mostrarMensagem("Erro na comunicação com o servidor.", "error"));
        }

        function redefinirSenha() {
            const email = document.getElementById('email').value.trim();
            const codigo = document.getElementById('codigo').value.trim();
            const novaSenha = document.getElementById('nova_senha').value;

            if(!codigo || !novaSenha) return mostrarMensagem("Preencha o código e a nova senha.", "error");

            fetch('../../back/redefinir_senha_acao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&codigo=${encodeURIComponent(codigo)}&nova_senha=${encodeURIComponent(novaSenha)}`
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    mostrarMensagem(data.message + " <a href='auth.html'>Clique aqui para fazer login</a>", 'success');
                    document.getElementById('etapa-2').style.display = 'none';
                } else {
                    mostrarMensagem(data.message, 'error');
                }
            }).catch(() => mostrarMensagem("Erro ao alterar senha.", "error"));
        }
