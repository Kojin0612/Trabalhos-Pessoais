<?php
require_once __DIR__ . '/funcoes.php';

// A exclusao so pode ser solicitada por formulario POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use o botao de exclusao no cadastro do livro.');
}

validarToken();
require_once __DIR__ . '/conexao.php';

$id = validarId($_POST['id'] ?? '');
if ($id === false) {
    http_response_code(400);
    exit('ID do livro invalido.');
}

try {
    // Confirma que o livro existe antes de exclui-lo.
    $consulta = $pdo->prepare('SELECT id FROM livros WHERE id = :id');
    $consulta->execute(['id' => $id]);
    if (!$consulta->fetch()) {
        http_response_code(404);
        exit('Livro nao encontrado no catalogo.');
    }

    // O historico de estoque e preservado; a chave estrangeira define livro_id como NULL.
    $excluir = $pdo->prepare('DELETE FROM livros WHERE id = :id');
    $excluir->execute(['id' => $id]);

    $_SESSION['mensagem'] = 'Livro excluido do catalogo com sucesso.';
    redirecionar('consulta.php');
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    http_response_code(500);
    exit('Nao foi possivel excluir o livro. Confira o banco de dados e tente novamente.');
}
