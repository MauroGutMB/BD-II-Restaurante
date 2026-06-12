<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';
require_once __DIR__ . '/../bd/auth.php';
$fornecedores = get_fornecedores();
$active = '/fornecedor';
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fornecedores</title>
    <link rel="stylesheet" href="/style.css">
    <style>
        .inline-form { display: inline-block; margin: 0; }
        .inline-form button { background: none; border: none; padding: 0; cursor: pointer; color: inherit; font: inherit; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/partials/topbar.php'; ?>
    <main class="container">
        <section class="page-header">
            <div>
                <p class="eyebrow">Fornecedores</p>
                <h1>Gerenciar Fornecedores</h1>
                <p class="lead">Cadastro de fornecedores utilizados nas compras do restaurante.</p>
            </div>
            <div class="page-actions">
                <a class="button" href="#form-card">Novo Fornecedor</a>
            </div>
        </section>

        <section class="layout">
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fornecedores as $f): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$f['nome']) ?></td>
                            <td><?= htmlspecialchars((string)$f['cnpj']) ?></td>
                            <td><?= htmlspecialchars((string)$f['telefone']) ?></td>
                            <td><?= htmlspecialchars((string)$f['email']) ?></td>
                            <td>
                                <form method="POST" action="/" class="inline-form">
                                    <input type="hidden" name="action" value="delete_fornecedor">
                                    <input type="hidden" name="id_fornecedor" value="<?= $f['id_fornecedor'] ?>">
                                    <button class="text-link" type="submit" style="color:red" onclick="return confirm('Tem certeza?')">Apagar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($fornecedores)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Nenhum fornecedor cadastrado.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <aside class="form-card" id="form-card">
                <h2>Cadastrar Fornecedor</h2>
                <form class="stack" method="POST" action="/">
                    <input type="hidden" name="action" value="create_fornecedor">
                    <label class="field">
                        <span>Nome</span>
                        <input type="text" name="nome" required>
                    </label>
                    <label class="field">
                        <span>CNPJ</span>
                        <input type="text" name="cnpj" placeholder="00.000.000/0000-00">
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
