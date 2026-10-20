<?php
/**
 * Conteúdo do curso (divisões, unidades e desafios práticos).
 *
 * Estes dados ficavam fixos em front/pages/dashboard.php e
 * front/pages/licao.php; agora moram nas tabelas `divisoes`, `unidades`
 * e `desafios` (ver database/opus.sql e database/migrations/001_conteudo_curso.sql).
 * As funções devolvem arrays no mesmo formato que as páginas já usavam.
 */

/**
 * Unidades (seções) indexadas pelo número.
 * Formato: [numero => ['titulo','nome','descricao','cor','mascote','guia_texto','guia_codigo','licoes' => [1..N]]]
 */
function conteudo_unidades(mysqli $conn): array
{
    $unidades = [];
    $res = $conn->query("SELECT numero, titulo, nome, descricao, cor, mascote, guia_texto, guia_codigo, total_licoes FROM unidades ORDER BY numero ASC");
    while ($row = $res->fetch_assoc()) {
        $numero = (int) $row['numero'];
        $total  = max(1, (int) $row['total_licoes']);
        $unidades[$numero] = [
            'titulo'      => $row['titulo'],
            'nome'        => $row['nome'],
            'descricao'   => $row['descricao'],
            'cor'         => $row['cor'],
            'mascote'     => $row['mascote'],
            'guia_texto'  => $row['guia_texto'],
            'guia_codigo' => $row['guia_codigo'],
            'licoes'      => range(1, $total),
        ];
    }
    return $unidades;
}

/**
 * Divisões da trilha indexadas pelo id.
 */
function conteudo_divisoes(mysqli $conn): array
{
    $divisoes = [];
    $res = $conn->query("SELECT id, nome, tema, descricao, secao_inicio, secao_fim, icone, cor, badge FROM divisoes ORDER BY id ASC");
    while ($row = $res->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $row['secao_inicio'] = (int) $row['secao_inicio'];
        $row['secao_fim'] = (int) $row['secao_fim'];
        $divisoes[$row['id']] = $row;
    }
    return $divisoes;
}

/**
 * Id da divisão que contém a seção informada (fallback: primeira divisão).
 */
function conteudo_divisao_da_secao(array $divisoes, int $secao): int
{
    foreach ($divisoes as $id => $d) {
        if ($secao >= $d['secao_inicio'] && $secao <= $d['secao_fim']) {
            return (int) $id;
        }
    }
    return (int) (array_key_first($divisoes) ?? 1);
}

/**
 * Desafio prático (lição 5) da unidade, com fallback para o da unidade 1.
 */
function conteudo_desafio(mysqli $conn, int $unidade_numero): ?array
{
    $sql = "SELECT badge, heading, intro, missao, alvo_visual, dica_texto, dica_codigo, arquivo, codigo_inicial, validador_js, mensagem_ajuste
            FROM desafios WHERE unidade_numero IN (?, 1)
            ORDER BY (unidade_numero = ?) DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $unidade_numero, $unidade_numero);
    $stmt->execute();
    $desafio = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $desafio ?: null;
}
