<?php
session_start();

// Verifica se existe um caixa aberto para o usuário logado
if (isset($_SESSION['caixa_id'])) {
    // NOVO: Define uma mensagem de aviso na sessão em vez de usar um alert.
    $_SESSION['aviso'] = 'Você precisa fechar sua conferência atual antes de sair do sistema.';
    
    // NOVO: Redireciona diretamente para a página de fechar o caixa.
    header('Location: fechar_caixa.php');
    exit;
}

// Se não houver caixa aberto, encerra a sessão e redireciona para o login.
session_destroy();
header("Location: login.php");
exit;
?>