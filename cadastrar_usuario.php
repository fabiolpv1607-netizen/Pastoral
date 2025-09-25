<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado e se é um administrador
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (empty($username) || empty($password) || empty($role)) {
        $mensagem = "Por favor, preencha todos os campos.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (username, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sss", $username, $hashed_password, $role);
            if ($stmt->execute()) {
                $mensagem = "Novo usuário cadastrado com sucesso!";
            } else {
                $mensagem = "Erro ao cadastrar usuário: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $mensagem = "Erro interno do servidor.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Novo Usuário</title>
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
            <h2>Cadastrar Novo Usuário</h2>
            <?php if (!empty($mensagem)): ?>
                <p style="color: green; text-align: center;"><?php echo htmlspecialchars($mensagem); ?></p>
            <?php endif; ?>
            <form action="cadastrar_usuario.php" method="post" class="form-container">
                <div class="form-group">
                    <label for="username">Usuário:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Tipo de Usuário:</label>
                    <select id="role" name="role" required>
                        <option value="usuario">Usuário Padrão</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <button type="submit" class="submit-button">Cadastrar Usuário</button>
            </form>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Pastoral do Dízimo. Todos os direitos reservados.</p>
    </footer>
	<script src="js/script.js"></script>
</body>
</html>