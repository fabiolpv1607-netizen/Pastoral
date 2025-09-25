<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php?acesso=negado");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastoral do Dízimo - Cadastro</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <img src="img/logo.png" alt="Logotipo da Pastoral do Dízimo" class="logo">
            <h1 class="site-title">Pastoral do Dízimo</h1>
        </div>
<nav class="nav-menu">
    <button class="menu-toggle" aria-controls="main-navigation" aria-expanded="false">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>
     <ul id="main-navigation" class="menu-links">
        <li><a href="index.php">Início</a></li>
        <li><a href="cadastrar.php">Cadastrar</a></li>
        <li><a href="consultar.php">Consultar</a></li>
        <li><a href="relatorio_caixas.php">Relatórios</a></li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li><a href="cadastrar_usuario.php">Cadastrar Usuário</a></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['caixa_id'])): ?>
            <li><a href="fechar_caixa.php">Fechar Conferência</a></li>
        <?php else: ?>
            <li><a href="abrir_caixa.php">Abrir Conferência</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Sair</a></li>
    </ul>
</nav>
    </header>

    <main class="main-content">
        <section class="card">
            <h2>Formulário de Cadastro</h2>
            <form action="processar_cadastro.php" method="post" class="form-container">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div class="form-group">
                    <label for="endereco">Endereço:</label>
                    <input type="text" id="endereco" name="endereco" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone:</label>
                    <input type="tel" id="telefone" name="telefone" required>
                </div>
                <div class="form-group">
                    <label for="paroquia">Comunidade:</label>
                    <input type="text" id="paroquia" name="paroquia" required>
                </div>
                <div class="form-group">
                    <label for="dataNascimento">Data de Nascimento:</label>
                    <input type="date" id="dataNascimento" name="dataNascimento" required>
                </div>
                <div class="form-group">
                    <label for="dataCadastro">Data do Cadastro:</label>
                    <input type="date" id="dataCadastro" name="dataCadastro" required>
                </div>
                <button type="submit" class="submit-button">Cadastrar</button>
            </form>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Pastoral do Dízimo. Todos os direitos reservados.</p>
    </footer>
	<script src="js/script.js"></script>
</body>
</html>