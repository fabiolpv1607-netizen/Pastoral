<?php
// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php'; 

// Verifica se os dados do formulário foram enviados via método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta os dados do formulário
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $paroquia = $_POST['paroquia'];
    $dataNascimento = $_POST['dataNascimento'];
    $dataCadastro = $_POST['dataCadastro'];

    // Prepara e executa a instrução SQL para inserir os dados
    $sql = "INSERT INTO pastoral_dizimo (nome, endereco, telefone, paroquia, data_nascimento, data_cadastro) VALUES (?, ?, ?, ?, ?, ?)";
    
    // Usa prepared statements para prevenir SQL Injection
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nome, $endereco, $telefone, $paroquia, $dataNascimento, $dataCadastro);

    if ($stmt->execute()) {
        header("Location: cadastro_sucesso.php");
    } else {
        header("Location: cadastrar.php");
    }

    $stmt->close();
}

$conn->close();
?>