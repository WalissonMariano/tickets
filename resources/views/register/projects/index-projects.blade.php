<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos — Chamados</title>
    @vite(['resources/css/components/index-page.css'])
</head>
<body class="index-page">
    <div class="index-page-container">
        <header class="index-page-header">
            <div>
                <h1>Projetos</h1>
                <p>Listagem de projetos cadastrados no sistema.</p>
            </div>
            <a href="{{ route('register.projects.create') }}" class="index-page-btn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Novo projeto
            </a>
        </header>

        @if (session('success'))
            <div class="index-page-alert index-page-alert--success">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('register.projects.index') }}" class="index-page-toolbar">
            <div class="index-page-search">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por nome..."
                >
            </div>
            <button type="submit" class="index-page-btn index-page-btn--secondary">Filtrar</button>
        </form>

        <div class="index-page-card">
            @if ($projects->isEmpty())
                <div class="index-page-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                    </svg>
                    <h2>Nenhum projeto encontrado</h2>
                    <p>
                        @if (request()->filled('search'))
                            Tente ajustar os filtros ou cadastre um novo projeto.
                        @else
                            Ainda não há projetos cadastrados no sistema.
                        @endif
                    </p>
                </div>
            @else
                <div class="index-page-table-wrap">
                    <table class="index-page-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Usuários</th>
                                <th>Tarefas</th>
                                <th>Status</th>
                                <th>Cadastrado em</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($projects as $project)
                                <tr>
                                    <td>
                                        <span class="index-page-title">{{ $project->name }}</span>
                                    </td>
                                    <td>{{ $project->users_count }}</td>
                                    <td>{{ $project->tasks_count }}</td>
                                    <td>
                                        @if ($project->is_active)
                                            <span class="index-page-badge index-page-badge--active">Ativo</span>
                                        @else
                                            <span class="index-page-badge index-page-badge--inactive">Inativo</span>
                                        @endif
                                    </td>
                                    <td>{{ $project->created_at?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="index-page-actions">
                                        <a href="{{ route('register.projects.edit', $project) }}" class="index-page-action-link">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                            Editar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="index-page-footer">
                    <span>
                        Exibindo {{ $projects->firstItem() }}–{{ $projects->lastItem() }} de {{ $projects->total() }} projetos
                    </span>
                    @if ($projects->hasPages())
                        {{ $projects->links('tasks.partials.pagination') }}
                    @endif
                </div>
            @endif
        </div>
    </div>
</body>
</html>
