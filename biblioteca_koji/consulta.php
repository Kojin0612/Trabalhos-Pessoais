<?php
require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/conexao.php';

$consulta = $pdo->query('SELECT * FROM livros ORDER BY id DESC');
$livros = $consulta->fetchAll();
$totalLivros = count($livros);
$totalExemplares = array_sum(array_map(static fn($livro) => (int) $livro['quantity'], $livros));
$totalGeneros = count(array_unique(array_column($livros, 'genero')));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Catálogo - Biblioteca Koji</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <header class="topbar">
    <a class="brand" href="index.php" aria-label="Biblioteca Koji"><span class="brand-icon">📙</span><span>Koji</span></a>
    <form class="search" id="searchForm">
      <label class="sr-only" for="searchInput">Buscar livros</label>
      <span class="search-icon">⌕</span>
      <input id="searchInput" type="search" placeholder="Buscar na biblioteca">
      <button type="submit" aria-label="Buscar">⌕</button>
    </form>
    <nav class="top-actions" aria-label="Atalhos"><a href="#catalogo" title="Catálogo">▤</a></nav>
  </header>

  <nav class="main-nav" aria-label="Navegação principal">
    <a href="index.php">INÍCIO</a>
    <a href="consulta.php">CATÁLOGO</a>
    <a href="cadastro.php">CADASTRAR LIVRO</a>
  </nav>

  <main>
    <section class="stats" aria-label="Resumo do catálogo">
      <article class="stat-card"><span class="stat-icon">▤</span><div><strong><?= $totalLivros ?></strong><span>Títulos cadastrados</span></div></article>
      <article class="stat-card"><span class="stat-icon">♧</span><div><strong><?= $totalExemplares ?></strong><span>Exemplares no acervo</span></div></article>
      <article class="stat-card"><span class="stat-icon">⌕</span><div><strong><?= $totalGeneros ?></strong><span>Categorias diferentes</span></div></article>
    </section>

    <section class="panel catalog-panel" id="catalogo">
      <div class="catalog-header">
        <div><span class="section-kicker">SEU ACERVO</span><h2>Livros cadastrados</h2><p class="muted">Consulte e gerencie os títulos da biblioteca.</p></div>
        <span class="catalog-count"><?= $totalLivros ?> <?= $totalLivros === 1 ? 'livro' : 'livros' ?></span>
      </div>
      <?php if (!empty($_SESSION['mensagem'])): ?>
        <p class="form-message" role="status"><?= escapar($_SESSION['mensagem']) ?></p>
        <?php unset($_SESSION['mensagem']); ?>
      <?php endif; ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Livro</th><th>Autor(a)</th><th>Ano</th><th>Gênero</th><th>ISBN</th><th>Exemplares</th><th>Ação</th></tr></thead>
          <tbody id="bookTableBody">
            <?php if (!$livros): ?>
              <tr class="empty-row"><td colspan="7">Nenhum livro cadastrado ainda. Use o formulário para adicionar o primeiro!</td></tr>
            <?php else: ?>
              <?php foreach ($livros as $livro): ?>
                <tr>
                  <td><?= escapar($livro['title']) ?></td>
                  <td><?= escapar($livro['author']) ?></td>
                  <td><?= escapar($livro['year_publication']) ?></td>
                  <td><span class="category-tag"><?= escapar($livro['genero']) ?></span></td>
                  <td><?= escapar($livro['isbn'] ?? '—') ?></td>
                  <td><?= (int) $livro['quantity'] ?></td>
                  <td>
                    <a href="cadastro.php?id=<?= (int) $livro['id'] ?>">Editar</a>
                    <form action="excluir.php" method="POST" style="display:inline" onsubmit="return confirm('Tem certeza de que deseja excluir este livro do catalogo? Esta acao nao pode ser desfeita.');">
                      <input type="hidden" name="token" value="<?= escapar($_SESSION['token']) ?>">
                      <input type="hidden" name="id" value="<?= (int) $livro['id'] ?>">
                      
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
  <footer><a class="footer-brand" href="index.php">📙 Koji</a><p>Biblioteca Koji · Sistema de cadastro de livros</p><a href="#inicio">Voltar ao topo ↑</a></footer>
  <script src="script.js"></script>
</body>
</html>
