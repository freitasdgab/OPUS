// esqueceu_senha.js - Fluxo em 3 Etapas com Validação por E-mail

function mostrarMensagem(texto, tipo) {
    const div = document.getElementById('mensagem');
    if (!div) return;
    div.innerHTML = texto;
    div.className = 'alert ' + tipo;
    div.style.display = 'block';
}

function limparMensagem() {
    const div = document.getElementById('mensagem');
    if (!div) return;
    div.innerHTML = '';
    div.style.display = 'none';
}

// ----------------------------------------------------
// ETAPA 1: Enviar Código por E-mail
// ----------------------------------------------------
function solicitarCodigo() {
    const emailInput = document.getElementById('email');
    const email = emailInput ? emailInput.value.trim() : '';
    const btn = document.getElementById('btnEnviarCodigo');

    if (!email) {
        return mostrarMensagem("Por favor, digite o seu e-mail cadastrado.", "error");
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> ENVIANDO...';
    }
    mostrarMensagem("Aguarde, enviando o código para o seu e-mail...", "success");

    fetch('../../back/api/solicitar_senha.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'ENVIAR CÓDIGO';
        }

        if (data.status === 'success') {
            mostrarMensagem(data.message, 'success');
            document.getElementById('etapa-1').style.display = 'none';
            document.getElementById('etapa-2').style.display = 'block';
            
            const codigoInput = document.getElementById('codigo');
            if (codigoInput) codigoInput.focus();
        } else {
            mostrarMensagem(data.message || "Não foi possível enviar o código.", "error");
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'ENVIAR CÓDIGO';
        }
        mostrarMensagem("Erro de comunicação com o servidor. Verifique se o Apache está ativo.", "error");
    });
}

// ----------------------------------------------------
// ETAPA 2: Validar o Código de 6 Dígitos
// ----------------------------------------------------
function validarCodigo() {
    const email = document.getElementById('email').value.trim();
    const codigoInput = document.getElementById('codigo');
    const codigo = codigoInput ? codigoInput.value.trim() : '';
    const btn = document.getElementById('btnValidarCodigo');

    if (!codigo || codigo.length < 6) {
        return mostrarMensagem("Por favor, digite o código completo de 6 dígitos que chegou no seu e-mail.", "error");
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> VERIFICANDO...';
    }

    fetch('../../back/api/validar_codigo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}&codigo=${encodeURIComponent(codigo)}`
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'VERIFICAR CÓDIGO';
        }

        if (data.status === 'success') {
            // Código APROVADO! Libera a Etapa 3 para digitar a nova senha
            mostrarMensagem(data.message, 'success');
            document.getElementById('etapa-2').style.display = 'none';
            document.getElementById('etapa-3').style.display = 'block';

            const novaSenhaInput = document.getElementById('nova_senha');
            if (novaSenhaInput) novaSenhaInput.focus();
        } else {
            mostrarMensagem(data.message || "Código incorreto ou expirado.", "error");
        }
    })
    .catch(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'VERIFICAR CÓDIGO';
        }
        mostrarMensagem("Erro ao verificar o código no servidor.", "error");
    });
}

// ----------------------------------------------------
// ETAPA 3: Redefinir a Senha (após o código ser aprovado)
// ----------------------------------------------------
function redefinirSenha() {
    const email = document.getElementById('email').value.trim();
    const codigo = document.getElementById('codigo').value.trim();
    const novaSenha = document.getElementById('nova_senha').value;
    const confirmeNovaSenha = document.getElementById('confirme_nova_senha').value;
    const btn = document.getElementById('btnSalvarSenha');

    if (!novaSenha || !confirmeNovaSenha) {
        return mostrarMensagem("Preencha a nova senha e confirme-a nos dois campos.", "error");
    }

    if (novaSenha !== confirmeNovaSenha) {
        return mostrarMensagem("As duas senhas não coincidem. Digite-as com atenção.", "error");
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> SALVANDO...';
    }

    fetch('../../back/api/redefinir_senha.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}&codigo=${encodeURIComponent(codigo)}&nova_senha=${encodeURIComponent(novaSenha)}&confirme_senha=${encodeURIComponent(confirmeNovaSenha)}`
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'ALTERAR SENHA';
        }

        if (data.status === 'success') {
            document.getElementById('etapa-3').style.display = 'none';
            mostrarMensagem(data.message + " <br><br><a href='auth.html' style='color:#fff; text-decoration:underline; font-weight:bold;'>Clique aqui para ir para o Login</a>", 'success');
        } else {
            mostrarMensagem(data.message || "Erro ao redefinir a senha.", "error");
        }
    })
    .catch(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'ALTERAR SENHA';
        }
        mostrarMensagem("Erro ao conectar com o servidor.", "error");
    });
}

function voltarParaEtapa1() {
    limparMensagem();
    document.getElementById('etapa-2').style.display = 'none';
    document.getElementById('etapa-3').style.display = 'none';
    document.getElementById('etapa-1').style.display = 'block';
}
