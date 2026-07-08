@php
    $initials = collect(explode(' ', $user->name))
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->join('');
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha conta — Chamados</title>
    @vite(['resources/css/components/account.css'])
</head>
<body class="account-page">
    <div class="account-container">
        <header class="account-header">
            <h1>Minha conta</h1>
            <p>Informações do seu perfil no sistema.</p>
        </header>

        <section class="account-profile">
            <div class="account-avatar">{{ $initials }}</div>
            <div class="account-profile-info">
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>
                @if ($user->is_active)
                    <span class="account-badge account-badge--active">Ativo</span>
                @endif
            </div>
        </section>

        <section class="account-card">
            <h3>Dados pessoais</h3>
            <div class="account-details">
                <div class="account-field">
                    <label>Nome</label>
                    <span>{{ $user->name }}</span>
                </div>
                <div class="account-field">
                    <label>E-mail</label>
                    <span>{{ $user->email }}</span>
                </div>
                <div class="account-field">
                    <label>Grupo</label>
                    <span>{{ $user->group?->description ?? '—' }}</span>
                </div>
                <div class="account-field">
                    <label>Status</label>
                    <span>{{ $user->is_active ? 'Ativo' : 'Inativo' }}</span>
                </div>
                <div class="account-field">
                    <label>Membro desde</label>
                    <span>{{ $user->created_at?->format('d/m/Y') ?? '—' }}</span>
                </div>
            </div>
        </section>

        <section class="account-card">
            <h3>Projetos vinculados</h3>
            <ul class="account-projects">
                @forelse ($user->projects as $project)
                    <li class="account-project-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                        </svg>
                        {{ $project->name }}
                    </li>
                @empty
                    <li class="account-project-item account-project-item--empty">
                        Nenhum projeto vinculado.
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
</body>
</html>
