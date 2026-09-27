<?php
$css = "
/* Novos Tipos de Licoes - Estilos Premium */
.typing-container, .complete-code-container {
    width: 100%;
    margin-top: 5px;
}
.typing-input {
    width: 100%;
    background: transparent;
    border: none;
    color: #ce82ff;
    padding: 10px 0;
    font-family: 'Courier New', monospace;
    font-size: 1.1rem;
    resize: none;
    height: 80px;
    font-weight: 600;
}
.typing-input:focus { outline: none; }

.code-snippet-box {
    font-family: 'Courier New', monospace;
    font-size: 1.15rem;
    line-height: 1.8;
}

.code-select {
    background: rgba(42, 45, 56, 0.8);
    border: 2px solid #1a36ca;
    color: #fff;
    padding: 4px 12px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 1.05rem;
    cursor: pointer;
    outline: none;
    transition: 0.3s;
    font-weight: 700;
}
.code-select:hover { border-color: #4b8df8; }

.code-input-inline {
    background: rgba(42, 45, 56, 0.8);
    border: 2px solid #2a2d38;
    border-bottom: 2px solid #4b8df8;
    color: #ce82ff;
    padding: 6px 12px;
    border-radius: 6px;
    font-family: 'Courier New', monospace;
    font-size: 1.1rem;
    width: 250px;
    outline: none;
    transition: all 0.3s;
    font-weight: 700;
}
.code-input-inline:focus {
    border-color: #4b8df8;
    background: rgba(26, 54, 202, 0.2);
}
";
file_put_contents('front/assets/css/licao.css', $css, FILE_APPEND);
echo "CSS Appended";
