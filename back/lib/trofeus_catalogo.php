<?php
/**
 * Catálogo central de troféus e conquistas do OPUS com caminhos de imagem reais
 * em front/assets/img/ e textos para o Tutor Opi comemorar conquistas.
 */

if (!function_exists('opus_trofeus_catalogo')) {
    function opus_trofeus_catalogo(): array {
        return [
            'primeiro_passo' => [
                'slug' => 'primeiro_passo',
                'nome' => 'Primeiro Passo',
                'desc' => 'Concluiu sua primeira lição.',
                'imagem' => 'primeirospassos.png',
                'cor' => '#ffc800',
                'raridade' => 'comum',
                'tutor_texto' => 'Você acabou de desbloquear o troféu **Primeiro Passo** por concluir sua primeira lição! Esse é o marco inicial da sua jornada em Java no **OPUS**.'
            ],
            'perfeicao' => [
                'slug' => 'perfeicao',
                'nome' => 'Mente Brilhante',
                'desc' => 'Gabaritou 100% das questões em uma lição!',
                'imagem' => 'mentebrilhante-3de3acertos.png',
                'cor' => '#1cb0f6',
                'raridade' => 'raro',
                'tutor_texto' => 'Que desempenho impecável! Você acertou todas as questões sem errar nenhuma e conquistou o troféu **Mente Brilhante**!'
            ],
            'capitulo_1' => [
                'slug' => 'capitulo_1',
                'nome' => 'Fundamentos',
                'desc' => 'Terminou o Capítulo 1.',
                'imagem' => 'fundamentos-capitulo1.png',
                'cor' => '#58cc02',
                'raridade' => 'comum',
                'tutor_texto' => 'Seção dominada! Você concluiu todos os desafios dos **Fundamentos** e desbloqueou o troféu **Fundamentos**.'
            ],
            'capitulo_2' => [
                'slug' => 'capitulo_2',
                'nome' => 'Caminhos Lógicos',
                'desc' => 'Dominou as Estruturas de Decisão no Cap. 2.',
                'imagem' => 'caminhoslogicos-capitulo2.png',
                'cor' => '#1cb0f6',
                'raridade' => 'raro',
                'tutor_texto' => 'Mandou muito bem! Você dominou essa etapa e garantiu o troféu **Caminhos Lógicos** na sua estante.'
            ],
            'capitulo_3' => [
                'slug' => 'capitulo_3',
                'nome' => 'Mestre da Repetição',
                'desc' => 'Dominou os Loops no Capítulo 3.',
                'imagem' => 'mestredarepeticao.png',
                'cor' => '#ce82ff',
                'raridade' => 'epico',
                'tutor_texto' => 'Incrível! Você concluiu os desafios dessa jornada e conquistou o troféu épico **Mestre da Repetição**!'
            ],
            'capitulo_4' => [
                'slug' => 'capitulo_4',
                'nome' => 'Senhor dos Arrays',
                'desc' => 'Dominou Arrays e Matrizes no Capítulo 4.',
                'imagem' => 'senhor dos arrays.png',
                'cor' => '#ff527b',
                'raridade' => 'epico',
                'tutor_texto' => 'Fantástico! Você mostrou domínio nas estruturas e ganhou o troféu **Senhor dos Arrays**!'
            ],
            'capitulo_5' => [
                'slug' => 'capitulo_5',
                'nome' => 'Arquiteto Java',
                'desc' => 'Concluiu POO no Capítulo 5. Você é o mestre!',
                'imagem' => 'arquitetojava.png',
                'cor' => '#ffc800',
                'raridade' => 'lendario',
                'tutor_texto' => 'Troféu lendário desbloqueado! Você conquistou **Arquiteto Java** e subiu para outro nível na programação!'
            ],
            'fogo_3' => [
                'slug' => 'fogo_3',
                'nome' => 'Em Chamas',
                'desc' => 'Alcançou 3 Dias de Fogo.',
                'imagem' => 'alcancos3diasdefogo.png',
                'cor' => '#ff9600',
                'raridade' => 'raro',
                'tutor_texto' => 'Sua chama está acesa! Por manter **3 dias seguidos** de prática, você desbloqueou o troféu **Em Chamas**!'
            ],
            'precisao_absoluta' => [
                'slug' => 'precisao_absoluta',
                'nome' => 'Precisão Absoluta',
                'desc' => 'Alcançou 150 XP acumulados.',
                'imagem' => 'medalha.png',
                'cor' => '#1cb0f6',
                'raridade' => 'raro',
                'tutor_texto' => 'Seu XP não para de subir! Ao ultrapassar **150 XP acumulados**, você garantiu o troféu **Precisão Absoluta**!'
            ],
            'sequencia_7' => [
                'slug' => 'sequencia_7',
                'nome' => 'Semana Perfeita',
                'desc' => 'Alcançou 7 Dias de Fogo.',
                'imagem' => 'medalha.png',
                'cor' => '#ff4b4b',
                'raridade' => 'epico',
                'tutor_texto' => 'Uma semana inteira praticando Java! Você desbloqueou o troféu épico **Semana Perfeita**!'
            ],
            'sequencia_30' => [
                'slug' => 'sequencia_30',
                'nome' => 'Mês Épico',
                'desc' => 'Alcançou 30 Dias de Fogo.',
                'imagem' => 'medalha.png',
                'cor' => '#ce82ff',
                'raridade' => 'lendario',
                'tutor_texto' => 'Disciplina lendária! **30 dias seguidos** estudando no OPUS renderam o troféu **Mês Épico**!'
            ],
            'batalha_irmaos' => [
                'slug' => 'batalha_irmaos',
                'nome' => 'Batalha de Irmãos',
                'desc' => 'Venceu uma Batalha de Fogo contra um amigo!',
                'imagem' => 'medalha.png',
                'cor' => '#ff527b',
                'raridade' => 'lendario',
                'tutor_texto' => 'Vitória na arena! Você venceu uma Batalha de Fogo contra um amigo e conquistou o troféu **Batalha de Irmãos**!'
            ],
        ];
    }
}

if (!function_exists('opus_trofeu_info')) {
    function opus_trofeu_info(string $slug): array {
        $catalogo = opus_trofeus_catalogo();
        if (isset($catalogo[$slug])) {
            return $catalogo[$slug];
        }

        return [
            'slug' => $slug,
            'nome' => ucwords(str_replace('_', ' ', $slug)),
            'desc' => 'Conquista desbloqueada na sua jornada Java!',
            'imagem' => 'medalha.png',
            'cor' => '#ffc800',
            'raridade' => 'comum',
            'tutor_texto' => 'Parabéns! Você desbloqueou um novo troféu e ele já está salvo na sua Sala de Troféus no **Perfil**!'
        ];
    }
}

if (!function_exists('opus_listar_trofeus_usuario')) {
    function opus_listar_trofeus_usuario(mysqli $conn, int $user_id): array {
        $slugs = [];
        if ($user_id <= 0) {
            return $slugs;
        }
        $stmt = $conn->prepare("SELECT trofeu_slug FROM user_trofeus WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    if (!empty($row['trofeu_slug'])) {
                        $slugs[] = (string) $row['trofeu_slug'];
                    }
                }
            }
            $stmt->close();
        }
        return $slugs;
    }
}

if (!function_exists('opus_montar_novos_trofeus')) {
    function opus_montar_novos_trofeus(array $slugs_antes, array $slugs_depois): array {
        $novos_slugs = array_values(array_diff($slugs_depois, $slugs_antes));
        $lista = [];
        foreach ($novos_slugs as $slug) {
            $lista[] = opus_trofeu_info((string) $slug);
        }
        return $lista;
    }
}
