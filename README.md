# Chamados.TI

Sistema de gerenciamento de chamados desenvolvido em Laravel. Permite controlar solicitações por projeto, atribuir tarefas a usuários, registrar anotações com anexos e manter histórico de alterações.

## Funcionalidades

### Implementado
- Tela de login com autenticação
- Layout principal com menu lateral e conteúdo em iframe
- Página Home com resumo e ações rápidas
- Dashboard com métricas e indicadores
- Models, migrations e relacionamentos do banco de dados
- Estrutura de views para cadastros (grupos, usuários, projetos, tarefas, etc.)

### Planejado
- CRUD completo de cadastros e tarefas
- Envio de e-mail
- Armazenamento de arquivos anexados
- Middleware de autenticação nas rotas protegidas

## Tecnologias

- PHP 8.1+
- Laravel 10
- Laravel Sanctum
- Vite
- MySQL (ou banco configurado no `.env`)

## Estrutura do banco

| Tabela | Descrição |
|--------|-----------|
| `groups` | Grupos de usuários |
| `users` | Usuários do sistema |
| `projects` | Projetos |
| `project_user` | Vínculo usuário ↔ projeto |
| `tasks` | Chamados/tarefas |
| `task_notes` | Anotações e anexos das tarefas |
| `histories` | Log de auditoria (ações nos registros) |

## Estrutura de views

```
resources/views/
├── auth/           # Login
├── layouts/        # Menu principal
├── home/           # Página inicial
├── dashboard/      # Dashboard
├── groups/         # Cadastro de grupos
├── users/          # Cadastro de usuários
├── projects/       # Cadastro de projetos
├── project-user/   # Vínculo usuário-projeto
├── tasks/          # Tarefas
├── task-notes/     # Anotações
└── histories/      # Histórico
```

## Instalação

```bash
# Clonar o repositório e entrar na pasta
cd tickets

# Instalar dependências
composer install
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Configurar banco de dados no .env e rodar migrations
php artisan migrate

# Compilar assets
npm run dev
```

## Rotas principais

| Rota | Descrição |
|------|-----------|
| `/` | Login |
| `/menu` | Layout com menu e iframe |
| `/home` | Home (conteúdo do iframe) |
| `/dashboard` | Dashboard (conteúdo do iframe) |

> As rotas ainda não estão protegidas por middleware de autenticação durante o desenvolvimento inicial.

## Desenvolvimento

```bash
# Servidor de desenvolvimento (Vite)
npm run dev

# Build para produção
npm run build

# Rodar testes
php artisan test
```

Com [Laravel Herd](https://herd.laravel.com), o projeto pode ser acessado diretamente em `http://tickets.test`.

## Paleta visual

- Verde primário: `#16a34a`
- Sidebar: `#2C2C2C`
- Fundo da área de conteúdo: `#f1f5f9`

## Licença

Este projeto é open-source sob a licença [MIT](https://opensource.org/licenses/MIT).
