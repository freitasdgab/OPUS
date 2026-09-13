<?php
// Inicia a sessão para salvar dados do usuário que está entrando no sistema
session_start();

// Exemplo de lógica back-end: Registrar que o usuário acessou a aplicação hoje
$_SESSION['user_started'] = true;
$_SESSION['start_time'] = date('Y-m-d H:i:s');

/* Aqui você pode fazer validações extras, como verificar cookies,
  ou registrar acessos em uma tabela de logs do banco de dados do Opus.
*/

// Após processar a lógica, redireciona o usuário para a tela principal (Ex: dashboard.php ou as lições)
// Se a tela destino estiver dentro da pasta 'back/', basta mudar o caminho.
header("Location: dashboard.php");
exit();
?>