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
    <title>Dicionário - Opus</title>
    <link rel="icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link rel="shortcut icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/topbar.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/dicionario.css?v=<?= time() ?>">
</head>
<body>

    <div class="app-container">
        
        <!-- Sidebar -->
        <?php include '../../back/includes/sidebar.php'; ?>

        <main class="main-content">
            
            <!-- Topbar -->
            <?php include '../../back/includes/topbar.php'; ?>

            <div class="codex-container">
                
                <!-- Hero do Dicionario com Mascote Opi Investigador -->
                <section class="codex-hero">
                    <div class="codex-hero-content">
                        <div class="codex-hero-badge">
                            <i class="fa-solid fa-book-bookmark"></i> C&oacute;dex de Consulta
                        </div>
                        <h1 class="codex-hero-title">
                            Dicion&aacute;rio de <span>Comandos Java</span>
                        </h1>
                        <p class="codex-hero-desc">
                            Explore palavras-chave, operadores, cole&ccedil;&otilde;es e m&eacute;todos fundamentais. Passe o mouse ou toque sobre qualquer comando para inspecionar sua explica&ccedil;&atilde;o pr&aacute;tica!
                        </p>
                    </div>
                    <div class="codex-hero-mascote">
                        <img src="../assets/img/opi_dicionario.png" alt="Mascote Opi com Lupa">
                    </div>
                </section>

                <!-- Categoria: Fundamentos -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-code" style="color: var(--duo-blue);"></i> Sintaxe Básica e Variáveis</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Imprime um texto no console e continua na mesma linha.">System.out.print()</div>
                        <div class="comando-btn" data-descricao="Imprime um texto no console e pula para a próxima linha.">System.out.println()</div>
                        <div class="comando-btn" data-descricao="Imprime um texto formatado no console usando marcadores (ex: %s, %d, %.2f).">System.out.printf()</div>
                        <div class="comando-btn" data-descricao="Método principal (public static void main): ponto de partida de todo programa Java.">main</div>
                        <div class="comando-btn" data-descricao="Parâmetro padrão do método main que recebe argumentos de texto na inicialização.">String[] args</div>
                        <div class="comando-btn" data-descricao="Tipo primitivo para números inteiros (ex: 10, -5).">int</div>
                        <div class="comando-btn" data-descricao="Classe usada para armazenar textos e palavras.">String</div>
                        <div class="comando-btn" data-descricao="Tipo primitivo para números decimais com ponto (ex: 3.14, 19.90).">double</div>
                        <div class="comando-btn" data-descricao="Tipo lógico: armazena apenas verdadeiro (true) ou falso (false).">boolean</div>
                        <div class="comando-btn" data-descricao="Armazena um único caractere entre aspas simples (ex: 'A', '3', '@').">char</div>
                        <div class="comando-btn" data-descricao="Define uma constante cujo valor é fixo e não pode ser alterado.">final</div>
                        <div class="comando-btn" data-descricao="Valor lógico literal que representa 'verdadeiro' em uma variável boolean.">true</div>
                        <div class="comando-btn" data-descricao="Valor lógico literal que representa 'falso' em uma variável boolean.">false</div>
                        <div class="comando-btn" data-descricao="Tipo primitivo para números decimais de precisão simples (ex: 9.5f).">float</div>
                        <div class="comando-btn" data-descricao="Tipo primitivo para números inteiros muito grandes.">long</div>
                        <div class="comando-btn" data-descricao="Tipo primitivo inteiro compacto que armazena valores de -128 a 127.">byte</div>
                        <div class="comando-btn" data-descricao="Tipo primitivo para números inteiros curtos.">short</div>
                        <div class="comando-btn" data-descricao="Indica que uma variável ou referência não aponta para nenhum objeto.">null</div>
                        <div class="comando-btn" data-descricao="Usado para ler dados digitados pelo usuário no teclado.">Scanner</div>
                        <div class="comando-btn" data-descricao="Ponto e vírgula: obrigatório ao final de toda instrução em Java.">;</div>
                        <div class="comando-btn" data-descricao="Aspas duplas: obrigatórias para delimitar textos (String) em Java.">" "</div>
                        <div class="comando-btn" data-descricao="Aspas simples: obrigatórias para delimitar um único caractere (char).">' '</div>
                        <div class="comando-btn" data-descricao="Comentário de linha única: o compilador ignora tudo após as barras na linha.">//</div>
                        <div class="comando-btn" data-descricao="Comentário de múltiplas linhas: permite criar blocos de anotação no código.">/* */</div>
                        <div class="comando-btn" data-descricao="Casting (conversão explícita): converte um valor para inteiro (ex: (int) 9.8).">(int)</div>
                    </div>
                </div>

                <!-- Categoria: Operadores -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-calculator" style="color: #ff9600;"></i> Operadores Matemáticos e Lógicos</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Soma dois valores numéricos ou junta (concatena) textos.">+</div>
                        <div class="comando-btn" data-descricao="Subtrai o segundo valor do primeiro.">-</div>
                        <div class="comando-btn" data-descricao="Multiplica dois valores.">*</div>
                        <div class="comando-btn" data-descricao="Divide o primeiro valor pelo segundo.">/</div>
                        <div class="comando-btn" data-descricao="Módulo: Retorna o resto de uma divisão inteira.">%</div>
                        <div class="comando-btn" data-descricao="Atribuição: guarda o valor da direita dentro da variável à esquerda.">=</div>
                        <div class="comando-btn" data-descricao="Incremento: Aumenta o valor de uma variável em 1.">++</div>
                        <div class="comando-btn" data-descricao="Decremento: Diminui o valor de uma variável em 1.">--</div>
                        <div class="comando-btn" data-descricao="Soma e atribui: adiciona um valor à variável atual (ex: pontos += 10).">+=</div>
                        <div class="comando-btn" data-descricao="Subtrai e atribui: desconta um valor da variável atual (ex: vidas -= 1).">-=</div>
                        <div class="comando-btn" data-descricao="Multiplica e atribui: multiplica o valor da variável atual.">*=</div>
                        <div class="comando-btn" data-descricao="Divide e atribui: divide o valor da variável atual.">/=</div>
                        <div class="comando-btn" data-descricao="Igualdade: Verifica se dois valores são iguais.">==</div>
                        <div class="comando-btn" data-descricao="Diferença: Verifica se dois valores são diferentes.">!=</div>
                        <div class="comando-btn" data-descricao="Maior que: Retorna true se o valor da esquerda for maior que o da direita.">&gt;</div>
                        <div class="comando-btn" data-descricao="Menor que: Retorna true se o valor da esquerda for menor que o da direita.">&lt;</div>
                        <div class="comando-btn" data-descricao="Maior ou igual: Retorna true se o valor for maior ou igual ao comparado.">&gt;=</div>
                        <div class="comando-btn" data-descricao="Menor ou igual: Retorna true se o valor for menor ou igual ao comparado.">&lt;=</div>
                        <div class="comando-btn" data-descricao="Operador E (AND). Verdadeiro só se os dois lados forem verdadeiros.">&&</div>
                        <div class="comando-btn" data-descricao="Operador OU (OR). Verdadeiro se pelo menos um lado for verdadeiro.">||</div>
                        <div class="comando-btn" data-descricao="Negação (NOT): Inverte um valor booleano (!true vira false e !false vira true).">!</div>
                        <div class="comando-btn" data-descricao="Parênteses: Definem a prioridade de cálculo em expressões matemáticas e lógicas.">( )</div>
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
                        <div class="comando-btn" data-descricao="Interrompe e sai imediatamente de um loop ou de um switch.">break</div>
                        <div class="comando-btn" data-descricao="Operador ternário: forma curta de escrever um if/else em uma linha (condicao ? seTrue : seFalse).">? :</div>
                        <div class="comando-btn" data-descricao="Switch moderno: executa o caso diretamente sem precisar de break.">-&gt;</div>
                        <div class="comando-btn" data-descricao="Define um conjunto fixo de constantes nomeadas (muito usado com switch).">enum</div>
                    </div>
                </div>

                <!-- Categoria: Entrada e Saída (I/O) -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-terminal" style="color: #00d2d3;"></i> Entrada, Saída e Formatação (I/O)</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Importa classes e pacotes externos para o programa (ex: import java.util.Scanner;).">import</div>
                        <div class="comando-btn" data-descricao="Representa a saída padrão de dados do sistema (o console/terminal).">System.out</div>
                        <div class="comando-btn" data-descricao="Representa a entrada padrão de dados do sistema (o teclado).">System.in</div>
                        <div class="comando-btn" data-descricao="Método do Scanner que lê uma linha inteira de texto digitada pelo usuário.">.nextLine()</div>
                        <div class="comando-btn" data-descricao="Método do Scanner que lê apenas a próxima palavra (até encontrar um espaço).">.next()</div>
                        <div class="comando-btn" data-descricao="Método do Scanner que lê um número inteiro digitado pelo usuário.">.nextInt()</div>
                        <div class="comando-btn" data-descricao="Método do Scanner que lê um número decimal (double) digitado no teclado.">.nextDouble()</div>
                        <div class="comando-btn" data-descricao="Método do Scanner que lê um valor lógico (true ou false).">.nextBoolean()</div>
                        <div class="comando-btn" data-descricao="Fecha o objeto Scanner e libera os recursos da memória.">.close()</div>
                        <div class="comando-btn" data-descricao="Marcador do printf para inserir um texto (String) na mensagem formatada.">%s</div>
                        <div class="comando-btn" data-descricao="Marcador do printf para inserir um número inteiro na mensagem formatada.">%d</div>
                        <div class="comando-btn" data-descricao="Marcador do printf para inserir um número decimal (ponto flutuante).">%f</div>
                        <div class="comando-btn" data-descricao="Marcador do printf para formatar um número decimal com 2 casas após o ponto.">%.2f</div>
                        <div class="comando-btn" data-descricao="Caractere especial de escape que pula para a próxima linha dentro de um texto.">\n</div>
                    </div>
                </div>

                <!-- Categoria: Biblioteca Matemática -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-square-root-variable" style="color: #ff6b81;"></i> Funções Matemáticas (Classe Math)</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Calcula a potência de um número: Math.pow(base, expoente).">Math.pow()</div>
                        <div class="comando-btn" data-descricao="Calcula a raiz quadrada positiva de um número (ex: Math.sqrt(64) retorna 8.0).">Math.sqrt()</div>
                        <div class="comando-btn" data-descricao="Retorna o maior valor entre dois números comparados.">Math.max()</div>
                        <div class="comando-btn" data-descricao="Retorna o menor valor entre dois números comparados.">Math.min()</div>
                        <div class="comando-btn" data-descricao="Retorna o valor absoluto (sempre positivo) de um número.">Math.abs()</div>
                        <div class="comando-btn" data-descricao="Arredonda um número decimal para o inteiro mais próximo.">Math.round()</div>
                        <div class="comando-btn" data-descricao="Arredonda um número decimal sempre para cima (teto).">Math.ceil()</div>
                        <div class="comando-btn" data-descricao="Arredonda um número decimal sempre para baixo (piso).">Math.floor()</div>
                        <div class="comando-btn" data-descricao="Gera um número decimal aleatório entre 0.0 (inclusivo) e 1.0 (exclusivo).">Math.random()</div>
                        <div class="comando-btn" data-descricao="Constante matemática com o valor de Pi (3.141592653589793).">Math.PI</div>
                    </div>
                </div>

                <!-- Categoria: Estruturas de Repetição -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-rotate-right" style="color: #ff4b4b;"></i> Estruturas de Repetição (Loops)</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Loop 'PARA'. Repete o código por um número exato de vezes.">for</div>
                        <div class="comando-btn" data-descricao="Loop 'ENQUANTO'. Repete o código enquanto uma condição for verdadeira.">while</div>
                        <div class="comando-btn" data-descricao="Executa o código pelo menos uma vez antes de checar a condição.">do</div>
                        <div class="comando-btn" data-descricao="Estrutura que garante a execução do bloco pelo menos uma vez antes do while.">do...while</div>
                        <div class="comando-btn" data-descricao="For-each: percorre automaticamente todos os itens de um Array ou lista.">for ( : )</div>
                        <div class="comando-btn" data-descricao="Pula para a próxima repetição do loop, ignorando o código abaixo dele.">continue</div>
                    </div>
                </div>

                <!-- Categoria: Arrays e Coleções -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-layer-group" style="color: #ce82ff;"></i> Arrays, Matrizes e Coleções</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Colchetes. Usados para criar e acessar itens em um Array (ex: notas[0]).">[ ]</div>
                        <div class="comando-btn" data-descricao="Chaves. Usadas para inicializar os valores de um Array direto na criação.">{ }</div>
                        <div class="comando-btn" data-descricao="Retorna o tamanho total (quantidade de itens) de um Array.">.length</div>
                        <div class="comando-btn" data-descricao="Matriz (Array multidimensional): armazena dados em linhas e colunas.">[ ][ ]</div>
                        <div class="comando-btn" data-descricao="Ordena automaticamente os elementos de um Array em ordem crescente.">Arrays.sort()</div>
                        <div class="comando-btn" data-descricao="Converte um Array inteiro em texto formatado para exibição.">Arrays.toString()</div>
                        <div class="comando-btn" data-descricao="Lista dinâmica que pode crescer ou diminuir de tamanho automaticamente.">ArrayList</div>
                        <div class="comando-btn" data-descricao="Coleção que armazena pares de Chave e Valor para buscas rápidas.">HashMap</div>
                        <div class="comando-btn" data-descricao="Conjunto de elementos únicos que não permite valores duplicados.">HashSet</div>
                        <div class="comando-btn" data-descricao="Adiciona um novo item dentro de um ArrayList ou HashSet.">.add()</div>
                        <div class="comando-btn" data-descricao="Obtém um elemento pela posição (ArrayList) ou pela chave (HashMap).">.get()</div>
                        <div class="comando-btn" data-descricao="Insere ou atualiza um par de chave e valor dentro de um HashMap.">.put()</div>
                        <div class="comando-btn" data-descricao="Remove um elemento de uma coleção (ArrayList, HashMap ou HashSet).">.remove()</div>
                        <div class="comando-btn" data-descricao="Verifica se um item existe dentro de uma lista ou conjunto.">.contains()</div>
                        <div class="comando-btn" data-descricao="Verifica se uma chave específica existe dentro de um HashMap.">.containsKey()</div>
                        <div class="comando-btn" data-descricao="Retorna todas as chaves armazenadas dentro de um HashMap.">.keySet()</div>
                        <div class="comando-btn" data-descricao="Retorna todos os valores armazenados dentro de um HashMap.">.values()</div>
                        <div class="comando-btn" data-descricao="União de conjuntos: adiciona todos os elementos de outra coleção.">.addAll()</div>
                        <div class="comando-btn" data-descricao="Interseção de conjuntos: mantém apenas os elementos em comum.">.retainAll()</div>
                        <div class="comando-btn" data-descricao="Verifica se uma coleção ou String está completamente vazia.">.isEmpty()</div>
                        <div class="comando-btn" data-descricao="Retorna a quantidade atual de elementos dentro de uma coleção.">.size()</div>
                        <div class="comando-btn" data-descricao="Limpa e remove todos os elementos de uma coleção.">.clear()</div>
                    </div>
                </div>

                <!-- Categoria: Manipulação de Strings e Datas -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-font" style="color: #58cc02;"></i> Operações com Strings e Datas</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="Retorna o número total de caracteres de uma String.">.length()</div>
                        <div class="comando-btn" data-descricao="Converte todas as letras do texto para MAIÚSCULAS.">.toUpperCase()</div>
                        <div class="comando-btn" data-descricao="Converte todas as letras do texto para minúsculas.">.toLowerCase()</div>
                        <div class="comando-btn" data-descricao="Recorta e retorna uma parte específica de uma String.">.substring()</div>
                        <div class="comando-btn" data-descricao="Compara se o conteúdo de duas Strings é exatamente igual.">.equals()</div>
                        <div class="comando-btn" data-descricao="Compara duas Strings ignorando diferenças entre maiúsculas e minúsculas.">.equalsIgnoreCase()</div>
                        <div class="comando-btn" data-descricao="Retorna o caractere situado em um índice específico do texto.">.charAt()</div>
                        <div class="comando-btn" data-descricao="Retorna a posição (índice) da primeira ocorrência de um trecho no texto.">.indexOf()</div>
                        <div class="comando-btn" data-descricao="Substitui um trecho de texto ou caractere por outro na String.">.replace()</div>
                        <div class="comando-btn" data-descricao="Substitui partes do texto que correspondem a uma expressão regular (Regex).">.replaceAll()</div>
                        <div class="comando-btn" data-descricao="Verifica se a String inteira corresponde a um padrão Regex.">.matches()</div>
                        <div class="comando-btn" data-descricao="Remove espaços em branco extras no início e no fim do texto.">.trim()</div>
                        <div class="comando-btn" data-descricao="Divide uma String em várias partes (Array) usando um separador.">.split()</div>
                        <div class="comando-btn" data-descricao="Classe para montar e modificar textos grandes com alto desempenho.">StringBuilder</div>
                        <div class="comando-btn" data-descricao="Adiciona novos conteúdos ao final de um StringBuilder.">.append()</div>
                        <div class="comando-btn" data-descricao="Classe da API java.time para representar datas (ano, mês e dia).">LocalDate</div>
                        <div class="comando-btn" data-descricao="Classe da API java.time para representar horários (hora, minuto e segundo).">LocalTime</div>
                        <div class="comando-btn" data-descricao="Classe da API java.time para representar data e hora completas.">LocalDateTime</div>
                        <div class="comando-btn" data-descricao="Formata e converte datas e horas em padrões personalizados (ex: dd/MM/yyyy).">DateTimeFormatter</div>
                        <div class="comando-btn" data-descricao="Retorna a data ou horário atual do sistema no momento da execução.">.now()</div>
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
                        <div class="comando-btn" data-descricao="Protegido: Acessível pela própria classe, mesmo pacote e classes filhas.">protected</div>
                        <div class="comando-btn" data-descricao="Aponta para o próprio objeto atual sendo executado na classe.">this</div>
                        <div class="comando-btn" data-descricao="Faz com que o método/variável pertença à classe e não ao objeto.">static</div>
                        <div class="comando-btn" data-descricao="Getters e Setters: métodos de encapsulamento para ler (get) e alterar (set) atributos privados.">get / set</div>
                        <div class="comando-btn" data-descricao="Herança: Faz uma classe filha herdar atributos e métodos de uma classe mãe.">extends</div>
                        <div class="comando-btn" data-descricao="Referencia a classe mãe (superclasse) para acessar seus métodos ou construtor.">super</div>
                        <div class="comando-btn" data-descricao="Define um contrato de métodos obrigatórios que uma classe deve implementar.">interface</div>
                        <div class="comando-btn" data-descricao="Indica que uma classe assina e implementa os métodos de uma interface.">implements</div>
                        <div class="comando-btn" data-descricao="Define uma classe base que não pode ser instanciada ou um método sem corpo.">abstract</div>
                        <div class="comando-btn" data-descricao="Anotação que indica a sobrescrita de um método herdado da superclasse.">@Override</div>
                        <div class="comando-btn" data-descricao="Verifica se um objeto é instância de uma determinada classe ou interface.">instanceof</div>
                        <div class="comando-btn" data-descricao="Superclasse raiz da qual todas as classes em Java herdam automaticamente.">Object</div>
                        <div class="comando-btn" data-descricao="Método padrão que retorna uma representação em texto de um objeto.">.toString()</div>
                        <div class="comando-btn" data-descricao="Retorna um código numérico hash identificador do objeto (usado em coleções Hash).">.hashCode()</div>
                        <div class="comando-btn" data-descricao="Generics: Permite parametrizar tipos em classes, interfaces e métodos.">&lt;T&gt;</div>
                        <div class="comando-btn" data-descricao="Método estático clássico do padrão de projeto Singleton para obter instância única.">getInstance()</div>
                        <div class="comando-btn" data-descricao="Define o pacote (pasta lógica) ao qual a classe Java pertence.">package</div>
                    </div>
                </div>

                <!-- Categoria: Tratamento de Erros -->
                <div class="categoria-section">
                    <h2><i class="fa-solid fa-triangle-exclamation" style="color: #ffc800;"></i> Tratamento de Erros e Exceções</h2>
                    <div class="comandos-grid">
                        <div class="comando-btn" data-descricao="'Tentar'. Bloco de código que será testado para ver se dá erro.">try</div>
                        <div class="comando-btn" data-descricao="'Capturar'. Bloco que é executado caso aconteça um erro no 'try'.">catch</div>
                        <div class="comando-btn" data-descricao="'Finalmente'. Código que é executado sempre, dando erro ou não.">finally</div>
                        <div class="comando-btn" data-descricao="Dispara e lança manualmente uma exceção dentro do código.">throw</div>
                        <div class="comando-btn" data-descricao="Declara na assinatura do método que ele pode lançar uma exceção.">throws</div>
                        <div class="comando-btn" data-descricao="Classe base principal usada para capturar exceções e erros em Java.">Exception</div>
                        <div class="comando-btn" data-descricao="Retorna a mensagem detalhada explicando a causa da exceção capturada.">.getMessage()</div>
                        <div class="comando-btn" data-descricao="Exceção lançada em erros aritméticos, como divisão inteira por zero.">ArithmeticException</div>
                        <div class="comando-btn" data-descricao="Exceção lançada ao tentar acessar métodos ou atributos de um objeto null.">NullPointerException</div>
                        <div class="comando-btn" data-descricao="Exceção lançada ao tentar acessar um índice inválido fora dos limites do Array.">ArrayIndexOutOfBoundsException</div>
                    </div>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
