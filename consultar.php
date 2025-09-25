<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['caixa_id'])) {
    header("Location: index.php");
    exit;
}


// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php';

$resultados = [];
$nome_pesquisado = '';
$mes_pesquisado = '';

// Bloco para processar a atualização do Dízimo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['devolucao_dizimo']) && $_POST['devolucao_dizimo'] == 'Sim') {

    $dizimista_id = $_POST['id'];
    $data_devolucao = $_POST['data_devolucao'];
    $valor_devolvido = $_POST['valor_devolvido'];
    $forma_devolucao = $_POST['forma_devolucao'];
    $mes_referencia = $_POST['mes_referencia']; // NOVO CAMPO

    if (empty($valor_devolvido)) {
        $valor_devolvido = 0;
    }
    
    if (empty($data_devolucao)) {
        $data_devolucao = date("Y-m-d");
    }

    // A instrução SQL foi atualizada para incluir 'mes_referencia'
	$caixa_id = $_SESSION['caixa_id']; // ID do caixa atual
    $sql = "INSERT INTO historico_devolucoes_dizimo (dizimista_id, valor_devolvido, data_devolucao, forma_devolucao, mes_referencia, caixa_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // A ligação de parâmetros foi atualizada para 'idsss'
    // 'i' para inteiro (dizimista_id), 'd' para decimal (valor_devolvido), e 's' para string (data, forma, mes)
     $stmt->bind_param("idsssi", $dizimista_id, $valor_devolvido, $data_devolucao, $forma_devolucao, $mes_referencia, $caixa_id);

    if ($stmt->execute()) {
        echo "<script>alert('Dízimo registrado com sucesso!'); window.location.href='consultar.php';</script>";
    } else {
        echo "<script>alert('Erro ao registrar o dízimo: " . $stmt->error . "'); window.location.href='consultar.php';</script>";
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Restante do código PHP...

// Bloco para processar a pesquisa
if ($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['nome']) || isset($_GET['mes']))) {
    $nome_pesquisado = $_GET['nome'] ?? '';
    $mes_pesquisado = $_GET['mes'] ?? '';

    $sql = "SELECT id, nome, endereco, telefone, paroquia, data_nascimento, data_cadastro FROM pastoral_dizimo WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($nome_pesquisado)) {
        $sql .= " AND nome LIKE ?";
        $params[] = "%" . $nome_pesquisado . "%";
        $types .= 's';
    }
    
    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <title>Pastoral do Dízimo - Consulta</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .result-item .action-buttons {
            margin-top: 10px;
        }
        /* Estilos para a nova tabela */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .history-table th, .history-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        .history-table th {
            background-color: #f2f2f2;
        }
        .history-table .actions-cell {
            white-space: nowrap; /* Impede a quebra de linha do botão */
        }
        .update-devolucao-button {
            padding: 5px 10px;
            font-size: 14px;
        }
    </style>
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
            <h2>Campos de Pesquisa</h2>
            <form action="consultar.php" method="get" class="form-container">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($nome_pesquisado); ?>">
                </div>
                <div class="form-group">
                    <label for="mes">Mês de Devolução:</label>
                    <select id="mes" name="mes">
                        <option value="">Todos os Meses</option>
                        <option value="1" <?php if(isset($_GET['mes']) && $_GET['mes'] == '1') echo 'selected'; ?>>Janeiro</option>
                        <option value="2" <?php if(isset($_GET['mes']) && $_GET['mes'] == '2') echo 'selected'; ?>>Fevereiro</option>
                        <option value="3" <?php if(isset($_GET['mes']) && $_GET['mes'] == '3') echo 'selected'; ?>>Março</option>
                        <option value="4" <?php if(isset($_GET['mes']) && $_GET['mes'] == '4') echo 'selected'; ?>>Abril</option>
                        <option value="5" <?php if(isset($_GET['mes']) && $_GET['mes'] == '5') echo 'selected'; ?>>Maio</option>
                        <option value="6" <?php if(isset($_GET['mes']) && $_GET['mes'] == '6') echo 'selected'; ?>>Junho</option>
                        <option value="7" <?php if(isset($_GET['mes']) && $_GET['mes'] == '7') echo 'selected'; ?>>Julho</option>
                        <option value="8" <?php if(isset($_GET['mes']) && $_GET['mes'] == '8') echo 'selected'; ?>>Agosto</option>
                        <option value="9" <?php if(isset($_GET['mes']) && $_GET['mes'] == '9') echo 'selected'; ?>>Setembro</option>
                        <option value="10" <?php if(isset($_GET['mes']) && $_GET['mes'] == '10') echo 'selected'; ?>>Outubro</option>
                        <option value="11" <?php if(isset($_GET['mes']) && $_GET['mes'] == '11') echo 'selected'; ?>>Novembro</option>
                        <option value="12" <?php if(isset($_GET['mes']) && $_GET['mes'] == '12') echo 'selected'; ?>>Dezembro</option>
                    </select>
                </div>
                <button type="submit" class="submit-button">Pesquisar</button>
            </form>
        </section>

        <?php if (!empty($resultados)): ?>
        <section class="card result-section">
            <h2>Resultados da Pesquisa</h2>
            <?php foreach ($resultados as $dizimista): ?>
            <div class="result-item">
                <p><strong>Nome:</strong> <?php echo htmlspecialchars($dizimista['nome']); ?></p>
                <p><strong>Endereço:</strong> <?php echo htmlspecialchars($dizimista['endereco']); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($dizimista['telefone']); ?></p>
                <p><strong>Paróquia:</strong> <?php echo htmlspecialchars($dizimista['paroquia']); ?></p>
                <p><strong>Data de Nascimento:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($dizimista['data_nascimento']))); ?></p>
                <p><strong>Data de Cadastro:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($dizimista['data_cadastro']))); ?></p>
                
                <div class="action-buttons">
                    <a href="atualizar_cadastro.php?id=<?php echo $dizimista['id']; ?>" class="button update-button">Atualizar Cadastro</a>
                </div>

                <hr>

                <h3>Histórico de Devoluções</h3>
                <?php
                $dizimista_id = $dizimista['id'];
                $sql_history = "SELECT id, valor_devolvido, data_devolucao, forma_devolucao, mes_referencia FROM historico_devolucoes_dizimo WHERE dizimista_id = ?";

                if (!empty($mes_pesquisado)) {
                    $sql_history .= " AND MONTH(data_devolucao) = ?";
                }

                $sql_history .= " ORDER BY FIELD(mes_referencia, 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro')";

                $stmt_history = $conn->prepare($sql_history);

                if (!empty($mes_pesquisado)) {
                    $mes_int = (int)$mes_pesquisado;
                    $stmt_history->bind_param("ii", $dizimista_id, $mes_int);
                } else {
                    $stmt_history->bind_param("i", $dizimista_id);
                }

                $stmt_history->execute();
                $history_result = $stmt_history->get_result();

                if ($history_result->num_rows > 0) {
                    echo "<table class='history-table'>";
                    echo "<thead><tr><th>Mês Referente</th><th>Valor</th><th>Data</th><th>Forma</th><th>Ações</th></tr></thead>";
                    echo "<tbody>";
                    while ($history_row = $history_result->fetch_assoc()) {
                        echo "<tr>";
						echo "<td>" . htmlspecialchars($history_row['mes_referencia']) . "</td>";
                        echo "<td>R$ " . number_format(htmlspecialchars($history_row['valor_devolvido']), 2, ',', '.') . "</td>";
                        echo "<td>" . htmlspecialchars(date('d/m/Y', strtotime($history_row['data_devolucao']))) . "</td>";
                        echo "<td>" . htmlspecialchars($history_row['forma_devolucao']) . "</td>";
                        echo "<td class='actions-cell'><a href='atualizar_devolucao.php?id=" . $history_row['id'] . "' class='button update-devolucao-button'>Editar Devolução</a></td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";
                    echo "</table>";
                } else {
                    echo "<p>Nenhuma devolução registrada.</p>";
                }
                $stmt_history->close();
                ?>

                <hr>

                <h3>Registrar Nova Devolução</h3>
                <form action="consultar.php" method="post" class="form-container">
                    <input type="hidden" name="id" value="<?php echo $dizimista['id']; ?>">
                    <div class="form-group">
                        <label for="devolucao_dizimo">Devolução do Dízimo:</label>
                        <select id="devolucao_dizimo" name="devolucao_dizimo" required>
                            <option value="Não">Não</option>
                            <option value="Sim">Sim</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="data_devolucao">Data da Devolução:</label>
                        <input type="date" id="data_devolucao" name="data_devolucao" value="<?php echo date("Y-m-d"); ?>">
                    </div>
                    <div class="form-group">
                        <label for="valor_devolvido">Valor do Dízimo Devolvido:</label>
						<div class="form-group">
    <label for="mes_referencia">Mês Referente:</label>
    <select id="mes_referencia" name="mes_referencia" required>
        <option value="">Selecione o Mês</option>
        <option value="Janeiro">Janeiro</option>
        <option value="Fevereiro">Fevereiro</option>
        <option value="Março">Março</option>
        <option value="Abril">Abril</option>
        <option value="Maio">Maio</option>
        <option value="Junho">Junho</option>
        <option value="Julho">Julho</option>
        <option value="Agosto">Agosto</option>
        <option value="Setembro">Setembro</option>
        <option value="Outubro">Outubro</option>
        <option value="Novembro">Novembro</option>
        <option value="Dezembro">Dezembro</option>
    </select>
</div>
                        <input type="number" step="0.01" id="valor_devolvido" name="valor_devolvido">
                    </div>
                    <div class="form-group">
                        <label for="forma_devolucao">Forma de Devolução:</label>
                        <select id="forma_devolucao" name="forma_devolucao" required>
                            <option value="">Selecione</option>
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Pix">Pix</option>
                            <option value="Cartão">Cartão</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-button">Registrar Devolução</button>
                </form>
            </div>
            <?php endforeach; ?>
        </section>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['nome']) || isset($_GET['mes']))): ?>
        <section class="card">
            <h2>Nenhum resultado encontrado.</h2>
        </section>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Pastoral do Dízimo. Todos os direitos reservados.</p>
    </footer>
	<script src="js/script.js"></script>
</body>
</html>