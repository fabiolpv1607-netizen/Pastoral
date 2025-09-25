<?php
session_start();
require_once 'conexao.php';

// Verifique se o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php?acesso=negado");
    exit;
}

// Verifique se o usuário é administrador
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$resultados = [];
$data_inicio = '';
$data_fim = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'] . ' 23:59:59';

    $sql = "SELECT c.data_abertura, c.data_fechamento, c.saldo_inicial, c.saldo_final, u.username 
            FROM caixas c
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.data_abertura BETWEEN ? AND ?
            ORDER BY c.data_abertura DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $data_inicio, $data_fim);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $resultados[] = $row;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastoral do Dízimo - Relatórios</title>
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
            <h2>Relatório de Conferência</h2>
            <form action="relatorio_caixas.php" method="post" class="filter-container">
                <div class="form-group">
                    <label for="data_inicio">De:</label>
                    <input type="date" id="data_inicio" name="data_inicio" value="<?php echo htmlspecialchars($data_inicio); ?>" required>
                </div>
                <div class="form-group">
                    <label for="data_fim">Até:</label>
                    <input type="date" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>" required>
                </div>
                <button type="submit" class="submit-button">Filtrar</button>
            </form>

            <?php if (!empty($resultados)): ?>
                <div class="report-table-container">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th>Abertura</th>
                                <th>Fechamento</th>
                                <th>Saldo Inicial</th>
                                <th>Total Recebido</th>
                                <th>Saldo Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $caixa): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($caixa['username']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($caixa['data_abertura'])); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($caixa['data_fechamento'])); ?></td>
                                    <td>R$ <?php echo number_format($caixa['saldo_inicial'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($caixa['saldo_final'] - $caixa['saldo_inicial'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($caixa['saldo_final'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Nenhum relatório encontrado para o período selecionado.</p>
            <?php endif; ?>
        </section>
    </main>
    <footer class="footer">
        <p>&copy; 2025 Pastoral do Dízimo. Todos os direitos reservados.</p>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>