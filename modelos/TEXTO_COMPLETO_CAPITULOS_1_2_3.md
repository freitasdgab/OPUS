# TEXTO DOS CAPÍTULOS 1, 2 E 3 INSERIDOS NO LIBREOFFICE
## Projeto: OPUS – Plataforma Web Interativa e Gamificada para o Aprendizado de Programação Java
**Autora:** Gabrieli dos Santos de Freitas  
**Orientador:** Prof. Saul J. C. Amaral  
**Instituição:** CEDUP – Centro de Educação Profissional Abílio Paulo (Criciúma / SC)

---

## CAPA E FOLHA DE ROSTO
- **Instituição:** CENTRO DE EDUCAÇÃO PROFISSIONAL ABÍLIO PAULO – CEDUP
- **Curso:** CURSO TÉCNICO EM INFORMÁTICA INTEGRADO AO ENSINO MÉDIO
- **Área de Concentração:** Desenvolvimento de Sistemas Web
- **Disciplina:** Modelagem de Sistemas
- **Título:** OPUS: PLATAFORMA WEB INTERATIVA E GAMIFICADA PARA O APRENDIZADO DE PROGRAMAÇÃO JAVA
- **Subtítulo:** Uma Abordagem Lúdica para o Ensino e Fixação de Conceitos Básicos de Programação
- **Nome do Aluno:** Gabrieli dos Santos de Freitas
- **Orientador:** Prof. Saul J. C. Amaral
- **Local e Ano:** CRICIÚMA - 2026

---

## 1. INTRODUÇÃO

A contínua evolução da Tecnologia da Informação impõe a necessidade constante de capacitação de novos desenvolvedores de software. No entanto, o ingresso na área de programação apresenta desafios consideráveis para iniciantes, sobretudo no domínio da lógica computacional e de linguagens de tipagem estática e orientadas a objetos, como a linguagem Java. A rigorosidade sintática e a necessidade de abstração algorítmica constituem barreiras recorrentes que resultam em expressivos índices de desmotivação e desistência nas fases iniciais de aprendizagem.

Nesse cenário, os métodos convencionais de ensino, comumente baseados em exposições teóricas e videoaulas passivas, mostram-se insuficientes para assegurar a fixação efetiva dos conceitos fundamentais. Em resposta a essa realidade, desenvolveu-se o OPUS: uma plataforma web interativa e gamificada idealizada para transformar o processo de aprendizagem em uma experiência dinâmica e envolvente. Estruturada em trilhas progressivas com auxílio de elementos lúdicos e mascotes, a ferramenta proporciona ao estudante o acompanhamento visual de sua própria evolução, incentivando a persistência prática e a retenção sólida do conhecimento.

### 1.1. TEMA
O presente trabalho tem como tema o desenvolvimento e a implementação de uma plataforma web gamificada voltada ao ensino e fixação dos conceitos fundamentais da linguagem de programação Java para estudantes iniciantes.

### 1.2. PROBLEMA DE PESQUISA
Embora haja ampla disponibilidade de cursos e tutoriais digitais na atualidade, muitos ingressantes na área de computação manifestam acentuada dificuldade em consolidar a lógica e os comandos basilares de Java — como tipos primitivos, variáveis, estruturas condicionais, laços de repetição e instanciação básica de classes. A falta de prática aplicada imediata costuma culminar no abandono dos estudos. Diante dessa problemática, formula-se a seguinte questão de pesquisa:

> **De que maneira uma plataforma web interativa, fundamentada em trilhas de lições gamificadas, pode contribuir para atenuar as dificuldades de fixação conceitual e elevar o engajamento contínuo de estudantes iniciantes no aprendizado da programação Java?**

### 1.3. HIPÓTESES
A adoção de uma metodologia pedagógica amparada na gamificação — fundamentada em trilhas de evolução progressiva, desafios interativos curtos e retorno visual imediato — potencializa a taxa de retenção e a memorização prática dos conceitos de programação em Java. Pressupõe-se que a percepção concreta do próprio progresso atue como fator motivacional essencial, reduzindo a sensação de frustração e estimulando a continuidade dos estudos com rendimento superior aos métodos expositivos tradicionais.

### 1.4. JUSTIFICATIVA
A justificativa para a concepção do projeto OPUS decorre das deficiências identificadas nas abordagens autodidatas convencionais. Em canais e plataformas de vídeo, a exemplo do YouTube, o formato predominante é o modelo de transmissão passiva: o instrutor desenvolve a solução em sua própria tela, e o estudante apenas observa. Em função do cansaço e da monotonia desse formato, o usuário tende a avançar sucessivamente para os próximos vídeos da playlist sem dedicar tempo à escrita e execução dos códigos, privando-se da prática necessária para consolidar a memória cognitiva e muscular. Quando desafiado a construir uma solução própria, o aluno se depara com a falta de autonomia e abandona o aprendizado.

Em contraponto, pesquisas consolidadas na área de Informática na Educação atestam que ambientes lúdicos favorecem diretamente os mecanismos de retenção e cognição. O formato interativo do OPUS, articulado por meio de mascotes orientadores e lições organizadas em trilhas de progressão, convida o aluno a experimentar o código em pequenos passos, com retorno imediato. Essa visualização clara das conquistas transforma o processo de aprendizagem em uma jornada motivadora e divertida, mitigando barreiras de entrada e conferindo segurança aos novos programadores.

---

## 2. OBJETIVOS

Neste capítulo são delineados os objetivos que orientam o desenvolvimento e a implementação da plataforma OPUS, divididos entre a meta geral da pesquisa e os desdobramentos específicos necessários para a sua realização.

### 2.1. OBJETIVO GERAL
Desenvolver uma aplicação web interativa e gamificada, denominada OPUS, voltada à facilitação da aprendizagem e à fixação de conceitos básicos da linguagem de programação Java para estudantes iniciantes.

### 2.2. OBJETIVOS ESPECÍFICOS
Para concretizar o objetivo geral proposto, foram estabelecidos os seguintes objetivos específicos:
- Investigar as principais dificuldades enfrentadas por aprendizes no primeiro contato com a linguagem Java e mapear as técnicas de gamificação eficazes para o suporte à memorização;
- Modelar a arquitetura do sistema, os diagramas de casos de uso e a estrutura do banco de dados relacional para gerenciamento de usuários, trilhas de lições e histórico de pontuação;
- Implementar a plataforma web integrando back-end em PHP com banco de dados MySQL e front-end responsivo em HTML5, CSS3 e JavaScript;
- Disponibilizar um ambiente administrativo para controle de lições e acompanhamento dos indicadores de progresso dos estudantes cadastrados.

---

## 3. FUNDAMENTAÇÃO TEÓRICA

A fundamentação teórica tem como finalidade sustentar conceitualmente as decisões de projeto e metodológicas adotadas no desenvolvimento do OPUS. Este capítulo aborda as particularidades e os desafios do ensino da linguagem de programação Java, os fundamentos científicos da gamificação aplicada à educação e a caracterização das tecnologias e ferramentas selecionadas para a construção da plataforma.

### 3.1. O ENSINO DE PROGRAMAÇÃO E A LINGUAGEM JAVA
O aprendizado inicial de programação de computadores envolve o desenvolvimento do pensamento computacional, exigindo do aprendiz habilidades de abstração, decomposição de problemas e formulação de algoritmos (WING, 2006). Quando introduzido por meio de linguagens fortemente tipadas e orientadas a objetos, como o Java, esse processo torna-se ainda mais desafiador. A rigorosidade exigida pela sintaxe, a declaração explícita de tipos de dados primitivos (como int, double, boolean) e a obrigatoriedade de estruturas formais de classes desde o primeiro programa constituem fontes recorrentes de sobrecarga cognitiva para o estudante iniciante.

Historicamente, os modelos tradicionais de ensino e os materiais didáticos disponibilizados na internet apoiam-se em métodos predominantemente passivos, tais como videoaulas e tutoriais lineares. Nesses ambientes, o aluno visualiza o instrutor escrevendo a lógica, mas raramente pratica na mesma velocidade, avançando de conteúdo sem a consolidação necessária. Para contornar essa limitação pedagógica, surge a necessidade de plataformas interativas que exijam a participação ativa e imediata do usuário na resolução de exercícios, promovendo a fixação dos conceitos fundamentais de lógica e sintaxe.

### 3.2. GAMIFICAÇÃO APLICADA À EDUCAÇÃO (GAMIFICATION)
A gamificação é conceituada como a utilização de elementos, mecânicas e dinâmicas de jogos em contextos não relacionados a entretenimento, com o propósito de engajar pessoas, motivar ações e solucionar problemas práticos (DETERDING et al., 2011; KAPP, 2012). No âmbito educacional, a aplicação de estratégias lúdicas estimula a curiosidade intrínseca do estudante, transformando tarefas complexas em metas graduais e recompensadoras (FARDO, 2013).

No sistema OPUS, a gamificação é empregada de forma estruturante por meio de múltiplos elementos: trilhas de aprendizagem divididas em unidades temáticas, acúmulo de experiência e pontuações por acertos, divisões competitivas em ligas, missões diárias, desbloqueio de troféus e mascotes orientadores que oferecem feedback visual e instantâneo a cada resposta. Essa arquitetura lúdica permite que o aprendiz identifique claramente o seu estágio de evolução, reforçando o sentimento de competência e reduzindo a taxa de desistência frente aos erros iniciais.

### 3.3. TECNOLOGIAS E FERRAMENTAS DE DESENVOLVIMENTO
A construção de uma plataforma web moderna, rápida e acessível diretamente via navegador requer a integração coordenada de tecnologias de back-end, banco de dados e front-end, operando sobre uma infraestrutura local de alta eficiência.

#### 3.3.1. Linguagem PHP e Arquitetura Cliente-Servidor
O PHP (Hypertext Preprocessor) é uma linguagem de script executada no lado do servidor (back-end), amplamente consolidada no desenvolvimento de sistemas web dinâmicos. No projeto OPUS, o PHP é responsável por gerenciar a camada de lógica de negócios, o controle seguro de sessões de usuários (autenticação de alunos e administradores), o processamento de respostas dos exercícios e a comunicação direta com a base de dados relacional.

#### 3.3.2. Sistema Gerenciador de Banco de Dados MySQL
O MySQL é um dos Sistemas Gerenciadores de Banco de Dados Relacionais (SGBD) mais utilizados globalmente, destacando-se pelo alto desempenho, confiabilidade e conformidade com os padrões da linguagem SQL. No OPUS, o MySQL armazena e organiza de forma relacional as informações essenciais da aplicação: dados cadastrais dos usuários, divisão em unidades e lições, histórico de pontuação, troféus conquistados, controle de ligas e registro de progresso diário.

#### 3.3.3. Ambiente de Execução Local: Laragon
Para a implantação e hospedagem do ambiente de execução do sistema, foi selecionado o Laragon. Trata-se de um ambiente integrado de desenvolvimento local moderno, isolado, portátil e altamente veloz para Windows, projetado para executar aplicações baseadas em PHP e MySQL com mínima sobrecarga de recursos do sistema operacional. O Laragon oferece suporte nativo ao servidor web Apache e ao gerenciamento otimizado de instâncias do MySQL, viabilizando testes ágeis e execução fluida da plataforma sem dependências desnecessárias.

#### 3.3.4. Front-end Interativo: HTML5, CSS3 e JavaScript
A camada de interface visual com o usuário (front-end) foi construída com tecnologias web padronizadas pelo W3C: HTML5 para estruturação semântica do conteúdo, CSS3 para estilização visual responsiva e moderna, e JavaScript para interatividade em tempo real. O JavaScript atua tanto na manipulação dinâmica do DOM quanto na renderização de animações no elemento Canvas, assegurando uma experiência de uso imersiva e agradável para os estudantes.

---

## 📚 FONTES E REFERÊNCIAS CITADAS (ARTIGOS E SITES VERIFICADOS)

1. **DETERDING, S.; DIXON, D.; KHALED, R.; NACKE, L.** From game design elements to gamefulness: defining gamification. In: *Proceedings of the 15th International Academic MindTrek Conference: Envisioning Future Media Environments*. ACM, p. 9–15, 2011. DOI: https://doi.org/10.1145/2181037.2181040
2. **KAPP, K. M.** *The Gamification of Learning and Instruction: Game-based Methods and Strategies for Training and Education*. San Francisco: Pfeiffer, 2012. ISBN: 978-1118096345.
3. **FARDO, M. L.** A gamificação aplicada em ambientes de aprendizagem. *Revista Novas Tecnologias na Educação (RENOTE)*, Porto Alegre, CINTED-UFRGS, v. 11, n. 1, p. 1-9, jul. 2013. Disponível em: https://seer.ufrgs.br/index.php/renote/article/view/41629
4. **WING, Jeannette M.** Computational Thinking. *Communications of the ACM*, v. 49, n. 3, p. 33–35, 2006. Disponível em: https://www.cs.cmu.edu/~15110-s13/Wing06-ct.pdf
5. **ORACLE CORPORATION.** *The Java™ Tutorials: Learning the Java Language*. Oracle Documentation, 2024. Disponível em: https://docs.oracle.com/javase/tutorial/java/index.html
6. **LARAGON.** *Laragon - Portable, isolated, fast & powerful universal development environment*. Laragon Documentation, 2024. Disponível em: https://laragon.org/
7. **ORACLE CORPORATION.** *MySQL 8.0 Reference Manual*. Oracle MySQL Documentation, 2024. Disponível em: https://dev.mysql.com/doc/refman/8.0/en/
