# ESTRUTURA E ROTEIRO DE PESQUISA DO TCC / PIN (CEDUP 2026)
## Projeto: OPUS – Plataforma Interativa e Gamificada para Ensino de Programação

> **Arquivos Analisados na Pasta `modelos/`:**
> 1. `2026-MODELO_COMPLETO.odt`: Modelo oficial para o documento escrito no LibreOffice.
> 2. `00-JFC_PIN-Manual.pdf`: Manual oficial com as regras e diretrizes do Projeto Integrador (Prof. Jucemar F. Cândido).
> 3. `JFC_PI-Modelo_Apresentacao-2026.pdf`: Modelo oficial dos slides para a banca de defesa 2026.
> 4. `Apresentação_08.pptx` e `Apresentação_Artigo.pptx`: Exemplos reais de apresentações aprovadas pela banca do CEDUP.

---

## 🎯 Regra de Ouro da Avaliação (O que Fazer vs. O que Descartar)

Conforme a nota oficial do professor no modelo:
> **"Os itens em amarelo são obrigatórios; os outros itens, o orientador pode definir quais necessários ou alterações se desejar."**

Para economizar seu tempo, manter o foco no que dá nota e evitar trabalho inútil:
- ✅ **Faremos APENAS o que está em amarelo (obrigatório)** + os diagramas e especificações técnicas de **Modelagem de Sistemas** indispensáveis para o curso de Informática.
- ❌ **Dispensamos o que é opcional**: Glossário, Apêndices (termo de consentimento/atas), Anexos e Índices Remissivos.

Além disso, a estrutura do documento escrito (`.odt`) foi pensada para alimentar diretamente os slides da sua apresentação final (`JFC_PI-Modelo_Apresentacao-2026.pdf`), sem retrabalho!

---

## 🗺️ Ordem Cronológica de Execução (Roteiro de Pesquisa e Escrita)

Siga rigorosamente esta ordem para produzir seu TCC sem bloqueios:

```
┌────────────────────────────────────────────────────────────────────────┐
│ FASE 1: DEFINIÇÃO DE ESCOPO E IDENTIFICAÇÃO                            │
│ 1. Capa e Folha de Rosto (Dados institucionais do CEDUP)              │
│ 2. Tema, Problema de Pesquisa, Hipóteses e Justificativa (Capítulo 1)  │
│ 3. Objetivos Geral e Específicos (Capítulo 2)                          │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│ FASE 2: PESQUISA TEÓRICA E FERRAMENTAS (Fundamentação)                 │
│ 4. Fundamentação Teórica / Referencial Teórico (Capítulo 3)            │
│    • Dificuldades no Ensino de Programação                             │
│    • Gamificação na Educação (Pontos, Níveis, Trilhas)                │
│    • Tecnologias Utilizadas (PHP, MySQL, HTML5, CSS3, JS, Apache)      │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│ FASE 3: ENGENHARIA E TELAS DO OPUS (Modelagem de Sistemas)             │
│ 5. Metodologia / Desenvolvimento (Capítulo 4)                          │
│    • Levantamento de Requisitos (Funcionais e Não Funcionais)          │
│    • Modelagem do Banco de Dados (DER baseado no opus.sql)             │
│    • Diagramas UML (Casos de Uso e Fluxo de Atividades)                │
│    • Apresentação Prática das Telas (Prints do OPUS comentados)        │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│ FASE 4: FECHAMENTO E DEFESA                                            │
│ 6. Conclusão e Planos Futuros (Capítulo 5)                             │
│ 7. Referências Bibliográficas (ABNT NBR 6023 justificado)              │
│ 8. Resumo e Abstract (Feitos por último!)                              │
│ 9. Montagem dos Slides da Defesa (Baseados nos modelos .pptx)          │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 📖 Guia Detalhado Seção por Seção

---

### 1. ELEMENTOS PRÉ-TEXTUAIS (Capa e Folha de Rosto)
*Status: OBRIGATÓRIO (Em amarelo)*

No arquivo `2026-MODELO_COMPLETO.odt`, substitua os campos em maiúsculas:
- **Instituição:** CENTRO DE EDUCAÇÃO PROFISSIONAL ABÍLIO PAULO – CEDUP
- **Curso:** CURSO TÉCNICO EM INFORMÁTICA INTEGRADO AO ENSINO MÉDIO
- **Área de Concentração:** Desenvolvimento de Sistemas Web
- **Disciplina:** Modelagem de Sistemas
- **Título do Trabalho:** OPUS: PLATAFORMA WEB INTERATIVA E GAMIFICADA PARA O APRENDIZADO DE PROGRAMAÇÃO
- **Subtítulo:** Uma abordagem lúdica para o ensino de lógica computacional a estudantes iniciantes
- **Nome do Aluno:** Seu nome completo
- **Orientador(a):** Prof. Esp. Jucemar Formigoni Cândido (ou quem for o orientador designado)
- **Local e Ano:** CRICIÚMA - 2026

---

### 2. CAPÍTULO 1: INTRODUÇÃO
*Status: OBRIGATÓRIO (Em amarelo no modelo)*

> **Regra do professor:** Texto redigido na 3ª pessoa do singular (impessoal: *"este estudo analisa"*, *"o presente sistema propõe"*, nunca *"meu projeto"*).

#### 1.1 Tema
- **O que colocar:** Contextualização da área de Tecnologia da Informação, a importância crescente do aprendizado de programação e o uso de plataformas interativas/gamificadas como ferramentas facilitadoras.

#### 1.2 Problema de Pesquisa
- **Regra:** Deve ser obrigatoriamente uma **pergunta** clara e solucionável.
- **Pergunta padrão sugerida para o OPUS:**
  > *"De que forma uma plataforma web interativa, fundamentada em elementos de gamificação, pode auxiliar na redução das dificuldades iniciais e promover o engajamento de estudantes no aprendizado de lógica e conceitos básicos de programação?"*

#### 1.3 Hipóteses
- **O que é:** Uma afirmação prévia que será comprovada ou refutada com o desenvolvimento do sistema.
- **Hipótese sugerida:**
  > *"A utilização de um ambiente de aprendizagem dinâmico e gamificado, que ofereça feedback imediato, progressão por etapas e pontuações, proporciona maior motivação e melhor retenção dos conceitos fundamentais de programação em comparação aos métodos puramente teóricos e estáticos."*

#### 1.4 Justificativa
- **O que pesquisar para embasar:**
  - Elevados índices de desmotivação e evasão em cursos iniciais de computação;
  - A barreira da abstração inicial (sintaxe rígida, raciocínio algorítmico);
  - A relevância do OPUS como uma ferramenta prática, acessível via navegador, para aproximar os estudantes da tecnologia de forma intuitiva e divertida.

---

### 3. CAPÍTULO 2: OBJETIVOS
*Status: OBRIGATÓRIO (Em amarelo no modelo)*

#### 2.1 Objetivo Geral
- **Regra:** Exatamente uma frase objetiva com verbo de ação no infinitivo.
- **Texto sugerido:**
  > *"Desenvolver uma aplicação web interativa e gamificada, denominada OPUS, voltada à facilitação do aprendizado de lógica e fundamentos de programação para estudantes iniciantes."*

#### 2.2 Objetivos Específicos
- **Regra:** De 3 a 5 tópicos iniciados com verbos no infinitivo:
  1. *Investigar* as principais dificuldades encontradas por iniciantes no aprendizado de programação e os benefícios da aplicação da gamificação no ensino;
  2. *Modelar* a arquitetura de software, casos de uso e a estrutura do banco de dados relacional para controle de usuários, módulos e progresso;
  3. *Implementar* o sistema web utilizando PHP, banco de dados MySQL, e front-end responsivo em HTML5, CSS3 e JavaScript;
  4. *Disponibilizar* um módulo administrativo para gerenciamento de usuários e acompanhamento do progresso das lições.

---

### 4. CAPÍTULO 3: FUNDAMENTAÇÃO TEÓRICA
*Status: OBRIGATÓRIO (Em amarelo no modelo)*

Aqui você apresenta a revisão bibliográfica com conceitos e autores da área.

#### O que pesquisar (Tópicos obrigatórios):
1. **O Ensino de Programação e Pensamento Computacional:**
   - Desafios cognitivos no aprendizado de algoritmos;
   - Referências: Papert, Wing (2006).
2. **Gamificação na Educação (Gamification):**
   - Conceito: uso de mecânicas de jogos em contextos de aprendizagem;
   - Elementos: pontos, níveis, desafios e feedback imediato;
   - Referências: Karl Kapp (2012), Deterding et al. (2011), Fardo (2013).
3. **Tecnologias e Ferramentas Empregadas no OPUS:**
   - *Arquitetura Web Cliente-Servidor e protocolo HTTP;*
   - *Linguagem PHP e servidor Apache (XAMPP);*
   - *SGBD Relacional MySQL e linguagem SQL;*
   - *Front-end: HTML5 semântico, CSS3 e JavaScript.*

---

### 5. CAPÍTULO 4: METODOLOGIA OU DESENVOLVIMENTO
*Status: OBRIGATÓRIO (Em amarelo no modelo)*

Este é o capítulo prático do projeto, essencial para a disciplina de **Modelagem de Sistemas**:

#### 4.1 Caracterização da Pesquisa
- Pesquisa aplicada, com abordagem prática de engenharia de software e prototipagem iterativa.

#### 4.2 Levantamento de Requisitos do OPUS
- **Requisitos Funcionais (RF):**
  - `RF01`: Cadastro de novos usuários com validação de dados;
  - `RF02`: Autenticação e controle de sessões (Login e Logout);
  - `RF03`: Controle de níveis de acesso (Aluno comum vs. Administrador);
  - `RF04`: Exibição de trilha de aprendizado com lições e desafios;
  - `RF05`: Mecânica de pontuação / XP e acompanhamento do progresso;
  - `RF06`: Painel do administrador para consulta e gestão de usuários/conteúdos.
- **Requisitos Não-Funcionais (RNF):**
  - `RNF01` (Usabilidade): Interface simples, interativa e intuitiva;
  - `RNF02` (Desempenho): Carregamento ágil das páginas e respostas do servidor local;
  - `RNF03` (Segurança): Armazenamento de senhas em hash e proteção contra injeção SQL;
  - `RNF04` (Compatibilidade): Operação compatível com navegadores modernos (Chrome, Firefox, Edge).

#### 4.3 Modelagem do Sistema
- **Diagrama Entidade-Relacionamento (DER):**
  - Estrutura extraída diretamente do script de banco de dados do projeto (`opus.sql`): tabelas de usuários, progresso, módulos e lições.
- **Diagrama de Casos de Uso (UML):**
  - Atores: Aluno/Visitante e Administrador.
  - Casos de uso: Efetuar cadastro, Fazer login, Acessar dashboard, Executar lição, Visualizar ranking/pontos, Administrar sistema.
- **Diagrama de Atividades / Fluxograma:**
  - Fluxo do aluno desde o login até a conclusão de um módulo de programação.

#### 4.4 Implementação e Telas do Sistema
- Capturas de tela (prints) do sistema OPUS com legendas explicativas (padrão CEDUP):
  - *Figura 1 – Página Inicial de Apresentação (`index.html`)*
  - *Figura 2 – Telas de Login e Cadastro (`auth.html`)*
  - *Figura 3 – Dashboard do Aluno (`dashboard.php`)*
  - *Figura 4 – Painel Administrativo (`admin_dashboard.php`)*
  - *Figura 5 – Interface de Lições Interativas*

---

### 6. CAPÍTULO 5: CONCLUSÃO
*Status: OBRIGATÓRIO (Em amarelo no modelo)*

> **Regras rígidas do manual do professor:**
> - Não utilizar citações de autores neste capítulo;
> - Frases curtas, diretas e afirmativas;
> - Confirmar que os objetivos (geral e específicos) foram atingidos;
> - Afirmar a validação da hipótese formulada no Capítulo 1.

#### 5.1 Implementações Futuras (Trabalhos Futuros)
- Melhorias planejadas para o OPUS:
  - Criação de um interpretador de código em tempo real no navegador;
  - Criação de aplicativo mobile para estudo portátil;
  - Módulo social com ranking de pontuação entre escolas e fórum de ajuda mútua.

---

### 7. REFERÊNCIAS BIBLIOGRÁFICAS
*Status: OBRIGATÓRIO (Em amarelo no modelo)*

- Formatação exigida no modelo do CEDUP: ordem alfabética, alinhamento justificado e espaçamento simples entre linhas (ABNT NBR 6023).

---

### 8. RESUMO E ABSTRACT
*Status: OBRIGATÓRIO (Feito por último)*

- **Resumo:** Parágrafo único (100 a 500 palavras), justificado, sem recuo na primeira linha, cobrindo: introdução rápida, objetivos, metodologia utilizada e principais resultados alcançados no OPUS.
- **Palavras-chave:** Mínimo 3 e máximo 5, separadas por ponto:
  *Programação. Gamificação. Desenvolvimento Web. PHP. Modelagem de Sistemas.*
- **Abstract & Keywords:** Tradução formal em inglês do resumo.

---

## ❌ O Que Você NÃO Precisa Fazer (Economize esforço!)

Os seguintes itens **NÃO** são obrigatórios e você pode apagá-los do seu `.odt`:
- **Glossário** (não obrigatório);
- **Apêndices A, B, C...** (não obrigatório, reservado apenas a quem aplica termos formais de entrevista);
- **Anexos** (não obrigatório);
- **Índice Remissivo / de Autores** (dispensável em projetos de TCC/PI).

---

## 💡 Como vamos prosseguir agora?

Tudo será gerado exclusivamente aqui na pasta `modelos/`. 

Podemos avançar escrevendo os capítulos em arquivos markdown formatados, com os textos prontos para você copiar e colar diretamente no LibreOffice (`2026-MODELO_COMPLETO.odt`).

**Deseja que eu comece redigindo agora o Capítulo 1 (Introdução completa com Tema, Problema, Hipóteses e Justificativa) e o Capítulo 2 (Objetivos)?**
