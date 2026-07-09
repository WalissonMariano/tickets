@php
    $severityLabels = [
        'low' => 'Baixa',
        'medium' => 'Média',
        'high' => 'Alta',
        'critical' => 'Crítica',
    ];

    $severityBadgeClasses = [
        'low' => 'dashboard-badge--low',
        'medium' => 'dashboard-badge--medium',
        'high' => 'dashboard-badge--high',
        'critical' => 'dashboard-badge--critical',
    ];

    $statusBarLabels = [
        'open' => 'Abertas',
        'in_progress' => 'Em andamento',
        'resolved_closed' => 'Resolvidas/Fechadas',
    ];

    $statusBarClasses = [
        'open' => '',
        'in_progress' => 'dashboard-bar-fill--progress',
        'resolved_closed' => 'dashboard-bar-fill--resolved',
    ];
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Chamados</title>
    @vite(['resources/css/components/dashboard.css'])
</head>
<body class="dashboard-page">
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Dashboard</h1>
            <p>Métricas e indicadores do sistema de chamados.</p>
        </header>

        <section class="dashboard-stats">
            <div class="dashboard-stat-card">
                <div class="dashboard-stat-icon dashboard-stat-icon--green">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <div class="dashboard-stat-info">
                    <strong>{{ $stats['resolution_rate'] }}%</strong>
                    <span>Taxa de resolução</span>
                </div>
            </div>

            <div class="dashboard-stat-card">
                <div class="dashboard-stat-icon dashboard-stat-icon--gray">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="dashboard-stat-info">
                    <strong>{{ $stats['average_resolution'] }}</strong>
                    <span>Tempo médio</span>
                </div>
            </div>

            <div class="dashboard-stat-card">
                <div class="dashboard-stat-icon dashboard-stat-icon--green">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
                <div class="dashboard-stat-info">
                    <strong>{{ $stats['resolved_closed'] }}</strong>
                    <span>Resolvidas/Fechadas</span>
                </div>
            </div>

            <div class="dashboard-stat-card">
                <div class="dashboard-stat-icon dashboard-stat-icon--green">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <div class="dashboard-stat-info">
                    <strong>{{ $stats['active_users'] }}</strong>
                    <span>Usuários ativos</span>
                </div>
            </div>

            <div class="dashboard-stat-card">
                <div class="dashboard-stat-icon dashboard-stat-icon--dark">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                    </svg>
                </div>
                <div class="dashboard-stat-info">
                    <strong>{{ $stats['active_projects'] }}</strong>
                    <span>Projetos ativos</span>
                </div>
            </div>
        </section>

        <section class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Tarefas por status</h2>
                <div class="dashboard-bars">
                    @foreach ($statusBarLabels as $status => $label)
                        <div class="dashboard-bar-item">
                            <span class="dashboard-bar-label">{{ $label }}</span>
                            <div class="dashboard-bar-track">
                                <div
                                    class="dashboard-bar-fill {{ $statusBarClasses[$status] }}"
                                    style="width: {{ $statusBars[$status] }}%"
                                ></div>
                            </div>
                            <span class="dashboard-bar-value">{{ $displayStatusCounts[$status] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="dashboard-card">
                <h2>Tarefas por gravidade</h2>
                <div class="dashboard-severity-list">
                    @foreach ($severityLabels as $severity => $label)
                        <div class="dashboard-severity-item">
                            <span>{{ $label }}</span>
                            <span class="dashboard-badge {{ $severityBadgeClasses[$severity] }}">
                                {{ $severityCounts[$severity] }} {{ $severityCounts[$severity] === 1 ? 'tarefa' : 'tarefas' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="dashboard-card dashboard-card--wide">
                <h2>Atividade da semana</h2>
                <div class="dashboard-activity">
                    @foreach ($weeklyActivity as $day)
                        <div class="dashboard-activity-day">
                            <div
                                class="dashboard-activity-bar"
                                style="height: {{ max($day['height'], 5) }}%"
                                title="{{ $day['count'] }} {{ $day['count'] === 1 ? 'tarefa' : 'tarefas' }}"
                            ></div>
                            <span>{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</body>
</html>
