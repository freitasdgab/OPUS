<?php
// Test script for challenges in licao.php and guides in dashboard.php

// 1. Extract $desafios_por_unidade from licao.php without running licao_logic.php
$licao_content = file_get_contents(__DIR__ . '/../../../front/pages/licao.php');

// We can extract everything between '$desafios_por_unidade = [' and the end of the array
if (preg_match('/\$desafios_por_unidade\s*=\s*\[(.*?)\];\s*function opus_obter_desafio/s', $licao_content, $m)) {
    eval('$desafios = [' . $m[1] . '];');
} else {
    die("Could not extract \$desafios_por_unidade\n");
}

echo "Total challenges extracted: " . count($desafios) . "\n";

// Check for literal backslashes \" in the array definition of licao.php
echo "\n--- Checking literal \\\" in licao.php desafios ---\n";
foreach ($desafios as $u => $d) {
    foreach ($d as $field => $val) {
        if (is_string($val) && strpos($val, '\"') !== false) {
            echo "Unit $u field '$field' contains literal \\\": $val\n";
        }
    }
}

// 2. Extract $guias from dashboard.php
$dash_content = file_get_contents(__DIR__ . '/../../../front/pages/dashboard.php');
echo "\n--- Checking literal \\\" in dashboard.php guides ---\n";
if (preg_match_all('/\'guia_codigo\'\s*=>\s*\'([^\']*)\'/', $dash_content, $mCode)) {
    foreach ($mCode[1] as $idx => $code) {
        if (strpos($code, '\"') !== false) {
            echo "Guide code idx $idx contains literal \\\": $code\n";
        }
    }
}
if (preg_match_all('/\'guia_texto\'\s*=>\s*\'([^\']*)\'/', $dash_content, $mTexto)) {
    foreach ($mTexto[1] as $idx => $texto) {
        if (strpos($texto, '\"') !== false) {
            echo "Guide text idx $idx contains literal \\\": $texto\n";
        }
    }
}

// 3. Check Section 6 duplicate variable
echo "\n--- Checking Section 6 ---\n";
echo "Codigo inicial:\n" . $desafios[6]['codigo_inicial'] . "\n";
echo "Dica codigo:\n" . $desafios[6]['dica_codigo'] . "\n";

// 4. Check all 40 sections for variable duplication between codigo_inicial and dica_codigo
echo "\n--- Checking variable duplications across all 40 sections ---\n";
for ($u = 1; $u <= 40; $u++) {
    if (!isset($desafios[$u])) {
        echo "Missing unit $u in desafios!\n";
        continue;
    }
    $init = $desafios[$u]['codigo_inicial'];
    $dica = $desafios[$u]['dica_codigo'];
    
    // Check if dica declares a variable that is already declared in codigo_inicial
    if (preg_match_all('/(?:int|double|float|long|boolean|String|char|var)\s+([a-zA-Z_]\w*)\s*=/m', $init, $mInit)) {
        $initVars = $mInit[1];
        if (preg_match_all('/(?:int|double|float|long|boolean|String|char|var)\s+([a-zA-Z_]\w*)\s*=/m', $dica, $mDica)) {
            $dicaVars = $mDica[1];
            $dups = array_intersect($initVars, $dicaVars);
            if (!empty($dups)) {
                echo "Unit $u has DUPLICATE VARS in init and dica: " . implode(', ', $dups) . "\n";
            }
        }
    }
}
