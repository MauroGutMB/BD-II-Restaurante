# Logins do Sistema

Credenciais dos usuários cadastrados no banco de dados (`database.sqlite`).

> ⚠️ Senhas de desenvolvimento/avaliação. As senhas são armazenadas no banco com hash (bcrypt) — os valores abaixo são apenas para teste local.

## Gerente (admin)

| Nome    | Usuário | Senha      | Perfil |
| ------- | ------- | ---------- | ------ |
| Gerente | `admin` | `admin123` | admin  |

O gerente tem acesso total ao sistema, incluindo as páginas **Servidores** (`/servidor`) e **Fornecedores** (`/fornecedor`).

## Servidores

| Nome         | Usuário | Senha      | Perfil   |
| ------------ | ------- | ---------- | -------- |
| Maria Santos | `maria` | `maria123` | servidor |
| Joao Pereira | `joao`  | `joao123`  | servidor |
| Ana Lima     | `ana`   | `ana123`   | servidor |

Servidores gerenciam **pedidos**, **clientes**, **compras**, **mesas** e **cardápio**. Não têm acesso às áreas de administração.

## Como rodar

```bash
php -S localhost:8000 -t public public/index.php
```

Acesse `http://localhost:8000` e faça login com uma das credenciais acima.
