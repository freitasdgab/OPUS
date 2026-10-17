# Original User Request

## 2026-10-17T22:19:38Z

This is a single self-contained fix; keep it small and focused. Remover a página de teste isolada da IDE (`front/pages/ide.php`) e seu respectivo link na barra lateral (`back/sidebar.php`) do sistema OPUS, preservando integralmente todos os demais scripts, estilos e endpoints da IDE utilizados nas outras páginas da plataforma.

Working directory: c:\xampp\htdocs\OPUS
Integrity mode: development

## Requirements

### R1. Remoção do atalho da IDE na Sidebar
Remover o item de menu correspondente à IDE (`ide.php` / label `IDE`) do arquivo `back/sidebar.php`, mantendo todos os demais links da barra lateral (`dashboard.php`, `ranking.php`, `ligas.php`, `dicionario.php`, `perfil.php`, botão de modo claro e logout) intactos e funcionais.

### R2. Exclusão da página de teste isolada da IDE
Excluir o arquivo `front/pages/ide.php`, que servia apenas como página de teste antes da implantação definitiva da IDE na plataforma.

### R3. Preservação do restante do código da IDE
Não remover nem alterar nenhum outro arquivo, estilo (`front/assets/css/ide.css`), script (`front/assets/js/ide.js`) ou endpoint de backend da IDE utilizado pelo restante do sistema.

## Acceptance Criteria

### Verificação de Arquivos e Sintaxe
- [ ] O arquivo `c:\xampp\htdocs\OPUS\front\pages\ide.php` não existe mais no diretório do projeto.
- [ ] O arquivo `c:\xampp\htdocs\OPUS\back\sidebar.php` não contém referência a `ide.php` nem ao item `'label' => 'IDE'`.
- [ ] O arquivo `c:\xampp\htdocs\OPUS\back\sidebar.php` possui sintaxe PHP válida (verificável via `php -l back/sidebar.php` sem erros).
- [ ] Todos os demais itens do array `$itens_menu` em `back/sidebar.php` (`dashboard.php`, `ranking.php`, `ligas.php`, `dicionario.php`, `perfil.php`) permanecem presentes.
- [ ] Nenhum outro arquivo do projeto (como `front/assets/css/ide.css`, `front/assets/js/ide.js` ou arquivos em `back/`) foi excluído ou modificado indevidamente (`git status` ou verificação de arquivos confirma que apenas `back/sidebar.php` foi editado e `front/pages/ide.php` removido).
