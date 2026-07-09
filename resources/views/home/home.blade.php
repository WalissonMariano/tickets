@php
    $statusLabels = [
        'open' => 'Aberta',
        'in_progress' => 'Em andamento',
        'resolved' => 'Resolvida',
        'closed' => 'Fechada',
    ];

    $statusBadgeClasses = [
        'open' => 'home-badge--open',
        'in_progress' => 'home-badge--progress',
        'resolved' => 'home-badge--high',
        'closed' => 'home-badge--high',
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home — Chamados</title>
    @vite(['resources/css/components/home.css'])
</head>
<body class="home-page">
    <div class="home-container">
        <header class="home-header">
            <h1>Bem-vindo de volta</h1>
            <p>Gerencie suas solicitações e acompanhe o andamento das tarefas.</p>
        </header>

        <section class="home-stats">
            <div class="home-stat-card">
                <div class="home-stat-icon home-stat-icon--open">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div class="home-stat-info">
                    <strong>{{ $stats['open'] }}</strong>
                    <span>Abertas</span>
                </div>
            </div>

            <div class="home-stat-card">
                <div class="home-stat-icon home-stat-icon--progress">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="home-stat-info">
                    <strong>{{ $stats['in_progress'] }}</strong>
                    <span>Em andamento</span>
                </div>
            </div>

            <div class="home-stat-card">
                <div class="home-stat-icon home-stat-icon--resolved">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
                <div class="home-stat-info">
                    <strong>{{ $stats['resolved_closed'] }}</strong>
                    <span>Resolvidas/Fechadas</span>
                </div>
            </div>

            <div class="home-stat-card">
                <div class="home-stat-icon home-stat-icon--total">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                    </svg>
                </div>
                <div class="home-stat-info">
                    <strong>{{ $stats['total'] }}</strong>
                    <span>Total</span>
                </div>
            </div>
        </section>

        <section class="home-grid">
            <div class="home-card">
                <h2>Ações rápidas</h2>
                <div class="home-actions">
                    <a href="{{ route('tasks.create') }}" class="home-action-btn home-action-btn--primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nova tarefa
                    </a>
                    <a href="{{ route('tasks.index') }}" class="home-action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                        Minhas tarefas
                    </a>
                    <a href="{{ route('dashboard') }}" class="home-action-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        Ver dashboard
                    </a>
                </div>
            </div>

            <div class="home-card">
                <h2>Tarefas recentes</h2>
                @if ($recentTasks->isEmpty())
                    <p>Nenhuma tarefa encontrada nos seus projetos.</p>
                @else
                    <ul class="home-recent-list">
                        @foreach ($recentTasks as $task)
                            <li class="home-recent-item">
                                <div class="home-recent-item-info">
                                    <strong>
                                        <a href="{{ route('tasks.edit', $task) }}">{{ $task->title }}</a>
                                    </strong>
                                    <span>#{{ $task->code }} · {{ $task->project?->name ?? '—' }}</span>
                                </div>
                                <span class="home-badge {{ $statusBadgeClasses[$task->status] ?? 'home-badge--open' }}">
                                    {{ $statusLabels[$task->status] ?? $task->status }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</body>
</html>
