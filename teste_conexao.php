<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dizimistas";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("A conexão com o banco de dados falhou: " . $conn->connect_error);
}

echo "Conexão com o banco de dados 'dizimistas' bem-sucedida!";
$conn->close();
?>