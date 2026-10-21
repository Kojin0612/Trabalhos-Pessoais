<?php
$host = '127.0.0.1';
$porta = '3306';
$banco = 'biblioteca';
$usuario = 'root';
$senha = ''; // Padrão comum do XAMPP; altere se seu MySQL tiver senha.

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$porta};dbname={$banco};charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco. Confira o MySQL e os dados de conexao.php.');
}
