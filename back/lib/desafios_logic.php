<?php
/**
 * OPUS - Lógica de Negócios da Aba de Desafios & Projetos de Código
 * 
 * Gerencia a progressão, verificação de pré-requisitos por seção,
 * salvamento de rascunhos e validação com recompensa de XP.
 */

if (!function_exists('desafios_obter_progresso_trilha')) {
    /**
     * Retorna o mapa de seções concluídas e a seção corrente do usuário na trilha.
     */
    function desafios_obter_progresso_trilha(mysqli $conn, int $user_id): array
    {
        $secoes_concluidas = [];
        $secao_atual = 1;
        $max_secao_alcancada = 1;

        $stmt = $conn->prepare("SELECT unidade_numero, status, licoes_concluidas FROM progresso_usuario WHERE usuario_id = ? ORDER BY unidade_numero ASC");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $num = (int) $row['unidade_numero'];
                $status = $row['status'];
                $licoes = (int) $row['licoes_concluidas'];

                if ($num > $max_secao_alcancada && ($status === 'corrente' || $status === 'completo')) {
                    $max_secao_alcancada = $num;
                }

                if ($status === 'corrente') {
                    $secao_atual = $num;
                }

                if ($status === 'completo' || $licoes >= 5) {
                    $secoes_concluidas[$num] = true;
                }
            }
            $stmt->close();
        }

        // Qualquer seção anterior à seção atual ou anterior à máxima alcançada
        // é considerada concluída pelo fluxo linear do curso.
        for ($i = 1; $i < $max_secao_alcancada; $i++) {
            $secoes_concluidas[$i] = true;
        }

        return [
            'secoes_concluidas'   => $secoes_concluidas,
            'secao_atual'         => $secao_atual,
            'max_secao_alcancada' => $max_secao_alcancada
        ];
    }
}

if (!function_exists('desafios_esta_desbloqueado')) {
    /**
     * Verifica se um desafio com requisito $secao_requerida está desbloqueado.
     * Exemplo: Calculadora requer que o aluno tenha concluído a Seção 5 (depois da sessão cinco).
     */
    function desafios_esta_desbloqueado(int $secao_requerida, array $progresso_trilha): bool
    {
        $concluidas = $progresso_trilha['secoes_concluidas'] ?? [];
        $secao_atual = $progresso_trilha['secao_atual'] ?? 1;
        $max_alcancada = $progresso_trilha['max_secao_alcancada'] ?? 1;

        // Se a seção requerida foi marcada como concluída
        if (!empty($concluidas[$secao_requerida])) {
            return true;
        }

        // Se o aluno já avançou além da seção requerida
        if ($max_alcancada > $secao_requerida || $secao_atual > $secao_requerida) {
            return true;
        }

        return false;
    }
}

if (!function_exists('desafios_listar')) {
    /**
     * Lista todos os desafios disponíveis na plataforma com o estado atual do usuário.
     */
    function desafios_listar(mysqli $conn, int $user_id): array
    {
        $progresso_trilha = desafios_obter_progresso_trilha($conn, $user_id);

        // Mapeia o progresso do usuário nos desafios práticos
        $user_desafios = [];
        $stmt_u = $conn->prepare("SELECT desafio_id, status, codigo_salvo, concluido_em, xp_ganho, tentativas FROM desafios_projetos_usuario WHERE usuario_id = ?");
        if ($stmt_u) {
            $stmt_u->bind_param("i", $user_id);
            $stmt_u->execute();
            $res_u = $stmt_u->get_result();
            while ($ru = $res_u->fetch_assoc()) {
                $user_desafios[(int)$ru['desafio_id']] = $ru;
            }
            $stmt_u->close();
        }

        // Busca o catálogo de desafios
        $lista = [];
        $sql = "SELECT id, slug, titulo, subtitulo, descricao, secao_requerida, requisitos_titulo, requisitos_desc, categoria, dificuldade, xp_recompensa, icone, cor, arquivo_nome, codigo_inicial, missao_detalhada, exemplo_saida, dica, ordem FROM desafios_projetos ORDER BY ordem ASC, secao_requerida ASC";
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $did = (int) $row['id'];
                $secao_req = (int) $row['secao_requerida'];
                $desbloqueado = desafios_esta_desbloqueado($secao_req, $progresso_trilha);

                $u_info = $user_desafios[$did] ?? null;
                $concluido = ($u_info && $u_info['status'] === 'concluido');

                if ($concluido) {
                    $status_final = 'concluido';
                } elseif ($desbloqueado) {
                    $status_final = 'disponivel';
                } else {
                    $status_final = 'bloqueado';
                }

                // Cálculo didático de progresso até o desbloqueio
                $secao_atual = max(1, $progresso_trilha['max_secao_alcancada']);
                $pct_requisito = min(100, (int) round(($secao_atual / $secao_req) * 100));

                $row['id'] = $did;
                $row['secao_requerida'] = $secao_req;
                $row['xp_recompensa'] = (int) $row['xp_recompensa'];
                $row['desbloqueado'] = $desbloqueado;
                $row['concluido'] = $concluido;
                $row['status'] = $status_final;
                $row['concluido_em'] = $u_info['concluido_em'] ?? null;
                $row['codigo_salvo'] = $u_info['codigo_salvo'] ?? null;
                $row['tentativas'] = (int) ($u_info['tentativas'] ?? 0);
                $row['secao_atual_usuario'] = $secao_atual;
                $row['progresso_porcentagem'] = $pct_requisito;

                $lista[] = $row;
            }
        }

        return [
            'desafios'          => $lista,
            'progresso_trilha'  => $progresso_trilha,
            'total'             => count($lista),
            'concluidos_count'  => count(array_filter($lista, fn($d) => $d['concluido'])),
            'desbloqueados_count'=> count(array_filter($lista, fn($d) => $d['desbloqueado'])),
            'bloqueados_count'  => count(array_filter($lista, fn($d) => !$d['desbloqueado']))
        ];
    }
}

if (!function_exists('desafios_obter_detalhe')) {
    /**
     * Obtém os detalhes completos de um desafio específico por ID ou SLUG.
     */
    function desafios_obter_detalhe(mysqli $conn, int $user_id, $id_ou_slug): ?array
    {
        $progresso_trilha = desafios_obter_progresso_trilha($conn, $user_id);

        $sql = "SELECT * FROM desafios_projetos WHERE " . (is_numeric($id_ou_slug) ? "id = ?" : "slug = ?") . " LIMIT 1";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return null;

        if (is_numeric($id_ou_slug)) {
            $intId = (int) $id_ou_slug;
            $stmt->bind_param("i", $intId);
        } else {
            $stmt->bind_param("s", $id_ou_slug);
        }
        $stmt->execute();
        $desafio = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$desafio) return null;

        $did = (int) $desafio['id'];
        $secao_req = (int) $desafio['secao_requerida'];
        $desbloqueado = desafios_esta_desbloqueado($secao_req, $progresso_trilha);

        // Progresso do usuário no desafio
        $u_info = null;
        $stmt_u = $conn->prepare("SELECT status, codigo_salvo, concluido_em, xp_ganho, tentativas FROM desafios_projetos_usuario WHERE usuario_id = ? AND desafio_id = ? LIMIT 1");
        if ($stmt_u) {
            $stmt_u->bind_param("ii", $user_id, $did);
            $stmt_u->execute();
            $u_info = $stmt_u->get_result()->fetch_assoc();
            $stmt_u->close();
        }

        $concluido = ($u_info && $u_info['status'] === 'concluido');
        $codigo_atual = (!empty($u_info['codigo_salvo'])) ? $u_info['codigo_salvo'] : $desafio['codigo_inicial'];

        $desafio['id'] = $did;
        $desafio['secao_requerida'] = $secao_req;
        $desafio['xp_recompensa'] = (int) $desafio['xp_recompensa'];
        $desafio['desbloqueado'] = $desbloqueado;
        $desafio['concluido'] = $concluido;
        $desafio['status'] = $concluido ? 'concluido' : ($desbloqueado ? 'disponivel' : 'bloqueado');
        $desafio['codigo_atual'] = $codigo_atual;
        $desafio['codigo_salvo'] = $u_info['codigo_salvo'] ?? null;
        $desafio['concluido_em'] = $u_info['concluido_em'] ?? null;
        $desafio['tentativas'] = (int) ($u_info['tentativas'] ?? 0);
        $desafio['progresso_trilha'] = $progresso_trilha;

        return $desafio;
    }
}

if (!function_exists('desafios_salvar_rascunho')) {
    /**
     * Salva o código que o usuário está digitando como rascunho.
     */
    function desafios_salvar_rascunho(mysqli $conn, int $user_id, int $desafio_id, string $codigo): bool
    {
        $stmt = $conn->prepare("INSERT INTO desafios_projetos_usuario (usuario_id, desafio_id, status, codigo_salvo, tentativas)
                                VALUES (?, ?, 'em_progresso', ?, 1)
                                ON DUPLICATE KEY UPDATE 
                                  codigo_salvo = VALUES(codigo_salvo),
                                  status = IF(status = 'concluido', 'concluido', 'em_progresso'),
                                  tentativas = tentativas + 1");
        if (!$stmt) return false;
        $stmt->bind_param("iis", $user_id, $desafio_id, $codigo);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}

if (!function_exists('desafios_submeter')) {
    /**
     * Valida o desafio, salva a solução e premia o usuário com XP.
     */
    function desafios_submeter(mysqli $conn, int $user_id, int $desafio_id, string $codigo, string $saida, bool $sucesso_compilacao): array
    {
        $desafio = desafios_obter_detalhe($conn, $user_id, $desafio_id);
        if (!$desafio) {
            return ['sucesso' => false, 'erro' => 'Desafio não encontrado no sistema.'];
        }

        if (!$desafio['desbloqueado']) {
            return [
                'sucesso' => false, 
                'erro' => "Este desafio ainda está bloqueado! Conclua a Seção {$desafio['secao_requerida']} na trilha de aprendizado para desbloqueá-lo."
            ];
        }

        if (!$sucesso_compilacao) {
            return [
                'sucesso' => false,
                'erro' => 'O código apresentou erro de compilação ou execução. Corrija os erros indicados no terminal antes de submeter.'
            ];
        }

        $saida_normalizada = mb_strtolower(trim($saida), 'UTF-8');
        $validador_tipo = $desafio['validador_tipo'] ?? 'saida_contem';
        $validador_regra = $desafio['validador_regra'] ?? '';
        $passou = false;
        $mensagem_erro = "A saída do programa ainda não atende aos requisitos do projeto. Confira os objetivos da missão e tente novamente!";

        // Validação didática flexível conforme a regra do desafio
        if ($validador_tipo === 'saida_contem') {
            $palavras_necessarias = array_map('trim', explode(',', mb_strtolower($validador_regra, 'UTF-8')));
            $todas_presentes = true;
            $faltando = [];

            foreach ($palavras_necessarias as $palavra) {
                if ($palavra !== '' && strpos($saida_normalizada, $palavra) === false) {
                    $todas_presentes = false;
                    $faltando[] = $palavra;
                }
            }

            if ($todas_presentes) {
                $passou = true;
            } else {
                $mensagem_erro = "A saída do console não conteve os resultados esperados: '" . implode("', '", $faltando) . "'. Verifique suas impressões e cálculos!";
            }
        } elseif ($validador_tipo === 'regex') {
            if (@preg_match('/' . $validador_regra . '/i', $saida_normalizada)) {
                $passou = true;
            }
        } else {
            // Fallback genérico: se executou com sucesso e produziu alguma saída não vazia
            $passou = (strlen($saida_normalizada) > 3);
        }

        if (!$passou) {
            return [
                'sucesso' => false,
                'erro' => $mensagem_erro,
                'dica' => $desafio['dica'] ?? ''
            ];
        }

        // Desafio CONCLUÍDO com sucesso!
        $ja_concluido = !empty($desafio['concluido']);
        $xp_base = (int) $desafio['xp_recompensa'];
        $xp_efetivo = $ja_concluido ? 10 : $xp_base; // Se for repetição, ganha 10 XP simbólico

        // Registra a conclusão no banco
        $stmt = $conn->prepare("INSERT INTO desafios_projetos_usuario 
                                (usuario_id, desafio_id, status, codigo_salvo, concluido_em, xp_ganho, tentativas)
                                VALUES (?, ?, 'concluido', ?, NOW(), ?, 1)
                                ON DUPLICATE KEY UPDATE 
                                  status = 'concluido',
                                  codigo_salvo = VALUES(codigo_salvo),
                                  concluido_em = IF(concluido_em IS NULL, NOW(), concluido_em),
                                  xp_ganho = GREATEST(xp_ganho, VALUES(xp_ganho)),
                                  tentativas = tentativas + 1");
        if ($stmt) {
            $stmt->bind_param("iisi", $user_id, $desafio_id, $codigo, $xp_efetivo);
            $stmt->execute();
            $stmt->close();
        }

        // Credita XP ao usuário e sincroniza liga/ofensiva
        $conn->query("UPDATE usuarios SET xp = xp + $xp_efetivo WHERE id = $user_id");

        // Dispara procedures do Opus se existirem usando opus_call para drenar os result sets
        if (function_exists('opus_call')) {
            @opus_call($conn, "CALL sp_liga_registrar_xp(?, ?)", "ii", [$user_id, $xp_efetivo]);
            @opus_call($conn, "CALL sp_registrar_missao_progresso(?, ?, ?)", "iii", [$user_id, $xp_efetivo, 1]);
            @opus_call($conn, "CALL sp_atualizar_fogo(?)", "i", [$user_id]);
        }

        // Troféu especial para o primeiro desafio de código concluído
        $res_count = $conn->query("SELECT COUNT(*) as total FROM desafios_projetos_usuario WHERE usuario_id = $user_id AND status = 'concluido'");
        $total_concluidos = ($res_count && ($rc = $res_count->fetch_assoc())) ? (int)$rc['total'] : 1;

        if ($total_concluidos === 1) {
            @$conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'primeiro_passo')");
        }

        return [
            'sucesso'          => true,
            'mensagem'         => $ja_concluido 
                ? "Parabéns por refazer o projeto! Sua solução foi validada com sucesso (+{$xp_efetivo} XP)." 
                : "Excelente trabalho! Você concluiu o projeto '{$desafio['titulo']}' com sucesso e ganhou +{$xp_efetivo} XP!",
            'xp_ganho'         => $xp_efetivo,
            'ja_concluido'     => $ja_concluido,
            'desafio_titulo'   => $desafio['titulo'],
            'total_concluidos' => $total_concluidos
        ];
    }
}
