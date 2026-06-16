<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';
$mesas = get_mesas();
$servidores = get_servidores();
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
                <p class="lead">Crie mesas e atribua servidores responsaveis.</p>
            </div>
            <div class="page-actions">
                <a class="button button-outline" href="/gerenciar-mesa">Ver ocupacao</a>
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
                            <th>Status</th>
                            <th>Servidor responsavel</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mesas as $m): ?>
                        <tr>
                            <td><div class="cell-title">Mesa <?= htmlspecialchars((string)$m['numero']) ?></div></td>
                            <td><?= htmlspecialchars((string)$m['capacidade']) ?> pessoas</td>
                            <td>
                                <?php
                                $badge_m = match($m['status']) {
                                    'ocupada'  => 'badge--warning',
                                    'livre'    => 'badge--success',
                                    default    => '',
                                };
                                ?>
                                <span class="badge <?= $badge_m ?>"><?= htmlspecialchars((string)$m['status']) ?></span>
                            </td>
                            <td>
                                <?php if ($m['servidor_nome']): ?>
                                <span class="cell-title"><?= htmlspecialchars((string)$m['servidor_nome']) ?></span>
                                <?php else: ?>
                                <span class="cell-sub">sem servidor</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="button-row">
                                    <a class="text-link" href="/gerenciar-mesa?id=<?= $m['id_mesa'] ?>">Gerenciar</a>
                                    <form method="POST" action="/" class="inline-form">
                                        <input type="hidden" name="action" value="delete_mesa">
                                        <input type="hidden" name="id_mesa" value="<?= $m['id_mesa'] ?>">
                                        <button class="text-link" type="submit" style="color:red" onclick="return confirm('Tem certeza?')">Apagar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($mesas)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Nenhuma mesa cadastrada.</td>
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
