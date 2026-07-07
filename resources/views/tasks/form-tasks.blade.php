@php
    $isEdit = isset($task);

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
    <title>{{ $isEdit ? 'Editar tarefa' : 'Nova tarefa' }} — Chamados</title>
    @vite(['resources/css/components/form-page.css'])
</head>
<body class="form-page">
    <div class="form-page-container">
        <header class="form-page-header">
            <h1>{{ $isEdit ? 'Editar tarefa' : 'Nova tarefa' }}</h1>
            <p>{{ $isEdit ? 'Atualize os dados da tarefa.' : 'Preencha os dados para abrir uma nova solicitação.' }}</p>
        </header>

        <div class="form-page-card">
            @if ($errors->any())
                <div class="form-page-alert form-page-alert--error">
                    <strong>Verifique os campos abaixo:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ $isEdit ? route('tasks.update', $task) : route('tasks.store') }}"
                enctype="multipart/form-data"
            >
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="form-page-grid form-page-grid--cols-3">
                    <div class="form-page-field">
                        <label for="project_id">Projeto</label>
                        <select
                            id="project_id"
                            name="project_id"
                            class="@error('project_id') is-invalid @enderror"
                            required
                        >
                            <option value="">Selecione um projeto</option>
                            @foreach ($projects as $project)
                                <option
                                    value="{{ $project->id }}"
                                    @selected(old('project_id', $task->project_id ?? '') == $project->id)
                                >
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field">
                        <label for="code">Código</label>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            value="{{ old('code', $task->code ?? '') }}"
                            class="@error('code') is-invalid @enderror"
                            required
                        >
                        @error('code')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field">
                        <label for="title">Título</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title', $task->title ?? '') }}"
                            class="@error('title') is-invalid @enderror"
                            required
                        >
                        @error('title')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field">
                        <label for="severity">Gravidade</label>
                        <select
                            id="severity"
                            name="severity"
                            class="@error('severity') is-invalid @enderror"
                            required
                        >
                            @foreach ($severityLabels as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('severity', $task->severity ?? 'medium') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('severity')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field">
                        <label for="status">Status</label>
                        <select
                            id="status"
                            name="status"
                            class="@error('status') is-invalid @enderror"
                            required
                        >
                            @foreach ($statusLabels as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('status', $task->status ?? 'open') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field">
                        <label for="reporter_id">Solicitante</label>
                        <select
                            id="reporter_id"
                            name="reporter_id"
                            class="@error('reporter_id') is-invalid @enderror"
                            required
                        >
                            <option value="">Selecione o solicitante</option>
                            @foreach ($users as $user)
                                <option
                                    value="{{ $user->id }}"
                                    @selected(old('reporter_id', $task->reporter_id ?? '') == $user->id)
                                >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('reporter_id')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field">
                        <label for="assignee_id">Responsável</label>
                        <select
                            id="assignee_id"
                            name="assignee_id"
                            class="@error('assignee_id') is-invalid @enderror"
                        >
                            <option value="">Não atribuído</option>
                            @foreach ($users as $user)
                                <option
                                    value="{{ $user->id }}"
                                    @selected(old('assignee_id', $task->assignee_id ?? '') == $user->id)
                                >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assignee_id')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field">
                        <label for="requested_at">Data da solicitação</label>
                        <input
                            type="datetime-local"
                            id="requested_at"
                            name="requested_at"
                            value="{{ old('requested_at', isset($task) ? $task->requested_at?->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                            class="@error('requested_at') is-invalid @enderror"
                            required
                        >
                        @error('requested_at')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field">
                        <label for="resolved_at">Data da resolução</label>
                        <input
                            type="datetime-local"
                            id="resolved_at"
                            name="resolved_at"
                            value="{{ old('resolved_at', isset($task) && $task->resolved_at ? $task->resolved_at->format('Y-m-d\TH:i') : '') }}"
                            class="@error('resolved_at') is-invalid @enderror"
                        >
                        <span class="form-page-hint">Opcional. Preencha quando a tarefa for resolvida.</span>
                        @error('resolved_at')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field form-page-field--full">
                        <label for="description">Descrição</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            class="@error('description') is-invalid @enderror"
                            required
                        >{{ old('description', $task->description ?? '') }}</textarea>
                        @error('description')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field form-page-field--full">
                        <label for="attachment">Anexo</label>
                        <input
                            type="file"
                            id="attachment"
                            name="attachment"
                            class="form-page-file @error('attachment') is-invalid @enderror"
                        >
                        <span class="form-page-hint">Opcional. Formatos: PDF, imagens, Word, Excel, ZIP (máx. 10 MB).</span>
                        @if ($isEdit && $task->attachment)
                            <div class="form-page-current-file">
                                <span>Anexo atual:</span>
                                <a href="{{ asset('storage/' . $task->attachment) }}" target="_blank" rel="noopener">
                                    {{ basename($task->attachment) }}
                                </a>
                            </div>
                        @endif
                        @error('attachment')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    @include('task-notes.form-repeater-task-notes')
                </div>

                <div class="form-page-actions">
                    <a href="{{ route('tasks.index') }}" class="form-page-btn form-page-btn--secondary">Cancelar</a>
                    <button type="submit" class="form-page-btn form-page-btn--primary">
                        {{ $isEdit ? 'Salvar alterações' : 'Cadastrar tarefa' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
