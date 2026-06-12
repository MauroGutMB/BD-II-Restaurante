<?php

declare(strict_types=1);

require_once __DIR__ . '/models.php';

function auth_start(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function current_user(): ?array
{
    auth_start();
    return $_SESSION['usuario'] ?? null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['perfil'] === 'admin';
}

function attempt_login(string $usuario, string $senha): bool
{
    auth_start();
    $registro = get_usuario_by_login($usuario);
    if ($registro && password_verify($senha, (string)$registro['senha'])) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id_usuario' => $registro['id_usuario'],
            'nome' => $registro['nome'],
            'usuario' => $registro['usuario'],
            'perfil' => $registro['perfil'],
        ];
        return true;
    }
    return false;
}

function logout(): void
{
    auth_start();
    $_SESSION = [];
    session_destroy();
}
