<?php
/**
 * Configuração central de cor/mascote por capítulo (unidade).
 *
 * As cores abaixo são as MESMAS já usadas em front/pages/dashboard.php
 * (variável $nomes_unidades), então o capítulo aparece com a cor idêntica
 * no dashboard e dentro da lição.
 *
 * Cada capítulo tem 3 estados de imagem do mascote (arquivos dentro de
 * front/assets/img/):
 *   - explicando: pose usada nos slides de explicação e como estado inicial do quiz
 *   - feliz:      resposta correta / lição gabaritada
 *   - triste:     resposta errada
 *
 * OBS: o mascote azul (capítulo 1) não tem uma imagem "triste" própria nos
 * assets enviados — foi usado "acertoumetade.png" (pose neutra) como
 * substituto mais próximo. Se você tiver/gerar uma versão azul triste,
 * é só trocar o valor abaixo.
 */

$MASCOTES_POR_CAPITULO = [
    1 => [ // Azul
        'cor'        => '#1cb0f6',
        'explicando' => 'explicando2.png',
        'feliz'      => 'acertoutudo.png',
        'triste'     => 'acertoumetade.png', // fallback: sem versão triste própria
    ],
    2 => [ // Rosa
        'cor'        => '#ff527b',
        'explicando' => 'rosaexplicando.png',
        'feliz'      => 'rosafeliz.png',
        'triste'     => 'rosabravo.png',
    ],
    3 => [ // Roxo
        'cor'        => '#ce82ff',
        'explicando' => 'roxoexplicando.png',
        'feliz'      => 'roxofeliz.png',
        'triste'     => 'roxotriste.png',
    ],
    4 => [ // Verde
        'cor'        => '#58cc02',
        'explicando' => 'verdeexplicando.png',
        'feliz'      => 'verdefeliz.png',
        'triste'     => 'verdetriste.png',
    ],
    5 => [ // Laranja
        'cor'        => '#ffc800',
        'explicando' => 'laranjaexplicando.png',
        'feliz'      => 'laranjafeliz.png',
        'triste'     => 'laranjatriste.png',
    ],
];

/**
 * Retorna a configuração de mascote/cor de um capítulo, com fallback seguro
 * para o capítulo 1 (azul) caso o número não esteja mapeado.
 */
function opus_mascote_do_capitulo(int $unidade_numero): array
{
    global $MASCOTES_POR_CAPITULO;
    return $MASCOTES_POR_CAPITULO[$unidade_numero] ?? $MASCOTES_POR_CAPITULO[1];
}
