<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';

$compras = get_compras();
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compra</title>
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
                <a class="nav-link" href="/compra" aria-current="page">Compras</a>
                <a class="nav-link" href="/cliente">Clientes</a>
                <a class="nav-link" href="/mesa">Mesas</a>
                <a class="nav-link" href="/cardapio">Cardapio</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <section class="page-header">
            <div>
                <p class="eyebrow">Compras</p>
                <h1>Gerenciar compras</h1>
                <p class="lead">Centralize fornecedores, estoque e pagamentos com um fluxo simples de CRUD.</p>
            </div>
            <div class="page-actions">
                <a class="button" href="#form-card">Nova compra</a>
            </div>
        </section>

        <section class="layout">
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Descricao</th>
                            <th>Categoria</th>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $c): ?>
                        <tr>
                            <td>
                                <div class="cell-title"><?= htmlspecialchars((string)$c['descricao']) ?></div>
                            </td>
                            <td><?= htmlspecialchars((string)$c['categoria']) ?></td>
                            <td><?= htmlspecialchars((string)$c['data_despesa']) ?></td>
                            <td>R$ <?= number_format((float)$c['valor'], 2, ',', '.') ?></td>
                            <td>
                                <div class="button-row">
                                    <!-- Using simple JS to populate edit form -->
                                    <button class="text-link" onclick="editCompra(<?= $c['id_despesa'] ?>, '<?= htmlspecialchars($c['descricao']) ?>', '<?= htmlspecialchars($c['categoria']) ?>', '<?= $c['valor'] ?>', '<?= $c['data_despesa'] ?>')">Editar</button>
                                    
                                    <form method="POST" action="/" class="inline-form">
                                        <input type="hidden" name="action" value="delete_compra">
                                        <input type="hidden" name="id_despesa" value="<?= $c['id_despesa'] ?>">
                                        <button class="text-link" type="submit" style="color:red" onclick="return confirm('Tem certeza?')">Apagar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($compras)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Nenhuma compra encontrada.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <aside class="form-card" id="form-card">
                <h2 id="form-title">Cadastrar nova compra</h2>
                <form class="stack" method="POST" action="/">
                    <input type="hidden" name="action" id="action-input" value="create_compra">
                    <input type="hidden" name="id_despesa" id="id-input" value="">
                    
                    <label class="field">
                        <span>Descricao</span>
                        <input type="text" name="descricao" id="desc-input" required placeholder="Ex: Mercado fornecedor">
                    </label>
                    <label class="field">
                        <span>Categoria</span>
                        <input type="text" name="categoria" id="cat-input" required placeholder="Ex: Hortifruti">
                    </label>
                    <label class="field">
                        <span>Valor (R$)</span>
                        <input type="number" step="0.01" name="valor" id="valor-input" required placeholder="0.00">
                    </label>
                    <label class="field">
                        <span>Data</span>
                        <input type="date" name="data_despesa" id="data-input" required value="<?= date('Y-m-d') ?>">
                    </label>
                    <div class="form-actions">
                        <button class="button" type="submit" id="submit-btn">Salvar compra</button>
                        <button class="button button-outline" type="button" onclick="resetForm()">Cancelar</button>
                    </div>
                </form>
            </aside>
        </section>
    </main>

    <script>
    function editCompra(id, desc, cat, val, data) {
        document.getElementById('form-title').innerText = 'Editar compra';
        document.getElementById('action-input').value = 'update_compra';
        document.getElementById('id-input').value = id;
        document.getElementById('desc-input').value = desc;
        document.getElementById('cat-input').value = cat;
        document.getElementById('valor-input').value = val;
        
        let dateOnly = data.split(' ')[0]; // Em caso de datetime
        document.getElementById('data-input').value = dateOnly;
        
        document.getElementById('submit-btn').innerText = 'Atualizar compra';
        document.getElementById('form-card').scrollIntoView();
    }
    
    function resetForm() {
        document.getElementById('form-title').innerText = 'Cadastrar nova compra';
        document.getElementById('action-input').value = 'create_compra';
        document.getElementById('id-input').value = '';
        document.getElementById('desc-input').value = '';
        document.getElementById('cat-input').value = '';
        document.getElementById('valor-input').value = '';
        document.getElementById('data-input').value = '<?= date('Y-m-d') ?>';
        document.getElementById('submit-btn').innerText = 'Salvar compra';
    }
    </script>
</body>
</html>