<?php
// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php';

$dizimista = null;
$mensagem = '';
$id = null;

// Lógica para processar a atualização
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $paroquia = $_POST['paroquia'];
    $dataNascimento = $_POST['dataNascimento'];
    $dataCadastro = $_POST['dataCadastro'];

    $sql = "UPDATE pastoral_dizimo SET nome=?, endereco=?, telefone=?, paroquia=?, data_nascimento=?, data_cadastro=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $nome, $endereco, $telefone, $paroquia, $dataNascimento, $dataCadastro, $id);

    if ($stmt->execute()) {
        $mensagem = "Cadastro atualizado com sucesso!";
    } else {
        $mensagem = "Erro ao atualizar o cadastro: " . $stmt->error;
    }
    $stmt->close();
}

// Lógica para carregar os dados do dizimista a ser editado (para GET ou POST)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else if (isset($_POST['id'])) {
    $id = $_POST['id'];
}

if ($id) {
    $sql = "SELECT * FROM pastoral_dizimo WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dizimista = $result->fetch_assoc();
    } else {
        $mensagem = "Dizimista não encontrado.";
    }
    $stmt->close();
} else {
    $mensagem = "ID do dizimista não fornecido.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Cadastro - Pastoral do Dízimo</title>
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
            <h2>Atualizar Cadastro</h2>
            <?php if (!empty($mensagem)): ?>
                <p><?php echo htmlspecialchars($mensagem); ?></p>
            <?php endif; ?>

            <?php if ($dizimista): ?>
            <form action="atualizar_cadastro.php" method="post" class="form-container">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($dizimista['id']); ?>">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($dizimista['nome']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="endereco">Endereço:</label>
                    <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($dizimista['endereco']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="telefone">Telefone:</label>
                    <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($dizimista['telefone']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="paroquia">Paróquia:</label>
                    <input type="text" id="paroquia" name="paroquia" value="<?php echo htmlspecialchars($dizimista['paroquia']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="dataNascimento">Data de Nascimento:</label>
                    <input type="date" id="dataNascimento" name="dataNascimento" value="<?php echo htmlspecialchars($dizimista['data_nascimento']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="dataCadastro">Data do Cadastro:</label>
                    <input type="date" id="dataCadastro" name="dataCadastro" value="<?php echo htmlspecialchars($dizimista['data_cadastro']); ?>" required>
                </div>
                <button type="submit" class="submit-button">Atualizar Cadastro</button>
                <a href="consultar.php?nome=<?php echo urlencode($dizimista['nome']); ?>" class="button secondary-button">Voltar</a>
            </form>
            <?php else: ?>
                <p><?php echo htmlspecialchars($mensagem); ?></p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Pastoral do Dízimo. Todos os direitos reservados.</p>
    </footer>
	<script src="js/script.js"></script>
</body>
</html>