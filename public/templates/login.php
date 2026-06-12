<?php
declare(strict_types=1);
$erro = isset($_GET['erro']);
?><!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - Restaurante DB</title>
    <link rel="stylesheet" href="/style.css">
    <style>
        .login-wrap { min-height: 100vh; display: grid; place-items: center; }
        .login-card { width: 100%; max-width: 380px; }
        .login-brand { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
        .login-error { background: #fdecea; border: 1px solid #f5c6c2; color: #b3261e; padding: 10px 14px; border-radius: var(--radius-sm); font-weight: 600; font-size: 0.9rem; }
    </style>
</head>
<body>
    <main class="login-wrap">
        <div class="form-card login-card">
            <div class="login-brand">
                <span class="brand-mark">RD</span>
                <div>
                    <div class="brand-title">Restaurante DB</div>
                    <div class="brand-sub">Acesso restrito</div>
                </div>
            </div>
            <h2>Entrar</h2>
            <form class="stack" method="POST" action="/login">
                <input type="hidden" name="action" value="login">
                <?php if ($erro): ?>
                <div class="login-error">Usuario ou senha invalidos.</div>
                <?php endif; ?>
                <label class="field">
                    <span>Usuario</span>
                    <input type="text" name="usuario" required autofocus autocomplete="username">
                </label>
                <label class="field">
                    <span>Senha</span>
                    <input type="password" name="senha" required autocomplete="current-password">
                </label>
                <div class="form-actions">
                    <button class="button" type="submit">Entrar</button>
                </div>
                <p class="helper">Servidores sao cadastrados pelo gerente do restaurante.</p>
            </form>
        </div>
    </main>
</body>
</html>
