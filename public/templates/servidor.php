<?php
declare(strict_types=1);
require_once __DIR__ . '/../bd/models.php';
require_once __DIR__ . '/../bd/auth.php';
$servidores = get_servidores();
$erro_duplicado = ($_GET['erro'] ?? '') === 'duplicado';
$active = '/servidor';
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Servidores</title>
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
                <p class="eyebrow">Servidores</p>
                <h1>Gerenciar Servidores</h1>
                <p class="lead">Cadastre os servidores que operam pedidos, clientes e compras.</p>
            </div>
            <div class="page-actions">
                <a class="button" href="#form-card">Novo Servidor</a>
            </div>
        </section>

        <section class="layout">
            <div class="table-card">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Usuario</th>
                            <th>Situacao</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servidores as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$s['nome']) ?></td>
                            <td><?= htmlspecialchars((string)$s['usuario']) ?></td>
                            <td>
                                <span class="badge <?= $s['ativo'] ? 'badge--success' : 'badge--warning' ?>">
                                    <?= $s['ativo'] ? 'ativo' : 'inativo' ?>
                                </span>
                            </td>
                            <td>
                                <div class="button-row">
                                    <form method="POST" action="/" class="inline-form">
                                        <input type="hidden" name="action" value="set_servidor_ativo">
                                        <input type="hidden" name="id_usuario" value="<?= $s['id_usuario'] ?>">
                                        <input type="hidden" name="ativo" value="<?= $s['ativo'] ? 0 : 1 ?>">
                                        <button class="text-link" type="submit"><?= $s['ativo'] ? 'Desativar' : 'Ativar' ?></button>
                                    </form>
                                    <form method="POST" action="/" class="inline-form">
                                        <input type="hidden" name="action" value="delete_servidor">
                                        <input type="hidden" name="id_usuario" value="<?= $s['id_usuario'] ?>">
                                        <button class="text-link" type="submit" style="color:red" onclick="return confirm('Tem certeza?')">Apagar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($servidores)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">Nenhum servidor cadastrado.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <aside class="form-card" id="form-card">
                <h2>Cadastrar Servidor</h2>
                <form class="stack" method="POST" action="/">
                    <input type="hidden" name="action" value="create_servidor">
                    <?php if ($erro_duplicado): ?>
                    <p class="helper" style="color:red;">Ja existe um usuario com esse login.</p>
                    <?php endif; ?>
                    <label class="field">
                        <span>Nome</span>
                        <input type="text" name="nome" required>
                    </label>
                    <label class="field">
                        <span>Usuario (login)</span>
                        <input type="text" name="usuario" required>
                    </label>
                    <label class="field">
                        <span>Senha</span>
                        <input type="password" name="senha" required minlength="4">
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
