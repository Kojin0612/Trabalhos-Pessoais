<?php
require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/conexao.php';

// Sem ID: formulário novo. Com ID: formulário de edição.
$produto = ['id' => '', 'title' => '', 'author' => '', 'year_publication' => '', 'isbn' => '', 'genero' => '', 'quantity' => '0'];
$editando = isset($_GET['id']);

if ($editando) {
    $id = validarId($_GET['id']);
    if ($id === false) {
        http_response_code(400);
        exit('ID inválido. Volte à lista.');
    }
    $produto = buscarProduto($pdo, $id);
}

$erros = $_SESSION['erros'] ?? [];
$dados = $_SESSION['dados'] ?? null;
unset($_SESSION['erros'], $_SESSION['dados']);

// Após um erro, recupera os valores que a pessoa digitou.
if ($dados !== null && $dados['id'] === (string) $produto['id']) {
    $produto = $dados;
}

$titulo = $editando ? 'Editar produto' : 'Cadastrar produto';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= escapar($titulo) ?></title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>

  <header class="topbar">
    <a class="brand" href="#" aria-label="Biblioteca Koji">
      <span class="brand-icon">📙</span>
      <span>Koji</span>
    </a>

    <form class="search" id="searchForm">
      <label class="sr-only" for="searchInput">Buscar livros</label>
      <span class="search-icon">⌕</span>
      <input id="searchInput" type="search" placeholder="Buscar na biblioteca">
      <button type="submit" aria-label="Buscar">⌕</button>
    </form>

    <nav class="top-actions" aria-label="Atalhos">
      <a href="#catalogo" title="Catálogo">▤</a>
    </nav>
  </header>

  
  
   <nav class="main-nav" aria-label="Navegação principal">
     <a href="index.php">INÍCIO</a>
     <a href="consulta.php">CATÁLOGO</a>
     <a href="cadastro.php">CADASTRAR LIVRO</a>
   </nav>
  

  <main>

    <section class="stats" aria-label="Resumo do catálogo">
      <article class="stat-card">
        <span class="stat-icon">▤</span>
        <div><strong id="totalBooks">0</strong><span>Títulos cadastrados</span></div>
      </article>
      <article class="stat-card">
        <span class="stat-icon">♧</span>
        <div><strong id="totalCopies">0</strong><span>Exemplares no acervo</span></div>
      </article>
      <article class="stat-card">
        <span class="stat-icon">⌕</span>
        <div><strong id="totalCategories">0</strong><span>Categorias diferentes</span></div>
      </article>
    </section>

    <section class="content-grid">
      <section class="panel form-panel" id="cadastro">
        <div class="section-heading">
          <div>
            <span class="section-kicker">GERENCIAMENTO</span>
            <h2><?= escapar($titulo) ?></h2>
          </div>
          <span class="heading-icon">＋</span>
        </div>
        <p class="muted">Preencha os dados abaixo para adicionar um livro ao acervo.</p>

        <?php if (!empty($erros)): ?>
          <div class="form-message" role="alert">
            <?php foreach ($erros as $erro): ?>
              <p><?= escapar($erro) ?></p>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['mensagem'])): ?>
          <p class="form-message" role="status"><?= escapar($_SESSION['mensagem']) ?></p>
          <?php unset($_SESSION['mensagem']); ?>
        <?php endif; ?>

        <form id="bookForm" action="salvar.php" method="POST">
          <input type="hidden" name="id" value="<?= escapar($produto['id']) ?>">
          <input type="hidden" name="token" value="<?= escapar($_SESSION['token']) ?>">
          <div class="form-row">
            <div class="field field-wide">
              <label for="title">Título do livro *</label>
              <input id="title" name="title" type="text" placeholder="Ex.: O Pequeno Príncipe" required value="<?= escapar($produto['title']) ?>">
            </div>
            <div class="field">
              <label for="author">Autor(a) *</label>
              <input id="author" name="author" type="text" placeholder="Nome do autor" required value="<?= escapar($produto['author']) ?>">
            </div>
            <div class="field">
              <label for="year_publication">Ano de publicação *</label>
              <input id="year_publication" name="year_publication" type="number" min="1" max="<?= (int) date('Y') ?>" placeholder="Ex.: 1943" required value="<?= escapar($produto['year_publication']) ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="field">
              <label for="isbn">ISBN</label>
              <input id="isbn" name="isbn" type="text" placeholder="Ex.: 978-85-00-00000-0" value="<?= escapar($produto['isbn']) ?>">
            </div>
            <div class="field">
              <label for="genero">Gênero *</label>
              <?php
$generos = [
    'Literatura',
    'Fantasia',
    'Ficção científica',
    'Romance',
    'História',
    'Educação',
    'Tecnologia',
    'Infantil',
    'Outros'
];
?>

<select id="genero" name="genero" required>
    <option value="">Selecione uma categoria</option>

    <?php foreach ($generos as $genero): ?>
        <option
            value="<?= escapar($genero) ?>"
            <?= ($produto['genero'] ?? '') === $genero ? 'selected' : '' ?>
        >
            <?= escapar($genero) ?>
        </option>
    <?php endforeach; ?>
</select>
            </div>
            <div class="field field-small">
              <label for="quantity">Exemplares *</label>
              <input id="quantity" name="quantity" type="number" min="0" required value="<?= escapar($produto['quantity']) ?>">
            </div>
          </div>
          <div class="form-actions">
            <button class="btn-neon" type="reset">Limpar</button>
            <button class="btn-gradiente" type="submit">Salvar produto</button>
          </div>
          <p class="form-message" id="formMessage" role="status"></p>
        </form>
        <?php if ($editando): ?>
          <form action="excluir.php" method="POST" class="form-actions" onsubmit="return confirm('Tem certeza de que deseja excluir este livro do catalogo? Esta acao nao pode ser desfeita.');">
            <input type="hidden" name="token" value="<?= escapar($_SESSION['token']) ?>">
            <input type="hidden" name="id" value="<?= (int) $produto['id'] ?>">
            <button type="submit" class="btn-neon">Excluir livro</button>
          </form>
        <?php endif; ?>
      </section>

   
    
  </main>

<footer>
    <a class="footer-brand" href="#inicio">📙 Koji</a>
    <p>Biblioteca Koji · Sistema de cadastro de livros</p>
    <a href="#inicio">Voltar ao topo ↑</a>
  </footer>

  <script src="script.js"></script>
</body>
</html>