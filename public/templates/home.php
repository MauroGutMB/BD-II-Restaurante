<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';

$pdo = db_connect();

$stmtPedidos = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE status='aberto'");
$abertos = $stmtPedidos->fetchColumn();

$stmtCompras = $pdo->query("SELECT COUNT(*) FROM despesas");
$compras = $stmtCompras->fetchColumn();

$stmtProdutos = $pdo->query("SELECT IFNULL(SUM(estoque),0) FROM produtos");
$estoque = $stmtProdutos->fetchColumn();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restaurante DB</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php $active = '/'; require __DIR__ . '/partials/topbar.php'; ?>
    <main class="container">
        <section class="hero">
            <div class="hero-content">
                <p class="eyebrow">Painel principal</p>
                <h1>Controle total do fluxo do restaurante</h1>
                <p class="lead">Acompanhe compras, pedidos e status em um unico lugar. Use os atalhos abaixo para acessar os paineis.</p>
                <div class="button-row">
                    <a class="button" href="/pedido">Gerenciar pedidos</a>
                    <a class="button button-ghost" href="/compra">Gerenciar compras</a>
                </div>
            </div>
            <div class="hero-card">
                <h2>Resumo rapido</h2>
                <p class="lead">Indicadores do CRUD.</p>
                <ul class="stat-list">
                    <li class="stat-item"><span>Pedidos em aberto</span><strong><?= $abertos ?></strong></li>
                    <li class="stat-item"><span>Compras pendentes</span><strong><?= $compras ?></strong></li>
                    <li class="stat-item"><span>Itens em estoque</span><strong><?= $estoque ?></strong></li>
                </ul>
            </div>
        </section>

        <section class="card-grid">
            <article class="card">
                <h3>Gestao de pedidos</h3>
                <p class="lead">Organize o fluxo de mesas, entregas e status com rapidez.</p>
                <ul class="card-list">
                    <li>Editar status e prioridade</li>
                    <li>Revisar historico por data</li>
                    <li>Adicionar observacoes do cliente</li>
                </ul>
                <div class="card-actions">
                    <a class="text-link" href="/pedido">Abrir painel</a>
                </div>
            </article>
            <article class="card">
                <h3>Gestao de compras</h3>
                <p class="lead">Controle fornecedores, categorias e valores do abastecimento.</p>
                <ul class="card-list">
                    <li>Registrar compras recorrentes</li>
                    <li>Filtrar por categoria</li>
                    <li>Gerar relatorios simples</li>
                </ul>
                <div class="card-actions">
                    <a class="text-link" href="/compra">Abrir painel</a>
                </div>
            </article>
        </section>
    </main>
</body>
</html>