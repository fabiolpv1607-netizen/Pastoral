<?php
session_start();
require_once 'conexao.php';

// Verificação de Acesso: Redireciona se já estiver logado
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (!isset($_SESSION['caixa_id'])) {
        header("Location: abrir_caixa.php");
        exit;
    }
    header("Location: index.php");
    exit;
}

$erro_mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // A consulta agora seleciona o 'role' do usuário (ESTA É A CORREÇÃO)
    $sql = "SELECT id, username, password, role FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Armazena o 'role' na sessão
                $_SESSION['usuario_id'] = $user['id']; // Armazena o ID do usuário

                // Verifica se o usuário tem um caixa aberto
                $sql_caixa = "SELECT id FROM caixas WHERE usuario_id = ? AND status = 'aberto'";
                $stmt_caixa = $conn->prepare($sql_caixa);
                $stmt_caixa->bind_param("i", $_SESSION['usuario_id']);
                $stmt_caixa->execute();
                $result_caixa = $stmt_caixa->get_result();

                if ($result_caixa->num_rows > 0) {
                    $caixa = $result_caixa->fetch_assoc();
                    $_SESSION['caixa_id'] = $caixa['id'];
                    header("Location: index.php");
                } else {
                    // Se não tiver, redireciona para a página de abertura de caixa
                    unset($_SESSION['caixa_id']);
                    header("Location: abrir_caixa.php");
                }
                exit;
            } else {
                $erro_mensagem = "Nome de usuário ou senha incorretos.";
            }
        } else {
            $erro_mensagem = "Nome de usuário ou senha incorretos.";
        }
        $stmt->close();
    } else {
        $erro_mensagem = "Erro interno do servidor.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pastoral do Dízimo</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .login-container {
            max-width: 300px;
            width: 100%;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            background-color: #fff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Acesso Restrito</h2>
        <?php
        if (isset($_GET['acesso'])) {
            $erro_mensagem = "Por favor, faça login para acessar.";
        }
        if (!empty($erro_mensagem)) {
            echo '<p style="color:red; text-align: center;">' . htmlspecialchars($erro_mensagem) . '</p>';
        }
        ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Usuário:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="submit-button">Entrar</button>
        </form>
    </div>
</body>
</html>