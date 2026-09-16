<?php
session_start();
// Mantenha a verificação de login se necessário
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dicionário</title>
    
    <!-- Fonte Nunito igual ao perfil -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Ajuste o caminho do CSS caso necessário -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    
    <style>
        /* =========================================
           ESTILO DUOLINGO DARK MODE (Igual ao Perfil)
           ========================================= */
        :root {
            --bg-dark: #131f24;
            --border-color: #37464f;
            --text-main: #ffffff;
            --text-muted: #778590;
            --duo-blue: #1cb0f6;
            --duo-blue-hover: #1899d6;
            --card-bg: #131f24; /* Fundo dos cards igual ao body */
            --tooltip-bg: #2a3942; /* Fundo escuro para a caixinha de dica */
        }

        body, html {
            background-color: var(--bg-dark);
            font-family: 'Nunito', sans-serif;
            color: var(--text-main);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .main-content {
            padding: 24px 40px;
            min-height: 100vh;
        }

        .codex-container {
            max-width: 900px;
            margin: 0 auto;
        }

        /* Cabeçalho do Códex */
        .codex-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            border: 2px solid var(--border-color);
            border-radius: 16px;
            background-color: var(--card-bg);
        }

        .codex-header h1 {
            color: var(--text-main);
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 10px 0;
        }

        .codex-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin: 0;
            font-weight: 700;
        }

        /* Cartões de Categoria */
        .categoria-section {
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            background-color: var(--card-bg);
        }

        .categoria-section h2 {
            color: var(--text-main);
            font-size: 1.4rem;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 15px;
        }

        /* Layout dos Comandos */
        .comandos-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* Botões de Comando */
        .comando-btn {
            background-color: transparent;
            color: var(--text-main);
            border: 2px solid var(--border-color);
            padding: 10px 18px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            font-family: 'Courier New', Courier, monospace;
            cursor: pointer;
            position: relative;
            transition: all 0.2s ease;
            user-select: none;
        }

        .comando-btn:hover {
            border-color: var(--duo-blue);
            background-color: rgba(28, 176, 246, 0.1); /* Fundo azul bem suave */
            color: var(--duo-blue);
            transform: translateY(-2px);
        }

        /* =========================================
           TOOLTIP / BALÃO DE EXPLICAÇÃO
           ========================================= */
        .comando-btn::after {
            content: attr(data-descricao);
            position: absolute;
            bottom: 130%;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--tooltip-bg);
            color: #ffffff;
            font-family: 'Nunito', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            padding: 10px 15px;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            width: max-content;
            max-width: 250px;
            text-align: center;
            white-space: normal;
            line-height: 1.4;
            
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            pointer-events: none;
        }

        .comando-btn::before {
            content: "";
            position: absolute;
            bottom: 115%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 8px;
            border-style: solid;
            border-color: var(--tooltip-bg) transparent transparent transparent;
            
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            z-index: 101;
            pointer-events: none;
        }

        /* Para a setinha não ficar com a borda cortada, adicionamos um contorno */
        .comando-btn:hover::after,
        .comando-btn:hover::before {
            opacity: 1;
            visibility: visible;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .main-content { padding: 20px; }
            .codex-header h1 { font-size: 1.6rem; }
            .comando-btn { width: 100%; text-align: center; }
            .comando-btn::after { max-width: 200px; }
        }
    </style>
</head>
<body>

    <div class="app-container">
        
        <!-- Sidebar -->
        <?php include '../../back/sidebar.php'; ?>

        <main class="main-content">
            
            <!-- Topbar -->
            <?php include '../../back/topbar.php'; ?>

            <div class="codex-container">
                
                <div class="codex-header">
                    <h1>Dicionário de Comandos</h1>
                    <p>Passe o mouse sobre os comandos para descobrir o que eles fazem no Java!</p>
                </div>

                <!-- Categoria: Fundamentos -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-code" style="color: var(--duo-blue);"></i> Sintaxe Básica e Variáveis</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Imprime um texto no console e continua na mesma linha.">System.out.print()</div>
                        <div class="comando-btn" data-descricao="Imprime um texto no console e pula para a próxima linha.">System.out.println()</div>
                        <div class="comando-btn" data-descricao="Tipo primitivo para números inteiros (ex: 10, -5).">int</div>
                        <div class="comando-btn" data-descricao="Classe usada para armazenar textos e palavras.">String</div>
                        <div class="comando-btn" data-descricao="Tipo primitivo para números decimais/com vírgula (ex: 3.14).">double</div>
                        <div class="comando-btn" data-descricao="Tipo lógico: armazena apenas verdadeiro (true) ou falso (false).">boolean</div>
                        <div class="comando-btn" data-descricao="Armazena um único caractere (ex: 'A', '3', '@').">char</div>
                        <div class="comando-btn" data-descricao="Usado para ler dados digitados pelo usuário no teclado.">Scanner</div>
                    </div>
                </div>

                <!-- Nova Categoria: Operadores -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-calculator" style="color: #ff9600;"></i> Operadores Matemáticos e Lógicos</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Soma dois valores ou junta (concatena) dois textos.">+</div>
                        <div class="comando-btn" data-descricao="Subtrai o segundo valor do primeiro.">-</div>
                        <div class="comando-btn" data-descricao="Multiplica dois valores.">*</div>
                        <div class="comando-btn" data-descricao="Divide o primeiro valor pelo segundo.">/</div>
                        <div class="comando-btn" data-descricao="Módulo: Retorna o resto de uma divisão.">%</div>
                        <div class="comando-btn" data-descricao="Operador E (AND). Verdadeiro só se os dois lados forem verdadeiros.">&&</div>
                        <div class="comando-btn" data-descricao="Operador OU (OR). Verdadeiro se pelo menos um lado for verdadeiro.">||</div>
                        <div class="comando-btn" data-descricao="Incremento: Aumenta o valor de uma variável em 1.">++</div>
                        <div class="comando-btn" data-descricao="Igualdade: Verifica se dois valores são iguais.">==</div>
                        <div class="comando-btn" data-descricao="Diferença: Verifica se dois valores são diferentes.">!=</div>
                    </div>
                </div>

                <!-- Categoria: Estruturas de Decisão -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-code-branch" style="color: #2bda5a;"></i> Estruturas de Decisão</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Significa 'SE'. Executa o código apenas se a condição for verdadeira.">if</div>
                        <div class="comando-btn" data-descricao="Significa 'SENÃO'. Executa se o 'if' anterior for falso.">else</div>
                        <div class="comando-btn" data-descricao="Significa 'SENÃO SE'. Testa uma nova condição se o primeiro 'if' falhar.">else if</div>
                        <div class="comando-btn" data-descricao="Escolhe um bloco de código entre várias opções (casos).">switch</div>
                        <div class="comando-btn" data-descricao="Define uma das opções dentro de um 'switch'.">case</div>
                        <div class="comando-btn" data-descricao="A opção padrão caso nenhum 'case' do 'switch' seja verdadeiro.">default</div>
                        <div class="comando-btn" data-descricao="Interrompe e sai de um loop ou de um switch.">break</div>
                    </div>
                </div>

                <!-- Categoria: Estruturas de Repetição -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-rotate-right" style="color: #ff4b4b;"></i> Estruturas de Repetição (Loops)</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Loop 'PARA'. Repete o código por um número exato de vezes.">for</div>
                        <div class="comando-btn" data-descricao="Loop 'ENQUANTO'. Repete o código enquanto uma condição for verdadeira.">while</div>
                        <div class="comando-btn" data-descricao="Executa o código pelo menos uma vez antes de checar a condição.">do</div>
                        <div class="comando-btn" data-descricao="Pula para a próxima repetição do loop, ignorando o código abaixo dele.">continue</div>
                    </div>
                </div>

                <!-- Categoria: Arrays -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-layer-group" style="color: #ce82ff;"></i> Arrays e Listas</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Colchetes. Usados para criar e acessar itens em um Array (ex: notas[0]).">[ ]</div>
                        <div class="comando-btn" data-descricao="Chaves. Usadas para inicializar os valores de um Array direto na criação.">{ }</div>
                        <div class="comando-btn" data-descricao="Retorna o tamanho total (quantidade de itens) de um Array.">.length</div>
                    </div>
                </div>

                <!-- Categoria: POO -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-cube" style="color: #1cb0f6;"></i> Orientação a Objetos (POO)</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Define um molde para criar objetos. O bloco principal do Java.">class</div>
                        <div class="comando-btn" data-descricao="Instancia (cria) um novo objeto na memória a partir de uma classe.">new</div>
                        <div class="comando-btn" data-descricao="Método que não retorna nenhum valor (é vazio).">void</div>
                        <div class="comando-btn" data-descricao="Devolve um valor como resultado final de um método.">return</div>
                        <div class="comando-btn" data-descricao="Público: O elemento pode ser acessado de qualquer outra classe.">public</div>
                        <div class="comando-btn" data-descricao="Privado: O elemento só pode ser usado dentro da própria classe.">private</div>
                        <div class="comando-btn" data-descricao="Aponta para o próprio objeto atual sendo executado na classe.">this</div>
                        <div class="comando-btn" data-descricao="Faz com que o método/variável pertença à classe e não ao objeto.">static</div>
                    </div>
                </div>

                <!-- Nova Categoria: Tratamento de Erros -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-triangle-exclamation" style="color: #ffc800;"></i> Tratamento de Erros</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="'Tentar'. Bloco de código que será testado para ver se dá erro.">try</div>
                        <div class="comando-btn" data-descricao="'Capturar'. Bloco que é executado caso aconteça um erro no 'try'.">catch</div>
                        <div class="comando-btn" data-descricao="'Finalmente'. Código que é executado sempre, dando erro ou não.">finally</div>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>