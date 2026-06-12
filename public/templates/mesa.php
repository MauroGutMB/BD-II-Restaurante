<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';
$mesas = get_mesas();
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mesas</title>
    <link rel="stylesheet" href="/style.css">
    <style>
        .inline-form { display: inline-block; margin: 0; }
        .inline-form button { background: none; border: none; padding: 0; cursor: pointer; color: inherit; font: inherit; }
    </style>
</head>
<body>
    <?php $active = '/mesa'; require __DIR__ . '/partials/topbar.php'; ?>
    <main class="container">
        <section class="page-header">
            <div>
                <p class="eyebrow">Mesas</p>
                <h1>Gerenciar Mesas</h1>
                <p class="lead">Controle de mesas do restaurante.</p>
            </div>
            <div class="page-actions">
                <a class="button" href="#form-card">Nova Mesa</a>
            </div>
        </section>

        <section class="layout">
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Capacidade</th>
                            <th>Status actual</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mesas as $m): ?>
                        <tr>
                            <td>Mesa <?= htmlspecialchars((string)$m['numero']) ?></td>
                            <td><?= htmlspecialchars((string)$m['capacidade']) ?> pessoas</td>
                            <td><span class="badge"><?= htmlspecialchars((string)$m['status']) ?></span></td>
                            <td>
                                <form method="POST" action="/" class="inline-form">
                                    <input type="hidden" name="action" value="delete_mesa">
                                    <input type="hidden" name="id_mesa" value="<?= $m['id_mesa'] ?>">
                                    <button class="text-link" type="submit" style="color:red" onclick="return confirm('Tem certeza?')">Apagar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($mesas)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">Nenhuma mesa cadastrada.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <aside class="form-card" id="form-card">
                <h2>Cadastrar Mesa</h2>
                <form class="stack" method="POST" action="/">
                    <input type="hidden" name="action" value="create_mesa">
                    <label class="field">
                        <span>Numero da Mesa</span>
                        <input type="number" name="numero" required>
                    </label>
                    <label class="field">
                        <span>Capacidade</span>
                        <input type="number" name="capacidade" value="4" required>
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