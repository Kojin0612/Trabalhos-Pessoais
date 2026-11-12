<?php
require_once __DIR__ . '/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use o formulário para salvar um livro.');
}

validarToken();
require_once __DIR__ . '/conexao.php';

$campos = ['id', 'title', 'author', 'year_publication', 'isbn', 'genero', 'quantity'];
$dados = [];

foreach ($campos as $campo) {
    $valor = $_POST[$campo] ?? '';
    if (!is_string($valor)) {
        http_response_code(400);
        exit('Dados inválidos.');
    }
    $dados[$campo] = trim($valor);
}

$id = $dados['id'] === '' ? null : validarId($dados['id']);
if ($id === false) {
    http_response_code(400);
    exit('ID inválido.');
}

$livroAntes = null;
if ($id !== null) {
    $livroAntes = buscarProduto($pdo, $id);
}

$erros = [];

if ($dados['title'] === '' || mb_strlen($dados['title'], 'UTF-8') > 150) {
    $erros[] = 'O título é obrigatório e deve ter até 150 caracteres.';
}

if ($dados['author'] === '' || mb_strlen($dados['author'], 'UTF-8') > 100) {
    $erros[] = 'O autor é obrigatório e deve ter até 100 caracteres.';
}

$anoAtual = (int) date('Y');
if (
    !preg_match('/^\d{4}$/', $dados['year_publication']) ||
    (int) $dados['year_publication'] < 1 ||
    (int) $dados['year_publication'] > $anoAtual
) {
    $erros[] = 'Informe um ano de publicação válido.';
}

if (mb_strlen($dados['isbn'], 'UTF-8') > 20) {
    $erros[] = 'O ISBN deve ter até 20 caracteres.';
}

$generosPermitidos = [
    'Literatura', 'Fantasia', 'Ficção científica', 'Romance',
    'História', 'Educação', 'Tecnologia', 'Infantil', 'Outros'
];
if (!in_array($dados['genero'], $generosPermitidos, true)) {
    $erros[] = 'Selecione um gênero válido.';
}

$quantidade = filter_var($dados['quantity'], FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 0, 'max_range' => 1000000]
]);
if ($quantidade === false) {
    $erros[] = 'Os exemplares devem ser um número inteiro entre 0 e 1000000.';
}

if ($erros) {
    $_SESSION['erros'] = $erros;
    $_SESSION['dados'] = $dados;
    redirecionar($id === null ? 'cadastro.php' : 'cadastro.php?id=' . $id);
}

try {
    $parametros = [
        'title' => $dados['title'],
        'author' => $dados['author'],
        'year_publication' => (int) $dados['year_publication'],
        'isbn' => $dados['isbn'] === '' ? null : $dados['isbn'],
        'genero' => $dados['genero'],
        'quantity' => $quantidade
    ];

    if ($id === null) {
        $sql = 'INSERT INTO livros (title, author, year_publication, isbn, genero, quantity)
                VALUES (:title, :author, :year_publication, :isbn, :genero, :quantity)';
        $mensagem = 'Livro cadastrado com sucesso!';
    } else {
        $sql = 'UPDATE livros SET title = :title, author = :author,
                year_publication = :year_publication, isbn = :isbn,
                genero = :genero, quantity = :quantity
                WHERE id = :id';
        $parametros['id'] = $id;
        $mensagem = 'Livro atualizado com sucesso!';
    }

    // Salva o livro e registra a movimentação no histórico.
    $pdo->beginTransaction();

    $comando = $pdo->prepare($sql);
    $comando->execute($parametros);

    $livroId = $id ?? (int) $pdo->lastInsertId();
    $quantidadeAnterior = $livroAntes === null ? 0 : (int) $livroAntes['quantity'];
    $quantidadeAtual = $quantidade;
    $diferenca = $quantidadeAtual - $quantidadeAnterior;

    if ($id === null) {
        $tipoMovimento = 'cadastro';
        $quantidadeMovimentada = $quantidadeAtual;
    } elseif ($diferenca > 0) {
        $tipoMovimento = 'entrada';
        $quantidadeMovimentada = $diferenca;
    } elseif ($diferenca < 0) {
        $tipoMovimento = 'saida';
        $quantidadeMovimentada = abs($diferenca);
    } else {
        $tipoMovimento = 'ajuste';
        $quantidadeMovimentada = 0;
    }

    $historico = $pdo->prepare(
        'INSERT INTO movimentos_estoque
         (livro_id, tipo, quantidade, quantidade_anterior, quantidade_atual)
         VALUES (:livro_id, :tipo, :quantidade, :anterior, :atual)'
    );
    $historico->execute([
        'livro_id' => $livroId,
        'tipo' => $tipoMovimento,
        'quantidade' => $quantidadeMovimentada,
        'anterior' => $quantidadeAnterior,
        'atual' => $quantidadeAtual
    ]);

    $pdo->commit();
} catch (PDOException $erro) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($erro->getMessage());
    http_response_code(500);
    exit('Não foi possível salvar o livro. Confira a estrutura da tabela livros e os logs do Apache.');
}

unset($_SESSION['erros'], $_SESSION['dados']);
$_SESSION['mensagem'] = $mensagem;
redirecionar('consulta.php');
