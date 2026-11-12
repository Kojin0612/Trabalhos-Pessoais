<?php
// Sessão para token CSRF, mensagens e recuperação de dados.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

function escapar($texto): string {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function redirecionar(string $pagina): void {
    header('Location: ' . $pagina);
    exit;
}

function validarToken(): void {
    $token = $_POST['token'] ?? '';
    $tokenSessao = $_SESSION['token'] ?? '';

    if (!is_string($token) || $tokenSessao === '' || !hash_equals($tokenSessao, $token)) {
        http_response_code(403);
        exit('Formulário inválido ou expirado. Volte ao cadastro e tente novamente.');
    }
}

function validarId($valor) {
    if (!is_string($valor) && !is_int($valor)) {
        return false;
    }

    return filter_var($valor, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 4294967295]
    ]);
}

function buscarProduto(PDO $pdo, int $id): array {
    $consulta = $pdo->prepare('SELECT * FROM livros WHERE id = :id');
    $consulta->execute(['id' => $id]);
    $livro = $consulta->fetch();

    if (!$livro) {
        http_response_code(404);
        exit('Livro não encontrado. Volte ao catálogo.');
    }

    return $livro;
}
