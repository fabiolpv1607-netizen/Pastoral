<?php
session_start();
require_once 'conexao.php';

// Verifique se o usuário está logado e se tem um caixa aberto
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['caixa_id'])) {
    header("Location: index.php");
    exit;
}

$caixa_id = $_SESSION['caixa_id'];
$mensagem = "";
$tipo_mensagem = "";

// Verifica se há uma mensagem de aviso vinda da página de logout.
if (isset($_SESSION['aviso'])) {
    $mensagem = $_SESSION['aviso'];
    $tipo_mensagem = 'aviso';
    unset($_SESSION['aviso']);
}

// Lógica para fechar o caixa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] == 'fechar') {
    // ALTERADO: Captura os valores para cada forma de pagamento
    $saldo_final_dinheiro = filter_input(INPUT_POST, 'saldo_final_dinheiro', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? 0;
    $saldo_final_cartao = filter_input(INPUT_POST, 'saldo_final_cartao', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? 0;
    $saldo_final_pix = filter_input(INPUT_POST, 'saldo_final_pix', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? 0;

    // Calcula o saldo final total
    $saldo_final_total = $saldo_final_dinheiro + $saldo_final_cartao + $saldo_final_pix;

    // ALTERADO: Atualiza o banco de dados com os novos campos
    $sql = "UPDATE caixas 
            SET data_fechamento = NOW(), 
                saldo_final = ?, 
                saldo_final_dinheiro = ?, 
                saldo_final_cartao = ?, 
                saldo_final_pix = ?, 
                status = 'fechado' 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    // ALTERADO: Adiciona os novos parâmetros no bind_param. 'ddddi' = double, double, double, double, integer
    $stmt->bind_param("ddddi", $saldo_final_total, $saldo_final_dinheiro, $saldo_final_cartao, $saldo_final_pix, $caixa_id);
    
    if ($stmt->execute()) {
    unset($_SESSION['caixa_id']);
    $_SESSION['mensagem_sucesso'] = "Conferência fechada com sucesso!";
    header("Location: index.php");
    exit;

    } else {
        $mensagem = "Erro ao fechar a Conferência: " . $stmt->error;
        $tipo_mensagem = 'erro';
    }
    $stmt->close();
}

// Lógica para obter o saldo inicial
$sql_caixa = "SELECT saldo_inicial, data_abertura FROM caixas WHERE id = ?";
$stmt_caixa = $conn->prepare($sql_caixa);
$stmt_caixa->bind_param("i", $caixa_id);
$stmt_caixa->execute();
$caixa = $stmt_caixa->get_result()->fetch_assoc();
$stmt_caixa->close();

// ALTERADO: Lógica para obter os totais recebidos por forma de pagamento
$sql_devolucoes = "SELECT 
                        SUM(CASE WHEN forma_devolucao = 'dinheiro' THEN valor_devolvido ELSE 0 END) AS total_dinheiro,
                        SUM(CASE WHEN forma_devolucao = 'cartao' THEN valor_devolvido ELSE 0 END) AS total_cartao,
                        SUM(CASE WHEN forma_devolucao = 'pix' THEN valor_devolvido ELSE 0 END) AS total_pix
                   FROM historico_devolucoes_dizimo WHERE caixa_id = ?";
$stmt_devolucoes = $conn->prepare($sql_devolucoes);
$stmt_devolucoes->bind_param("i", $caixa_id);
$stmt_devolucoes->execute();
$totais_recebidos = $stmt_devolucoes->get_result()->fetch_assoc();
$stmt_devolucoes->close();

// Calcula os saldos esperados. Assumimos que o saldo inicial é sempre em dinheiro.
$saldo_esperado_dinheiro = $caixa['saldo_inicial'] + ($totais_recebidos['total_dinheiro'] ?? 0);
$saldo_esperado_cartao = $totais_recebidos['total_cartao'] ?? 0;
$saldo_esperado_pix = $totais_recebidos['total_pix'] ?? 0;
$saldo_esperado_total = $saldo_esperado_dinheiro + $saldo_esperado_cartao + $saldo_esperado_pix;

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fechar Conferência - Pastoral do Dízimo</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .mensagem { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; font-weight: bold; }
        .mensagem.aviso { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .mensagem.erro { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .total-calculado { font-size: 1.2em; font-weight: bold; margin-top: 10px; }
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
            <h2>Resumo da Conferência</h2>
            <?php if (!empty($mensagem)): ?>
                <div class="mensagem <?php echo $tipo_mensagem; ?>">
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>

            <div class="report-list">
                <p><strong>Usuário:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p><strong>Aberto em:</strong> <?php echo date('d/m/Y H:i', strtotime($caixa['data_abertura'])); ?></p>
                <hr>
                <p><strong>Saldo Inicial (Dinheiro):</strong> R$ <?php echo number_format($caixa['saldo_inicial'], 2, ',', '.'); ?></p>
                <p><strong>Recebido em Dinheiro:</strong> R$ <?php echo number_format($totais_recebidos['total_dinheiro'] ?? 0, 2, ',', '.'); ?></p>
                <p><strong>Recebido em Cartão:</strong> R$ <?php echo number_format($totais_recebidos['total_cartao'] ?? 0, 2, ',', '.'); ?></p>
                <p><strong>Recebido em PIX:</strong> R$ <?php echo number_format($totais_recebidos['total_pix'] ?? 0, 2, ',', '.'); ?></p>
                <hr>
                <p><strong>Saldo Esperado (Dinheiro):</strong> R$ <?php echo number_format($saldo_esperado_dinheiro, 2, ',', '.'); ?></p>
                <p><strong>Saldo Esperado (Cartão):</strong> R$ <?php echo number_format($saldo_esperado_cartao, 2, ',', '.'); ?></p>
                <p><strong>Saldo Esperado (PIX):</strong> R$ <?php echo number_format($saldo_esperado_pix, 2, ',', '.'); ?></p>
                <p><strong>SALDO TOTAL ESPERADO:</strong> R$ <?php echo number_format($saldo_esperado_total, 2, ',', '.'); ?></p>
            </div>

            <form action="fechar_caixa.php" method="post" class="form-container">
                <div class="form-group">
                    <label for="saldo_final_dinheiro">Valor Final Contado em Dinheiro (R$):</label>
                    <input type="number" step="0.01" id="saldo_final_dinheiro" name="saldo_final_dinheiro" required value="0.00" class="saldo-input">
                </div>
                <div class="form-group">
                    <label for="saldo_final_cartao">Valor Final em Cartão (R$):</label>
                    <input type="number" step="0.01" id="saldo_final_cartao" name="saldo_final_cartao" required value="0.00" class="saldo-input">
                </div>
                <div class="form-group">
                    <label for="saldo_final_pix">Valor Final em PIX (R$):</label>
                    <input type="number" step="0.01" id="saldo_final_pix" name="saldo_final_pix" required value="0.00" class="saldo-input">
                </div>

                <div class="total-calculado" id="total-display">
                    Total Informado: R$ 0,00
                </div>

                <input type="hidden" name="acao" value="fechar">
                <button type="submit" class="submit-button">Fechar Conferência</button>
            </form>
        </section>
    </main>
    <footer class="footer">
        </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.saldo-input');
            const totalDisplay = document.getElementById('total-display');

            function calcularTotal() {
                let total = 0;
                inputs.forEach(input => {
                    const valor = parseFloat(input.value) || 0;
                    total += valor;
                });
                totalDisplay.textContent = `Total Informado: R$ ${total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }

            inputs.forEach(input => {
                input.addEventListener('input', calcularTotal);
            });
        });
    </script>
	<script src="js/script.js"></script>
</body>
</html>