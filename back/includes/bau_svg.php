<?php
/**
 * Renderiza o Baú de Tesouro Pirata em estilo flat-vector (alinhado à identidade de ilhas do OPUS).
 * Zero IDs duplicados no SVG para suportar dezenas de unidades simultâneas na trilha sem conflitos.
 *
 * @param string $estado 'available' | 'locked' | 'claimed'
 * @param array  $opcoes ['class' => string, 'aria_label' => string]
 * @return string HTML do SVG inline
 */
if (!function_exists('opus_render_bau_svg')) {
    function opus_render_bau_svg(string $estado = 'available', array $opcoes = []): string
    {
        $estado = in_array($estado, ['available', 'locked', 'claimed'], true) ? $estado : 'available';
        $extraClass = trim((string) ($opcoes['class'] ?? ''));
        $ariaLabel = (string) ($opcoes['aria_label'] ?? '');

        $classes = trim("opus-bau-svg bau-state-{$estado} {$extraClass}");
        $ariaAttr = $ariaLabel !== ''
            ? 'role="img" aria-label="' . htmlspecialchars($ariaLabel, ENT_QUOTES, 'UTF-8') . '"'
            : 'aria-hidden="true"';

        // Paleta flat-vector fiel ao estilo de baú de madeira + bronze com rebites + ouro
        if ($estado === 'locked') {
            $woodFront      = '#a6876b';
            $woodFrontLine  = '#7a5f48';
            $woodFrontHi    = '#b89a7e';
            $woodSide       = '#856951';
            $woodSideLine   = '#614a37';
            $bronzeFront    = '#5b4b3f';
            $bronzeSide     = '#4a3d33';
            $bronzeDark     = '#3c3129';
            $rivetColor     = '#a99787';
            $lockOuter      = '#5e4d40';
            $lockInner      = '#43352b';
            $lockRing       = '#6d5a4c';
            $lockFace       = '#524237';
            $keyhole        = '#261d17';
        } elseif ($estado === 'claimed') {
            $woodFront      = '#d6954f';
            $woodFrontLine  = '#9c5d29';
            $woodFrontHi    = '#e2a764';
            $woodSide       = '#ad7439';
            $woodSideLine   = '#7d4c1f';
            $bronzeFront    = '#6e523e';
            $bronzeSide     = '#5c4332';
            $bronzeDark     = '#4b3526';
            $rivetColor     = '#b39476';
            $lockOuter      = '#735641';
            $lockInner      = '#513a2a';
            $lockRing       = '#80614b';
            $lockFace       = '#644936';
            $keyhole        = '#2c1a0e';
        } else {
            // available (herói vibrante — idêntico à referência!)
            $woodFront      = '#ee9d45';
            $woodFrontLine  = '#aa6024';
            $woodFrontHi    = '#f7b15e';
            $woodSide       = '#c47e36';
            $woodSideLine   = '#8c4f1d';
            $bronzeFront    = '#77553e';
            $bronzeSide     = '#654632';
            $bronzeDark     = '#533827';
            $rivetColor     = '#bf9972';
            $lockOuter      = '#7d5a42';
            $lockInner      = '#573c29';
            $lockRing       = '#8c674c';
            $lockFace       = '#6d4d37';
            $keyhole        = '#2c190d';
        }

        // Helper interno para moeda de ouro flat-vector (deitada ou inclinada)
        $moedaPlana = static function (float $cx, float $cy, float $rx = 10.5, float $ry = 4.4, float $esp = 3.2): string {
            $yBot = $cy + $esp;
            $xL = $cx - $rx;
            $xR = $cx + $rx;
            $rxIn = round($rx * 0.72, 2);
            $ryIn = round($ry * 0.62, 2);
            return ''
                . '<g class="bau-coin">'
                . "<path d=\"M {$xL} {$cy} L {$xL} {$yBot} A {$rx} {$ry} 0 0 0 {$xR} {$yBot} L {$xR} {$cy} Z\" fill=\"#cf8808\" />"
                . "<ellipse cx=\"{$cx}\" cy=\"{$cy}\" rx=\"{$rx}\" ry=\"{$ry}\" fill=\"#f9bf1d\" />"
                . "<ellipse cx=\"{$cx}\" cy=\"{$cy}\" rx=\"{$rxIn}\" ry=\"{$ryIn}\" fill=\"none\" stroke=\"#df980b\" stroke-width=\"1.2\" />"
                . '</g>';
        };

        $moedaInclinada = static function (float $cx, float $cy, float $r = 8.4, float $rot = -20): string {
            $rIn = round($r * 0.7, 2);
            $cxBack = round($cx + 2.2, 2);
            $cyBack = round($cy + 1.2, 2);
            return ''
                . "<g class=\"bau-coin\" transform=\"rotate({$rot} {$cx} {$cy})\">"
                . "<ellipse cx=\"{$cxBack}\" cy=\"{$cyBack}\" rx=\"{$r}\" ry=\"" . round($r * 0.86, 2) . "\" fill=\"#cf8808\" />"
                . "<ellipse cx=\"{$cx}\" cy=\"{$cy}\" rx=\"{$r}\" ry=\"" . round($r * 0.86, 2) . "\" fill=\"#f9bf1d\" />"
                . "<ellipse cx=\"{$cx}\" cy=\"{$cy}\" rx=\"{$rIn}\" ry=\"" . round($rIn * 0.84, 2) . "\" fill=\"none\" stroke=\"#df980b\" stroke-width=\"1.25\" />"
                . '</g>';
        };

        // Helper para estrela brilho 4 pontas (estilo vetor limpo da referência)
        $brilho4Pontas = static function (float $cx, float $cy, float $size, string $cls): string {
            $s = $size;
            $q = round($size * 0.16, 2);
            return "<path class=\"bau-sparkle {$cls}\" transform=\"translate({$cx} {$cy})\" "
                . "d=\"M 0 -{$s} Q {$q} -{$q} {$s} 0 Q {$q} {$q} 0 {$s} Q -{$q} {$q} -{$s} 0 Q -{$q} -{$q} 0 -{$s} Z\" "
                . "fill=\"#fff8d6\" />";
        };

        $svg = "<svg class=\"{$classes}\" viewBox=\"0 0 180 156\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\" {$ariaAttr}>";

        // 1. SOMBRA SUAVE NA BASE
        $svg .= '<rect class="bau-ground-shadow" x="18" y="128" width="144" height="11" rx="5.5" fill="#4f2d14" fill-opacity="0.26" />';

        // 2. TAMPA DO BAÚ (ABERTA para available/claimed; FECHADA abaulada para locked)
        if ($estado === 'locked') {
            $svg .= '<g class="bau-lid-closed">';
            // Arco da frente do barril (tampa fechada 3/4)
            $svg .= "<path d=\"M 54 45 L 132 45 C 145 45, 152 55, 152 70 L 74 70 C 74 55, 67 45, 54 45 Z\" fill=\"{$woodFront}\" />";
            // Ripas horizontais na curva da tampa frontal
            $svg .= "<path d=\"M 66 53 L 144 53\" stroke=\"{$woodFrontLine}\" stroke-width=\"2\" />";
            $svg .= "<path d=\"M 71 61.5 L 149 61.5\" stroke=\"{$woodFrontLine}\" stroke-width=\"2\" />";
            // Brilho sutil no topo da curva da tampa
            $svg .= "<path d=\"M 62 48.5 L 136 48.5\" stroke=\"{$woodFrontHi}\" stroke-width=\"2.2\" stroke-linecap=\"round\" opacity=\"0.55\" />";

            // Lateral esquerda arredondada (tampa de barril 3/4)
            $svg .= "<path d=\"M 34 70 C 34 54, 42 45, 54 45 C 66 45, 74 54, 74 70 Z\" fill=\"{$woodSide}\" />";
            $svg .= "<path d=\"M 38 56.5 L 70 56.5\" stroke=\"{$woodSideLine}\" stroke-width=\"1.8\" />";
            $svg .= "<path d=\"M 35.5 63.5 L 72.5 63.5\" stroke=\"{$woodSideLine}\" stroke-width=\"1.8\" />";

            // Faixas de bronze acompanhando o arco da tampa fechada
            // Faixa lateral esquerda (borda externa do arco)
            $svg .= "<path d=\"M 34 70 C 34 54, 42 45, 54 45 L 58 45 C 47 45, 41 54, 41 70 Z\" fill=\"{$bronzeSide}\" />";
            // Faixa central-esquerda (divisa lateral/frente)
            $svg .= "<path d=\"M 53 45 L 61 45 C 72 46, 78 55, 79 70 L 71 70 C 70 56, 64 47, 53 45 Z\" fill=\"{$bronzeFront}\" />";
            // Faixa direita
            $svg .= "<path d=\"M 127 45 L 134 45 C 145 46, 151 55, 152 70 L 144 70 C 143 56, 137 47, 127 45 Z\" fill=\"{$bronzeFront}\" />";
            // Rebites na tampa fechada
            $svg .= "<circle cx=\"42\" cy=\"58\" r=\"1.7\" fill=\"{$rivetColor}\" />";
            $svg .= "<circle cx=\"70\" cy=\"56\" r=\"1.7\" fill=\"{$rivetColor}\" />";
            $svg .= "<circle cx=\"74\" cy=\"64\" r=\"1.7\" fill=\"{$rivetColor}\" />";
            $svg .= "<circle cx=\"143\" cy=\"56\" r=\"1.7\" fill=\"{$rivetColor}\" />";
            $svg .= "<circle cx=\"147\" cy=\"64\" r=\"1.7\" fill=\"{$rivetColor}\" />";
            $svg .= '</g>';
        } else {
            // TAMPA ABERTA (estilo exato da referência: dorso abaulado à esquerda + interior com ripas + sombra curva)
            $lidOuterWood = ($estado === 'claimed') ? '#af7437' : '#c68139';
            $lidOuterLine = ($estado === 'claimed') ? '#7f4e1f' : '#90531f';
            $lidInnerWood = ($estado === 'claimed') ? '#875425' : '#9a5d25';
            $lidInnerLine = ($estado === 'claimed') ? '#5d3614' : '#6b3b13';
            $lidInnerShade = ($estado === 'claimed') ? '#492b13' : '#533015';

            $svg .= '<g class="bau-lid-open">';
            // Dorso curvo do barril visível à esquerda da tampa aberta
            $svg .= "<path d=\"M 34 18 C 12 24, 12 64, 34 70 Z\" fill=\"{$lidOuterWood}\" />";
            $svg .= "<path d=\"M 27.5 20.5 C 18.5 32, 18.5 56, 27.5 67.5\" stroke=\"{$lidOuterLine}\" stroke-width=\"1.8\" stroke-linecap=\"round\" />";
            $svg .= "<path d=\"M 21.5 25 C 15 36, 15 52, 21.5 63\" stroke=\"{$lidOuterLine}\" stroke-width=\"1.5\" stroke-linecap=\"round\" />";

            // Moldura de bronze externa da tampa aberta
            $svg .= "<rect x=\"34\" y=\"18\" width=\"82\" height=\"52\" fill=\"{$bronzeSide}\" />";
            // Interior de madeira com ripas horizontais
            $svg .= "<rect x=\"40\" y=\"23\" width=\"70\" height=\"47\" fill=\"{$lidInnerWood}\" />";
            $svg .= "<rect x=\"40\" y=\"31.5\" width=\"70\" height=\"2\" fill=\"{$lidInnerLine}\" />";
            $svg .= "<rect x=\"40\" y=\"41\" width=\"70\" height=\"2\" fill=\"{$lidInnerLine}\" />";
            $svg .= "<rect x=\"40\" y=\"50.5\" width=\"70\" height=\"2\" fill=\"{$lidInnerLine}\" />";
            $svg .= "<rect x=\"40\" y=\"60\" width=\"70\" height=\"2\" fill=\"{$lidInnerLine}\" />";
            // Sombra curva icônica do interior abaulado à direita da tampa
            $svg .= "<path d=\"M 101 23 C 83 32, 83 58, 101 70 L 110 70 L 110 23 Z\" fill=\"{$lidInnerShade}\" />";
            $svg .= '</g>';

            // 3. CONTEÚDO INTERNO DO BAÚ ABERTO
            if ($estado === 'available') {
                // Montanha transbordando de moedas de ouro (idêntica à referência!)
                $svg .= '<g class="bau-gold-mound">';
                // Fundo escuro discreto atrás das moedas
                $svg .= '<path d="M 40 70 Q 94 52 146 70 Z" fill="#8f5414" />';
                // Camada traseira de moedas
                $svg .= $moedaInclinada(57, 62, 9.2, -12);
                $svg .= $moedaInclinada(77, 60, 9.4, 15);
                $svg .= $moedaInclinada(98, 59, 9.8, -8);
                $svg .= $moedaInclinada(118, 60, 9.5, 14);
                $svg .= $moedaInclinada(137, 64, 8.8, 22);
                // Camada intermediária/frontal de moedas detalhadas
                $svg .= $moedaPlana(52, 67, 10.5, 4.2, 3.5);
                $svg .= $moedaPlana(69, 65, 11.2, 4.5, 3.8);
                $svg .= $moedaInclinada(86, 64, 9.6, 26);
                $svg .= $moedaInclinada(104, 65, 9.8, -22);
                $svg .= $moedaPlana(122, 66, 10.8, 4.3, 3.5);
                $svg .= $moedaPlana(139, 68, 9.5, 3.9, 3.2);
                $svg .= $moedaPlana(77, 69, 10.8, 4.0, 3.0);
                $svg .= $moedaPlana(114, 69, 10.4, 3.8, 3.0);
                $svg .= '</g>';
            } else {
                // Baú coletado: interior vazio com apenas 2 moedinhas remanescentes repousando discretamente na borda
                $svg .= '<g class="bau-claimed-interior">';
                $svg .= '<rect x="42" y="67.5" width="70" height="2.8" fill="#472711" />';
                $svg .= $moedaPlana(66, 68.2, 8.0, 3.0, 2.2);
                $svg .= $moedaInclinada(114, 68.5, 6.2, -18);
                $svg .= '</g>';
            }
        }

        // 4. CORPO PRINCIPAL DO BAÚ (PERSPECTIVA 3/4 COM RIPAS, FAIXAS DE BRONZE E REBITES)
        $svg .= '<g class="bau-body-group">';

        // Painel lateral esquerdo de madeira (mais sombreado)
        $svg .= "<rect x=\"41\" y=\"75\" width=\"31\" height=\"52\" fill=\"{$woodSide}\" />";
        $svg .= "<rect x=\"41\" y=\"87.5\" width=\"31\" height=\"2\" fill=\"{$woodSideLine}\" />";
        $svg .= "<rect x=\"41\" y=\"100\" width=\"31\" height=\"2\" fill=\"{$woodSideLine}\" />";
        $svg .= "<rect x=\"41\" y=\"112.5\" width=\"31\" height=\"2\" fill=\"{$woodSideLine}\" />";

        // Painel frontal principal de madeira (dourado-âmbar luminoso)
        $svg .= "<rect x=\"78\" y=\"75\" width=\"67\" height=\"52\" fill=\"{$woodFront}\" />";
        // Sutil brilho na ripa superior
        $svg .= "<rect x=\"79\" y=\"78\" width=\"65\" height=\"3\" fill=\"{$woodFrontHi}\" opacity=\"0.42\" />";
        // Divisórias das 4 ripas horizontais
        $svg .= "<rect x=\"78\" y=\"87.5\" width=\"67\" height=\"2\" fill=\"{$woodFrontLine}\" />";
        $svg .= "<rect x=\"78\" y=\"100\" width=\"67\" height=\"2\" fill=\"{$woodFrontLine}\" />";
        $svg .= "<rect x=\"78\" y=\"112.5\" width=\"67\" height=\"2\" fill=\"{$woodFrontLine}\" />";

        // Moldura / Estrutura de Bronze (faixas horizontais e 3 colunas verticais)
        // Borda superior (lado esquerdo + frente)
        $svg .= "<rect x=\"34\" y=\"70\" width=\"41\" height=\"6\" fill=\"{$bronzeSide}\" />";
        $svg .= "<rect x=\"75\" y=\"70\" width=\"77\" height=\"6\" fill=\"{$bronzeFront}\" />";
        // Borda inferior (lado esquerdo + frente)
        $svg .= "<rect x=\"34\" y=\"126\" width=\"41\" height=\"6\" fill=\"{$bronzeDark}\" />";
        $svg .= "<rect x=\"75\" y=\"126\" width=\"77\" height=\"6\" fill=\"{$bronzeSide}\" />";

        // 3 Faixas verticais de bronze (esquerda, quina central, direita)
        $svg .= "<rect x=\"34\" y=\"70\" width=\"8\" height=\"62\" fill=\"{$bronzeSide}\" />";
        $svg .= "<rect x=\"71\" y=\"70\" width=\"4\" height=\"62\" fill=\"{$bronzeSide}\" />";
        $svg .= "<rect x=\"75\" y=\"70\" width=\"4\" height=\"62\" fill=\"{$bronzeFront}\" />";
        $svg .= "<rect x=\"144\" y=\"70\" width=\"8\" height=\"62\" fill=\"{$bronzeFront}\" />";

        // Sombra fina interna sob a borda superior
        $svg .= "<rect x=\"42\" y=\"76\" width=\"29\" height=\"1.8\" fill=\"{$bronzeDark}\" opacity=\"0.35\" />";
        $svg .= "<rect x=\"79\" y=\"76\" width=\"65\" height=\"1.8\" fill=\"{$bronzeDark}\" opacity=\"0.28\" />";

        // Rebites metálicos alinhados nas 3 colunas verticais (5 por coluna = 15 rebites)
        $rivetCols = [38, 75, 148];
        $rivetRows = [81.5, 91.2, 101.0, 110.8, 120.5];
        foreach ($rivetCols as $rx) {
            foreach ($rivetRows as $ry) {
                $svg .= "<circle cx=\"{$rx}\" cy=\"{$ry}\" r=\"1.9\" fill=\"{$rivetColor}\" />";
            }
        }

        // 5. FECHADURA CENTRAL EM FORMATO DE OLHO/AMÊNDOA COM MEDALHÃO E BURACO DE CHAVE
        $svg .= '<g class="bau-lock-plate">';
        // Placa amendoada externa
        $svg .= "<path d=\"M 90 101 Q 100.5 91.5 111.5 91.5 Q 122.5 91.5 133 101 Q 122.5 110.5 111.5 110.5 Q 100.5 110.5 90 101 Z\" fill=\"{$lockOuter}\" />";
        // Recorte interno sombreado
        $svg .= "<path d=\"M 94.5 101 Q 102.5 94.5 111.5 94.5 Q 120.5 94.5 128.5 101 Q 120.5 107.5 111.5 107.5 Q 102.5 107.5 94.5 101 Z\" fill=\"{$lockInner}\" />";
        // Medalhão circular central
        $svg .= "<circle cx=\"111.5\" cy=\"101\" r=\"10.2\" fill=\"{$lockRing}\" />";
        $svg .= "<circle cx=\"111.5\" cy=\"101\" r=\"7.4\" fill=\"{$lockFace}\" />";
        // Buraco da fechadura
        $svg .= "<circle cx=\"111.5\" cy=\"99\" r=\"2.4\" fill=\"{$keyhole}\" />";
        $svg .= "<path d=\"M 110.3 100.2 L 112.7 100.2 L 113.7 105.6 L 109.3 105.6 Z\" fill=\"{$keyhole}\" />";
        $svg .= '</g>';

        // Cadeado em destaque quando TRANCADO
        if ($estado === 'locked') {
            $svg .= '<g class="bau-padlock-badge">';
            // Haste metálica do cadeado
            $svg .= '<path d="M 105 97 V 90.5 A 6.5 6.5 0 0 1 118 90.5 V 97" stroke="#cbd5e1" stroke-width="3.6" stroke-linecap="round" fill="none" />';
            $svg .= '<path d="M 105 97 V 90.5 A 6.5 6.5 0 0 1 118 90.5 V 97" stroke="#64748b" stroke-width="1.1" stroke-linecap="round" fill="none" opacity="0.55" />';
            // Corpo dourado-âmbar do cadeado
            $svg .= '<rect x="100.5" y="96" width="22" height="18" rx="4.5" fill="#f59e0b" />';
            $svg .= '<path d="M 112 96 H 118 A 4.5 4.5 0 0 1 122.5 100.5 V 109.5 A 4.5 4.5 0 0 1 118 114 H 112 Z" fill="#d97706" />';
            $svg .= '<rect x="100.5" y="96" width="22" height="18" rx="4.5" stroke="#452710" stroke-width="1.8" fill="none" />';
            // Fechadura no cadeado
            $svg .= '<circle cx="111.5" cy="103.5" r="2.2" fill="#3a210d" />';
            $svg .= '<path d="M 110.5 104.8 L 112.5 104.8 L 113.2 109.2 L 109.8 109.2 Z" fill="#3a210d" />';
            $svg .= '</g>';
        }

        $svg .= '</g>'; // fim bau-body-group

        // 6. MOEDAS ESPALHADAS NA BASE E BRILHOS 4 PONTAS (EXCLUSIVO DO ESTADO DISPONÍVEL / HERÓI)
        if ($estado === 'available') {
            $svg .= '<g class="bau-base-coins">';
            // Moedas no canto inferior esquerdo
            $svg .= $moedaPlana(46, 134, 9.2, 3.6, 3.2);
            $svg .= $moedaInclinada(32, 129, 8.2, -28);
            // Moeda central caída na frente
            $svg .= $moedaPlana(85, 139, 9.6, 3.6, 3.2);
            // Moedas no canto inferior direito
            $svg .= $moedaPlana(128, 137, 9.0, 3.4, 3.0);
            $svg .= $moedaInclinada(146, 133, 8.0, 24);
            $svg .= '</g>';

            // Brilhos estelares 4 pontas (idênticos ao estilo da imagem!)
            $svg .= '<g class="bau-sparkles-group">';
            $svg .= $brilho4Pontas(63, 53, 8.5, 'sp-1');
            $svg .= $brilho4Pontas(74, 39, 6.2, 'sp-2');
            $svg .= $brilho4Pontas(99, 52, 8.0, 'sp-3');
            $svg .= $brilho4Pontas(123, 58, 5.8, 'sp-4');
            $svg .= $brilho4Pontas(46, 121, 5.4, 'sp-5');
            $svg .= $brilho4Pontas(143, 125, 6.0, 'sp-6');
            $svg .= '</g>';
        }

        $svg .= '</svg>';
        return $svg;
    }
}
