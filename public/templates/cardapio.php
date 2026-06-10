<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';
$produtos = get_produtos();
$categorias = get_categorias();
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cardápio</title>
    <link rel="stylesheet" href="/style.css">
    <style>
        .inline-form { display: inline-block; margin: 0; }
        .inline-form button { background: none; border: none; padding: 0; cursor: pointer; color: inherit; font: inherit; }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <div class="brand">
                <span class="brand-mark">RD</span>
                <div>
                    <div class="brand-title">Restaurante DB</div>
                    <div class="brand-sub">Painel de gestao de pedidos e compras</div>
                </div>
            </div>
            <nav class="nav-links">
                <a class="nav-link" href="/">Inicio</a>
                <a class="nav-link" href="/pedido">Pedidos</a>
                <a class="nav-link" href="/compra">Compras</a>
                <a class="nav-link" href="/cliente">Clientes</a>
                <a class="nav-link" href="/mesa">Mesas</a>
                <a class="nav-link" href="/cardapio" aria-current="page">Cardapio</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <section class="page-header">
            <div>
                <p class="eyebrow">Cardapio</p>
                <h1>Gerenciar Cardapio (Produtos)</h1>
                <p class="lead">Produtos disponiveis no menu e em estoque.</p>
            </div>
            <div class="page-actions">
                <a class="button" href="#form-card">Novo Produto</a>
                <a class="button button-ghost" href="#form-cat">Nova Categoria</a>
            </div>
        </section>

        <section class="layout">
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$p['nome']) ?></td>
                            <td><?= htmlspecialchars((string)$p['categoria_nome']) ?></td>
                            <td>R$ <?= number_format((float)$p['preco'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars((string)$p['estoque']) ?></td>
                            <td>
                                <form method="POST" action="/" class="inline-form">
                                    <input type="hidden" name="action" value="delete_produto">
                                    <input type="hidden" name="id_produto" value="<?= $p['id_produto'] ?>">
                                    <button class="text-link" type="submit" style="color:red" onclick="return confirm('Tem certeza?')">Apagar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($produtos)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Nenhum produto cadastrado.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="stack">
                <aside class="form-card" id="form-card">
                    <h2>Cadastrar Produto</h2>
                    <form class="stack" method="POST" action="/">
                        <input type="hidden" name="action" value="create_produto">
                        <label class="field">
                            <span>Nome</span>
                            <input type="text" name="nome" required>
                        </label>
                        <label class="field">
                            <span>Descrição</span>
                            <input type="text" name="descricao">
                        </label>
                        <label class="field">
                            <span>Preço</span>
                            <input type="number" step="0.01" name="preco" required>
                        </label>
                        <label class="field">
                            <span>Estoque atual (Opcional)</span>
                            <input type="number" name="estoque" value="0">
                        </label>
                        <label class="field">
                            <span>Categoria</span>
                            <select name="id_categoria">
                                <option value="">(Sem categoria)</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars((string)$cat['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <div class="form-actions">
                            <button class="button" type="submit">Salvar Produto</button>
                        </div>
                    </form>
                </aside>

                <aside class="form-card" id="form-cat" style="margin-top: 2rem;">
                    <h2>Nova Categoria</h2>
                    <form class="stack" method="POST" action="/">
                        <input type="hidden" name="action" value="create_categoria">
                        <label class="field">
                            <span>Nome da Categoria</span>
                            <input type="text" name="nome" required>
                        </label>
                        <div class="form-actions">
                            <button class="button" type="submit">Adicionar Categoria</button>
                        </div>
                    </form>
                </aside>
            </div>
        </section>
    </main>
</body>
</html>