<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';
$clientes = get_clientes();
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clientes</title>
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
                <a class="nav-link" href="/cliente" aria-current="page">Clientes</a>
                <a class="nav-link" href="/mesa">Mesas</a>
                <a class="nav-link" href="/cardapio">Cardapio</a>
            </nav>
        </div>
    </header>
    <main class="container">
        <section class="page-header">
            <div>
                <p class="eyebrow">Clientes</p>
                <h1>Gerenciar Clientes</h1>
                <p class="lead">Cadastre e veja os clientes do restaurante.</p>
            </div>
            <div class="page-actions">
                <a class="button" href="#form-card">Novo Cliente</a>
            </div>
        </section>

        <section class="layout">
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$c['nome']) ?></td>
                            <td><?= htmlspecialchars((string)$c['telefone']) ?></td>
                            <td><?= htmlspecialchars((string)$c['email']) ?></td>
                            <td>
                                <form method="POST" action="/" class="inline-form">
                                    <input type="hidden" name="action" value="delete_cliente">
                                    <input type="hidden" name="id_cliente" value="<?= $c['id_cliente'] ?>">
                                    <button class="text-link" type="submit" style="color:red" onclick="return confirm('Tem certeza?')">Apagar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">Nenhum cliente cadastrado.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <aside class="form-card" id="form-card">
                <h2>Cadastrar Cliente</h2>
                <form class="stack" method="POST" action="/">
                    <input type="hidden" name="action" value="create_cliente">
                    <label class="field">
                        <span>Nome</span>
                        <input type="text" name="nome" required>
                    </label>
                    <label class="field">
                        <span>Telefone</span>
                        <input type="text" name="telefone">
                    </label>
                    <label class="field">
                        <span>Email</span>
                        <input type="email" name="email">
                    </label>
                    <div class="form-actions">
                        <button class="button" type="submit">Salvar</button>
                    </div>
                </form>
            </aside>
        </section>
    </main>
</body>
</html>