<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php?acesso=negado");
    exit;
}

include 'conexao.php';

$devolucao = null;
$mensagem = '';
$id = null;

// Lógica para processar a atualização do Dízimo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    $valor_devolvido = $_POST['valor_devolvido'];
    $data_devolucao = $_POST['data_devolucao'];
    $forma_devolucao = $_POST['forma_devolucao'];
    $mes_referencia = $_POST['mes_referencia'];

    if (empty($valor_devolvido)) {
        $valor_devolvido = 0;
    }

    $sql = "UPDATE historico_devolucoes_dizimo SET valor_devolvido=?, data_devolucao=?, forma_devolucao=?, mes_referencia=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dsssi", $valor_devolvido, $data_devolucao, $forma_devolucao, $mes_referencia, $id);

    if ($stmt->execute()) {
        $mensagem = "Devolução atualizada com sucesso!";
    } else {
        $mensagem = "Erro ao atualizar a devolução: " . $stmt->error;
    }
    $stmt->close();

} else if (isset($_GET['id'])) {
    // Lógica para carregar os dados da devolução a ser editada
    $id = $_GET['id'];
}

// Se o ID estiver definido (vindo do GET ou do POST), busca os dados para exibir
if ($id) {
    $sql = "SELECT h.*, d.nome FROM historico_devolucoes_dizimo h JOIN pastoral_dizimo d ON h.dizimista_id = d.id WHERE h.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $devolucao = $result->fetch_assoc();
    } else {
        $mensagem = "Registro de devolução não encontrado.";
    }
    $stmt->close();
} else {
    // Se não há ID nem no GET, nem no POST
    $mensagem = "ID do registro não fornecido.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Devolução - Pastoral do Dízimo</title>
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
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-content">
        <section class="card">
            <h2>Atualizar Devolução</h2>
            <?php if (!empty($mensagem)): ?>
                <p><?php echo htmlspecialchars($mensagem); ?></p>
            <?php endif; ?>
            
            <?php if ($devolucao): ?>
            <h3>Dizimista: <?php echo htmlspecialchars($devolucao['nome']); ?></h3>
            <form action="atualizar_devolucao.php" method="post" class="form-container">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($devolucao['id']); ?>">
                <div class="form-group">
                    <label for="valor_devolvido">Valor do Dízimo Devolvido:</label>
                    <input type="number" step="0.01" id="valor_devolvido" name="valor_devolvido" value="<?php echo htmlspecialchars($devolucao['valor_devolvido']); ?>">
                </div>
                <div class="form-group">
                    <label for="data_devolucao">Data da Devolução:</label>
                    <input type="date" id="data_devolucao" name="data_devolucao" value="<?php echo htmlspecialchars($devolucao['data_devolucao']); ?>">
                </div>
                <div class="form-group">
                    <label for="mes_referencia">Mês Referente:</label>
                    <select id="mes_referencia" name="mes_referencia" required>
                        <option value="Janeiro" <?php if($devolucao['mes_referencia'] == 'Janeiro') echo 'selected'; ?>>Janeiro</option>
                        <option value="Fevereiro" <?php if($devolucao['mes_referencia'] == 'Fevereiro') echo 'selected'; ?>>Fevereiro</option>
                        <option value="Março" <?php if($devolucao['mes_referencia'] == 'Março') echo 'selected'; ?>>Março</option>
                        <option value="Abril" <?php if($devolucao['mes_referencia'] == 'Abril') echo 'selected'; ?>>Abril</option>
                        <option value="Maio" <?php if($devolucao['mes_referencia'] == 'Maio') echo 'selected'; ?>>Maio</option>
                        <option value="Junho" <?php if($devolucao['mes_referencia'] == 'Junho') echo 'selected'; ?>>Junho</option>
                        <option value="Julho" <?php if($devolucao['mes_referencia'] == 'Julho') echo 'selected'; ?>>Julho</option>
                        <option value="Agosto" <?php if($devolucao['mes_referencia'] == 'Agosto') echo 'selected'; ?>>Agosto</option>
                        <option value="Setembro" <?php if($devolucao['mes_referencia'] == 'Setembro') echo 'selected'; ?>>Setembro</option>
                        <option value="Outubro" <?php if($devolucao['mes_referencia'] == 'Outubro') echo 'selected'; ?>>Outubro</option>
                        <option value="Novembro" <?php if($devolucao['mes_referencia'] == 'Novembro') echo 'selected'; ?>>Novembro</option>
                        <option value="Dezembro" <?php if($devolucao['mes_referencia'] == 'Dezembro') echo 'selected'; ?>>Dezembro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="forma_devolucao">Forma de Devolução:</label>
                    <select id="forma_devolucao" name="forma_devolucao" required>
                        <option value="Dinheiro" <?php if($devolucao['forma_devolucao'] == 'Dinheiro') echo 'selected'; ?>>Dinheiro</option>
                        <option value="Pix" <?php if($devolucao['forma_devolucao'] == 'Pix') echo 'selected'; ?>>Pix</option>
                        <option value="Cartão" <?php if($devolucao['forma_devolucao'] == 'Cartão') echo 'selected'; ?>>Cartão</option>
                    </select>
                </div>
                <button type="submit" class="submit-button">Atualizar Devolução</button>

					<a href="consultar.php?nome=<?php echo urlencode($devolucao['nome']); ?>" class="button secondary-button">Voltar</a>
            </form>
            <?php else: ?>
                <p>Nenhum registro encontrado para atualização.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Pastoral do Dízimo. Todos os direitos reservados.</p>
    </footer>
	<script src="js/script.js"></script>
</body>
</html>