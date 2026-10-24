<?php
require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/conexao.php';

// Indicadores gerais do acervo.
$totais = $pdo->query(
    'SELECT COUNT(*) AS titulos,
            COALESCE(SUM(quantity), 0) AS exemplares,
            COUNT(DISTINCT genero) AS generos
     FROM livros'
)->fetch();

$estoqueBaixo = $pdo->query(
    'SELECT id, title, author, quantity
     FROM livros
     WHERE quantity BETWEEN 1 AND 10
     ORDER BY quantity ASC, title ASC
     LIMIT 8'
)->fetchAll();

$esgotados = $pdo->query(
    'SELECT id, title, author, quantity
     FROM livros
     WHERE quantity = 0
     ORDER BY title ASC
     LIMIT 8'
)->fetchAll();

// A tabela de histórico é criada no banco.sql / atualizar_historico.sql.
$movimentacoes = [];
try {
    $movimentacoes = $pdo->query(
        'SELECT m.tipo, m.quantidade, m.quantidade_anterior,
                m.quantidade_atual, m.data_movimentacao, l.title
         FROM movimentos_estoque m
         LEFT JOIN livros l ON l.id = m.livro_id
         ORDER BY m.data_movimentacao DESC, m.id DESC
         LIMIT 10'
    )->fetchAll();
} catch (PDOException $erro) {
    // A página continua funcionando se a tabela de histórico ainda não foi criada.
    error_log($erro->getMessage());
}

function formatarDataMovimentacao(string $data): string {
    $timestamp = strtotime($data);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : $data;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Painel inicial - Biblioteca Koji</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <header class="topbar">
    <a class="brand" href="index.php" aria-label="Biblioteca Koji">
      <span class="brand-icon">📙</span><span>Koji</span>
    </a>
    <form class="search" id="searchForm">
      <label class="sr-only" for="searchInput">Buscar livros</label>
      <span class="search-icon">⌕</span>
      <input id="searchInput" type="search" placeholder="Buscar no catálogo">
      <button type="submit" aria-label="Buscar">⌕</button>
    </form>
    <nav class="top-actions" aria-label="Atalhos"><a href="consulta.php" title="Catálogo">▤</a></nav>
  </header>

  <nav class="main-nav" aria-label="Navegação principal">
    <a href="index.php" aria-current="page">INÍCIO</a>
    <a href="consulta.php">CATÁLOGO</a>
    <a href="cadastro.php">CADASTRAR LIVRO</a>
  </nav>

  <main>
    <section class="dashboard-welcome">
      <div>
        <span class="section-kicker">PAINEL DE CONTROLE</span>
        <h1>Visão geral do estoque</h1>
        <p class="muted">Acompanhe seu acervo, confira o que precisa de reposição e veja as últimas movimentações.</p>
      </div>
      <a class="dashboard-primary-button" href="cadastro.php"><span>＋</span> Cadastrar livro</a>
    </section>

    <section class="stats dashboard-stats" aria-label="Resumo do estoque">
      <article class="stat-card">
        <span class="stat-icon">▤</span>
        <div><strong><?= (int) $totais['titulos'] ?></strong><span>Títulos cadastrados</span></div>
      </article>
      <article class="stat-card">
        <span class="stat-icon">♧</span>
        <div><strong><?= (int) $totais['exemplares'] ?></strong><span>Exemplares no estoque</span></div>
      </article>
      <article class="stat-card">
        <span class="stat-icon">!</span>
        <div><strong><?= count($estoqueBaixo) ?></strong><span>Livros com estoque baixo*</span></div>
      </article>
      <article class="stat-card">
        <span class="stat-icon">×</span>
        <div><strong><?= count($esgotados) ?></strong><span>Livros esgotados*</span></div>
      </article>
    </section>
    <p class="dashboard-note">* Os alertas exibem até 8 títulos por categoria. Estoque baixo significa de 1 a 10 exemplares.</p>

    <section class="dashboard-grid">
      <section class="panel dashboard-panel" id="alertas">
        <div class="dashboard-panel-heading">
          <div><span class="section-kicker">ATENÇÃO</span><h2>Alertas de estoque</h2></div>
          <span class="panel-heading-icon warning-icon">!</span>
        </div>

        <div class="alert-group">
          <div class="alert-group-title"><span class="status-dot status-low"></span><h3>Estoque baixo</h3><span class="mini-count"><?= count($estoqueBaixo) ?></span></div>
          <?php if (!$estoqueBaixo): ?>
            <p class="empty-state">Nenhum livro com estoque baixo no momento.</p>
          <?php else: ?>
            <div class="dashboard-list">
              <?php foreach ($estoqueBaixo as $livro): ?>
                <div class="inventory-row">
                  <div class="inventory-book"><strong><?= escapar($livro['title']) ?></strong><span><?= escapar($livro['author']) ?></span></div>
                  <span class="stock-pill stock-low"><?= (int) $livro['quantity'] ?> un.</span>
                  <a class="text-link" href="cadastro.php?id=<?= (int) $livro['id'] ?>">Ver</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="alert-group">
          <div class="alert-group-title"><span class="status-dot status-out"></span><h3>Sem estoque</h3><span class="mini-count"><?= count($esgotados) ?></span></div>
          <?php if (!$esgotados): ?>
            <p class="empty-state">Nenhum livro esgotado no momento.</p>
          <?php else: ?>
            <div class="dashboard-list">
              <?php foreach ($esgotados as $livro): ?>
                <div class="inventory-row">
                  <div class="inventory-book"><strong><?= escapar($livro['title']) ?></strong><span><?= escapar($livro['author']) ?></span></div>
                  <span class="stock-pill stock-out">0 un.</span>
                  <a class="text-link" href="cadastro.php?id=<?= (int) $livro['id'] ?>">Ver</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <a class="panel-footer-link" href="consulta.php">Consultar catálogo completo <span>→</span></a>
      </section>

      <section class="panel dashboard-panel" id="historico">
        <div class="dashboard-panel-heading">
          <div><span class="section-kicker">ACOMPANHAMENTO</span><h2>Movimentações recentes</h2><p class="muted">Histórico de alterações no estoque.</p></div>
          <span class="panel-heading-icon history-icon">↻</span>
        </div>
        <?php if (!$movimentacoes): ?>
          <div class="history-empty">
            <span class="history-empty-icon">↻</span>
            <strong>Nenhuma movimentação registrada</strong>
            <p>Quando um livro for cadastrado ou sua quantidade mudar, o histórico aparecerá aqui.</p>
          </div>
        <?php else: ?>
          <div class="history-list">
            <?php foreach ($movimentacoes as $mov): ?>
              <article class="history-item">
                <span class="history-type-icon <?= $mov['tipo'] === 'entrada' ? 'history-entry' : ($mov['tipo'] === 'saida' ? 'history-exit' : 'history-new') ?>">
                  <?= $mov['tipo'] === 'entrada' ? '＋' : ($mov['tipo'] === 'saida' ? '−' : '▤') ?>
                </span>
                <div class="history-description">
                  <strong><?= escapar($mov['title'] ?? 'Livro removido') ?></strong>
                  <span>
                    <?php if ($mov['tipo'] === 'cadastro'): ?>
                      Livro cadastrado com <?= (int) $mov['quantidade_atual'] ?> exemplar(es).
                    <?php elseif ($mov['tipo'] === 'entrada'): ?>
                      Entrada de <?= (int) $mov['quantidade'] ?> exemplar(es).
                    <?php elseif ($mov['tipo'] === 'saida'): ?>
                      Saída de <?= (int) $mov['quantidade'] ?> exemplar(es).
                    <?php else: ?>
                      Estoque ajustado de <?= (int) $mov['quantidade_anterior'] ?> para <?= (int) $mov['quantidade_atual'] ?>.
                    <?php endif; ?>
                  </span>
                  <time datetime="<?= escapar($mov['data_movimentacao']) ?>"><?= escapar(formatarDataMovimentacao($mov['data_movimentacao'])) ?></time>
                </div>
                <span class="history-balance"><?= (int) $mov['quantidade_atual'] ?> <small>un.</small></span>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <p class="history-footnote">O histórico registra cadastros e mudanças na quantidade de exemplares.</p>
      </section>
    </section>
  </main>

  <footer>
    <a class="footer-brand" href="index.php">📙 Koji</a>
    <p>Biblioteca Koji · Sistema de controle de estoque</p>
    <a href="#">Voltar ao topo ↑</a>
  </footer>
  <script src="script.js"></script>
</body>
</html>
