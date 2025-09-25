<?php
// Configurações de conexão com o banco de dados
$servername = "localhost";
$username = "root"; // Nome de usuário padrão do XAMPP
$password = ""; // Senha padrão do XAMPP (vazia)
$dbname = "dizimistas";

// Cria a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("A conexão com o banco de dados falhou: " . $conn->connect_error);
}
?>