<?php
session_start();
require_once 'conexao.php';

// Verifique se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php?acesso=negado");
    exit;
}

// Verifique se já existe um caixa aberto para o usuário
$usuario_id = $_SESSION['usuario_id'];
$sql_check = "SELECT id FROM caixas WHERE usuario_id = ? AND status = 'aberto'";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $usuario_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows > 0) {
    header("Location: index.php");
    exit;
}

// Processa a abertura do caixa
$mensagem = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $saldo_inicial = filter_input(INPUT_POST, 'saldo_inicial', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
    if ($saldo_inicial !== false) {
        $sql = "INSERT INTO caixas (usuario_id, data_abertura, saldo_inicial, status) VALUES (?, NOW(), ?, 'aberto')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("id", $usuario_id, $saldo_inicial);
        
        if ($stmt->execute()) {
            $_SESSION['caixa_id'] = $conn->insert_id;
            header("Location: index.php");
            exit;
        } else {
            $mensagem = "Erro ao abrir o caixa: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensagem = "Valor do saldo inicial inválido.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Conferência - Pastoral do Dízimo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <img src="img/logo.png" alt="Logotipo da Pastoral do Dízimo" class="logo">
            <h1 class="site-title">Pastoral do Dízimo</h1>
        </div>
        <nav class="nav-menu">
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
            <h2>Abrir Conferência</h2>
            <?php if (!empty($mensagem)): ?>
                <p style="color:red;"><?php echo htmlspecialchars($mensagem); ?></p>
            <?php endif; ?>
            <form action="abrir_caixa.php" method="post" class="form-container">
                <div class="form-group">
                    <label for="saldo_inicial">Saldo Inicial (R$):</label>
                    <input type="number" step="0.01" id="saldo_inicial" name="saldo_inicial" required>
                </div>
                <button type="submit" class="submit-button">Iniciar Conferência</button>
            </form>
        </section>
    </main>
    <footer class="footer">
        <p>&copy; 2025 Pastoral do Dízimo. Todos os direitos reservados.</p>
    </footer>
	 <script src="js/script.js"></script>
</body>
</html>