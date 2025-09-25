<?php
// Inicia a sessão para armazenar as informações do usuário
session_start();

// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, username, password FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verifica se a senha fornecida corresponde ao hash no banco de dados
        if (password_verify($password, $user['password'])) {
            // Senha correta, inicia a sessão
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            
            // Redireciona para a página principal
            header("Location: index.php");
            exit;
        } else {
            // Senha incorreta
            header("Location: login.php?erro=1");
            exit;
        }
    } else {
        // Usuário não encontrado
        header("Location: login.php?erro=1");
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>