// IDE / Laboratório Java - OPUS
document.addEventListener('DOMContentLoaded', () => {
    // 1. Configurações dos Códigos de Exemplo
    const EXEMPLOS_CODIGO = {
        hello: `public class Main {
    public static void main(String[] args) {
        System.out.println("Olá, Mundo do OPUS!");
    }
}`,
        variaveis: `public class Main {
    public static void main(String[] args) {
        int a = 15;
        int b = 25;
        int resultado = a + b;
        
        System.out.println("A soma de " + a + " + " + b + " = " + resultado);
    }
}`,
        condicional: `public class Main {
    public static void main(String[] args) {
        int pontuacao = 85;
        
        if (pontuacao >= 70) {
            System.out.println("Parabéns! Você passou de fase no OPUS! 🏆");
        } else {
            System.out.println("Tente novamente para conseguir mais pontos!");
        }
    }
}`,
        laco: `public class Main {
    public static void main(String[] args) {
        System.out.println("Iniciando contagem regressiva:");
        for (int i = 5; i >= 1; i--) {
            System.out.println(i + "...");
        }
        System.out.println("Decolar! 🚀");
    }
}`
    };

    // 2. Inicialização do Ace Editor
    const editor = ace.edit("editor");
    editor.setTheme("ace/theme/dracula");
    editor.session.setMode("ace/mode/java");
    editor.setFontSize(15);
    editor.setShowPrintMargin(false);
    editor.session.setTabSize(4);
    editor.session.setUseSoftTabs(true);
    editor.setValue(EXEMPLOS_CODIGO.hello, -1);

    // Habilitar autocompletar se a extensão estiver presente
    editor.setOptions({
        enableBasicAutocompletion: true,
        enableLiveAutocompletion: true,
        enableSnippets: true
    });

    window.addEventListener('resize', () => editor.resize());
    setTimeout(() => editor.resize(), 100);

    // Elementos da Interface
    const btnRun = document.getElementById('btnRunCode');
    const btnClear = document.getElementById('btnClearTerminal');
    const btnReset = document.getElementById('btnResetCode');
    const selectExemplo = document.getElementById('selectExemplo');
    const selectTema = document.getElementById('selectTema');
    const terminalBody = document.getElementById('terminalBody');
    const terminalStatus = document.getElementById('terminalStatus');

    // 3. Atualizar Tema
    if (selectTema) {
        selectTema.addEventListener('change', (e) => {
            editor.setTheme("ace/theme/" + e.target.value);
        });
    }

    // 4. Carregar Exemplos
    if (selectExemplo) {
        selectExemplo.addEventListener('change', (e) => {
            const key = e.target.value;
            if (EXEMPLOS_CODIGO[key]) {
                editor.setValue(EXEMPLOS_CODIGO[key], -1);
                editor.focus();
            }
        });
    }

    // 5. Reiniciar Código
    if (btnReset) {
        btnReset.addEventListener('click', () => {
            const key = selectExemplo ? selectExemplo.value : 'hello';
            editor.setValue(EXEMPLOS_CODIGO[key] || EXEMPLOS_CODIGO.hello, -1);
            editor.focus();
        });
    }

    // 6. Limpar Terminal
    if (btnClear) {
        btnClear.addEventListener('click', () => {
            terminalBody.innerHTML = `
                <div><span class="term-line-prefix">opus@ide:~$</span> <span class="term-line-cmd">clear</span></div>
                <div style="color: #6272a4; margin-top: 6px;">Terminal pronto. Digite seu código Java e clique em "Executar".</div>
            `;
            setStatusBadge('idle', 'Pronto');
        });
    }

    function setStatusBadge(tipo, texto) {
        terminalStatus.className = 'terminal-status-badge status-' + tipo;
        terminalStatus.textContent = texto;
    }

    // 7. Execução do Código via API
    async function executarCodigo() {
        const codigo = editor.getValue().trim();

        if (!codigo) {
            alert('Por favor, digite algum código Java antes de executar!');
            return;
        }

        btnRun.disabled = true;
        setStatusBadge('running', 'Compilando...');

        const startTime = performance.now();

        terminalBody.innerHTML = `
            <div><span class="term-line-prefix">opus@ide:~$</span> <span class="term-line-cmd">javac Main.java &amp;&amp; java Main</span></div>
            <div style="color: #f1fa8c; margin-top: 8px;">
                <i class="fa-solid fa-spinner term-loading-spinner"></i> Compilando e executando no OpenJDK 21...
            </div>
        `;

        try {
            const resposta = await fetch('../../back/api/executar_codigo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ codigo: codigo })
            });

            const data = await resposta.json();
            const elapsedSec = ((performance.now() - startTime) / 1000).toFixed(2);

            let html = `<div><span class="term-line-prefix">opus@ide:~$</span> <span class="term-line-cmd">javac Main.java &amp;&amp; java Main</span></div>`;

            if (data.sucesso) {
                setStatusBadge('success', `Sucesso (${elapsedSec}s)`);
                const saidaLimpa = data.saida || "(Código executado com sucesso sem saída no console)";
                html += `
                    <div class="term-output-success">${escapeHtml(saidaLimpa)}</div>
                    <div style="color: #6272a4; font-size: 0.8rem; margin-top: 10px; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 6px;">
                        ✔ Processo finalizado com código de saída 0 (${elapsedSec}s)
                    </div>
                `;
            } else {
                setStatusBadge('error', 'Erro');
                const erroMsg = data.erro_completo || data.erro || "Ocorreu um erro desconhecido ao executar o código.";
                html += `
                    <div class="term-output-error">
                        <div style="font-weight: bold; margin-bottom: 6px;"><i class="fa-solid fa-triangle-exclamation"></i> Erro de Compilação / Execução:</div>
                        <div>${escapeHtml(erroMsg)}</div>
                    </div>
                    <div style="color: #ff5555; font-size: 0.8rem; margin-top: 6px;">
                        ✖ Processo falhou com código de saída ${data.status || 1} (${elapsedSec}s)
                    </div>
                `;
            }

            terminalBody.innerHTML = html;
            terminalBody.scrollTop = terminalBody.scrollHeight;

        } catch (erro) {
            setStatusBadge('error', 'Falha');
            terminalBody.innerHTML = `
                <div><span class="term-line-prefix">opus@ide:~$</span> <span class="term-line-cmd">javac Main.java &amp;&amp; java Main</span></div>
                <div class="term-output-error">
                    <strong>Erro de Conexão:</strong> Não foi possível se comunicar com o servidor. Verifique sua conexão com a internet.
                </div>
            `;
        } finally {
            btnRun.disabled = false;
        }
    }

    function escapeHtml(texto) {
        const div = document.createElement('div');
        div.innerText = texto;
        return div.innerHTML;
    }

    // Eventos de clique e atalhos
    btnRun.addEventListener('click', executarCodigo);

    // Atalho de teclado: Ctrl + Enter ou Cmd + Enter para Rodar
    editor.commands.addCommand({
        name: 'runCodeShortcut',
        bindKey: { win: 'Ctrl-Enter', mac: 'Command-Enter' },
        exec: function () {
            executarCodigo();
        }
    });
});
