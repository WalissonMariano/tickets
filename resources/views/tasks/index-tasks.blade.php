@php
    $statusLabels = [
        'open' => 'Aberta',
        'in_progress' => 'Em andamento',
        'resolved' => 'Resolvida',
        'closed' => 'Fechada',
    ];

    $severityLabels = [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
        'critical' => 'Crítica',
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarefas — Chamados</title>
    @vite(['resources/css/components/index-page.css'])
</head>
<body class="index-page">
    <div class="index-page-container">
        <header class="index-page-header">
            <div>
                <h1>Tarefas</h1>
                <p>Listagem de chamados e solicitações do sistema.</p>
            </div>
            <a href="{{ route('tasks.create') }}" class="index-page-btn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nova tarefa
            </a>
        </header>

        @if (session('success'))
            <div class="index-page-alert index-page-alert--success">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('tasks.index') }}" class="index-page-toolbar">
            <div class="index-page-search">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por código ou título..."
                >
            </div>
            <div class="index-page-select">
                <select name="status">
                    <option value="">Todos os status</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="index-page-select">
                <select name="severity">
                    <option value="">Todas as gravidades</option>
                    @foreach ($severityLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('severity') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="index-page-btn index-page-btn--secondary">Filtrar</button>
        </form>

        <div class="index-page-card">
            @if ($tasks->isEmpty())
                <div class="index-page-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <h2>Nenhuma tarefa encontrada</h2>
                    <p>
                        @if (request()->hasAny(['search', 'status', 'severity']))
                            Tente ajustar os filtros ou cadastre uma nova tarefa.
                        @else
                            Ainda não há tarefas cadastradas no sistema.
                        @endif
                    </p>
                </div>
            @else
                <div class="index-page-table-wrap">
                    <table class="index-page-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Título</th>
                                <th>Projeto</th>
                                <th>Solicitante</th>
                                <th>Responsável</th>
                                <th>Gravidade</th>
                                <th>Status</th>
                                <th>Solicitada em</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tasks as $task)
                                <tr>
                                    <td>
                                        <span class="index-page-code">#{{ $task->code }}</span>
                                    </td>
                                    <td>
                                        <span class="index-page-title">{{ $task->title }}</span>
                                    </td>
                                    <td>{{ $task->project?->name ?? '—' }}</td>
                                    <td>{{ $task->reporter?->name ?? '—' }}</td>
                                    <td>
                                        @if ($task->assignee)
                                            {{ $task->assignee->name }}
                                        @else
                                            <span class="index-page-muted">Não atribuído</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="index-page-badge index-page-badge--{{ $task->severity }}">
                                            {{ $severityLabels[$task->severity] ?? $task->severity }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="index-page-badge index-page-badge--{{ $task->status }}">
                                            {{ $statusLabels[$task->status] ?? $task->status }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $task->requested_at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                    <td class="index-page-actions">
                                        <a href="{{ route('tasks.edit', $task) }}" class="index-page-action-link">
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
                        Exibindo {{ $tasks->firstItem() }}–{{ $tasks->lastItem() }} de {{ $tasks->total() }} tarefas
                    </span>
                    @if ($tasks->hasPages())
                        {{ $tasks->links('tasks.partials.pagination') }}
                    @endif
                </div>
            @endif
        </div>
    </div>
</body>
</html>
