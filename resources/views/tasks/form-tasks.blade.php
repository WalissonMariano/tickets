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

    $reporterName = $isEdit
        ? ($task->reporter?->name ?? '—')
        : auth()->user()->name;

    $assigneeName = $isEdit
        ? ($task->assignee?->name ?? 'Não atribuído')
        : 'Não atribuído';

    $requestedAtDisplay = $isEdit
        ? ($task->requested_at?->format('d/m/Y H:i') ?? '—')
        : now()->format('d/m/Y H:i');

    $resolvedAtDisplay = ($isEdit && $task->resolved_at)
        ? $task->resolved_at->format('d/m/Y H:i')
        : '—';

    $statusDisplay = $statusLabels[$isEdit ? $task->status : 'open'] ?? 'Aberta';

    $canAssignTasks = auth()->user()->loadMissing('group')->canAssignTasks();
    $isTaskLocked = $isEdit && ($task->status ?? 'open') !== 'open';
    $severityDisplay = $severityLabels[$isEdit ? ($task->severity ?? 'medium') : 'medium'] ?? 'Média';
    $projectName = $isEdit ? ($task->project?->name ?? '—') : null;

    $showNotes = $isEdit && ($task->status ?? 'open') !== 'open';
    $notesLocked = $isEdit && ($task->status ?? '') === 'closed';

    $isAssignedToMe = $isEdit && (int) ($task->assignee_id ?? 0) === (int) auth()->id();

    $showAssignToMe = $canAssignTasks && (
        $isEdit
            ? ($task->status ?? 'open') === 'open' && ! $isAssignedToMe
            : true
    );

    $showResolve = $canAssignTasks && $isEdit && ($task->status ?? '') === 'in_progress';
    $showClose = $isEdit && ($task->status ?? '') === 'resolved';
    $showSaveTask = ! $isTaskLocked;
    $showDelete = $isEdit && auth()->user()->loadMissing('group')->canDeleteTask($task);
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $isEdit ? 'Editar tarefa' : 'Nova tarefa' }} — Chamados</title>
    @vite(['resources/css/components/form-page.css'])
</head>
<body class="form-page">
    <div class="form-page-container">
        <header class="form-page-header">
            <h1>{{ $isEdit ? 'Editar tarefa' : 'Nova tarefa' }}</h1>
            <p>
                @if ($isTaskLocked)
                    Os dados da tarefa estão bloqueados. Você pode adicionar anotações abaixo.
                @elseif ($isEdit)
                    Atualize os dados da tarefa.
                @else
                    Preencha os dados para abrir uma nova solicitação.
                @endif
            </p>
        </header>

        <div class="form-page-card">
            @if (session('success'))
                <div class="form-page-alert form-page-alert--success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->getBag('default')->any())
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
                id="task-form"
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
                        <label for="code">Código</label>
                        <input
                            type="text"
                            id="code"
                            value="{{ $isEdit ? $task->code : '—' }}"
                            class="form-page-input--readonly"
                            readonly
                        >
                        @unless ($isEdit)
                            <span class="form-page-hint">Número sequencial gerado automaticamente ao salvar.</span>
                        @endunless
                    </div>

                    <div class="form-page-field">
                        <label for="project_id">Projeto</label>
                        @if ($isTaskLocked)
                            <input type="hidden" name="project_id" value="{{ $task->project_id }}">
                            <input
                                type="text"
                                id="project_id"
                                value="{{ $projectName }}"
                                class="form-page-input--readonly"
                                readonly
                            >
                        @else
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
                        @endif
                    </div>

                    <div class="form-page-field">
                        <label for="title">Título</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title', $task->title ?? '') }}"
                            class="{{ $isTaskLocked ? 'form-page-input--readonly' : '' }} @error('title') is-invalid @enderror"
                            @readonly($isTaskLocked)
                            required
                        >
                        @error('title')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field">
                        <label for="severity">Gravidade</label>
                        @if ($isTaskLocked)
                            <input type="hidden" name="severity" value="{{ $task->severity }}">
                            <input
                                type="text"
                                id="severity"
                                value="{{ $severityDisplay }}"
                                class="form-page-input--readonly"
                                readonly
                            >
                        @else
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
                        @endif
                    </div>

                    <div class="form-page-field">
                        <label for="reporter_id">Solicitante</label>
                        <input
                            type="text"
                            id="reporter_id"
                            value="{{ $reporterName }}"
                            class="form-page-input--readonly"
                            readonly
                        >
                    </div>

                    <div class="form-page-field">
                        <label for="assignee_id">Responsável</label>
                        <input
                            type="text"
                            id="assignee_id"
                            value="{{ $assigneeName }}"
                            class="form-page-input--readonly"
                            readonly
                        >
                    </div>

                    <div class="form-page-field">
                        <label for="requested_at">Data da solicitação</label>
                        <input
                            type="text"
                            id="requested_at"
                            value="{{ $requestedAtDisplay }}"
                            class="form-page-input--readonly"
                            readonly
                        >
                    </div>

                    <div class="form-page-field">
                        <label for="resolved_at">Data da resolução</label>
                        <input
                            type="text"
                            id="resolved_at"
                            value="{{ $resolvedAtDisplay }}"
                            class="form-page-input--readonly"
                            readonly
                        >
                    </div>

                    <div class="form-page-field">
                        <label for="status">Status</label>
                        <input
                            type="text"
                            id="status"
                            value="{{ $statusDisplay }}"
                            class="form-page-input--readonly"
                            readonly
                        >
                    </div>

                    <div class="form-page-field form-page-field--full">
                        <label for="description">Descrição</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            class="{{ $isTaskLocked ? 'form-page-input--readonly' : '' }} @error('description') is-invalid @enderror"
                            @readonly($isTaskLocked)
                            required
                        >{{ old('description', $task->description ?? '') }}</textarea>
                        @error('description')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field form-page-field--full">
                        <label for="attachment">Anexo</label>
                        @unless ($isTaskLocked)
                            <input
                                type="file"
                                id="attachment"
                                name="attachment"
                                class="form-page-file @error('attachment') is-invalid @enderror"
                            >
                            <span class="form-page-hint">Opcional. Formatos: PDF, imagens, Word, Excel, ZIP (máx. 10 MB).</span>
                        @endunless
                        @if ($isEdit && $task->attachment)
                            <div class="form-page-current-file">
                                <span>Anexo atual:</span>
                                <a href="{{ asset('storage/' . $task->attachment) }}" target="_blank" rel="noopener">
                                    {{ basename($task->attachment) }}
                                </a>
                            </div>
                        @elseif ($isTaskLocked)
                            <input
                                type="text"
                                value="Nenhum anexo"
                                class="form-page-input--readonly"
                                readonly
                            >
                        @endif
                        @error('attachment')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </form>

            <div class="form-page-actions">
                @if ($showDelete)
                    <form
                        method="POST"
                        action="{{ route('tasks.destroy', $task) }}"
                        class="form-page-delete-form"
                        onsubmit="return confirm('Tem certeza que deseja excluir esta tarefa? Esta ação não pode ser desfeita.');"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="form-page-btn form-page-btn--danger">Excluir</button>
                    </form>
                @endif

                <div class="form-page-actions-group">
                    <a href="{{ route('tasks.index') }}" class="form-page-btn form-page-btn--secondary">Cancelar</a>
                    @if ($showAssignToMe)
                        <button
                            type="submit"
                            form="task-form"
                            name="assign_to_me"
                            value="1"
                            class="form-page-btn form-page-btn--secondary"
                        >
                            Atribuir a mim
                        </button>
                    @endif
                    @if ($showResolve)
                        <button
                            type="submit"
                            form="task-form"
                            name="mark_resolved"
                            value="1"
                            class="form-page-btn form-page-btn--secondary"
                        >
                            Resolvido
                        </button>
                    @endif
                    @if ($showClose)
                        <button
                            type="submit"
                            form="task-form"
                            name="mark_closed"
                            value="1"
                            class="form-page-btn form-page-btn--secondary"
                        >
                            Fechar tarefa
                        </button>
                    @endif
                    @if ($showSaveTask)
                        <button type="submit" form="task-form" class="form-page-btn form-page-btn--primary">
                            {{ $isEdit ? 'Salvar alterações' : 'Cadastrar tarefa' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        @if ($showNotes)
            <div class="form-page-card form-page-card--section">
                @if (session('notes_success'))
                    <div class="form-page-alert form-page-alert--success">
                        {{ session('notes_success') }}
                    </div>
                @endif

                @if ($errors->getBag('notes')->any())
                    <div class="form-page-alert form-page-alert--error">
                        <strong>Verifique as anotações abaixo:</strong>
                        <ul>
                            @foreach ($errors->getBag('notes')->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($notesLocked)
                    @include('task-notes.form-repeater-task-notes', [
                        'users' => $users,
                        'taskNotes' => $taskNotes ?? [],
                        'task' => $task,
                        'errorBag' => 'notes',
                        'notesLocked' => true,
                    ])
                @else
                    <form
                        id="task-notes-form"
                        method="POST"
                        action="{{ route('tasks.notes.update', $task) }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PUT')

                        @include('task-notes.form-repeater-task-notes', [
                            'users' => $users,
                            'taskNotes' => $taskNotes ?? [],
                            'task' => $task,
                            'errorBag' => 'notes',
                            'notesLocked' => false,
                        ])

                        <div class="form-page-actions form-page-actions--section">
                            <button type="submit" class="form-page-btn form-page-btn--primary">
                                Salvar anotações
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endif
    </div>
</body>
</html>
