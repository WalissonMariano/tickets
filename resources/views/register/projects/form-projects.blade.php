@php
    $isEdit = isset($project);
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isEdit ? 'Editar projeto' : 'Novo projeto' }} — Chamados</title>
    @vite(['resources/css/components/form-page.css'])
</head>
<body class="form-page">
    <div class="form-page-container">
        <header class="form-page-header">
            <h1>{{ $isEdit ? 'Editar projeto' : 'Novo projeto' }}</h1>
            <p>{{ $isEdit ? 'Atualize os dados do projeto.' : 'Preencha os dados para cadastrar um novo projeto.' }}</p>
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
                id="project-form"
                method="POST"
                action="{{ $isEdit ? route('register.projects.update', $project) : route('register.projects.store') }}"
            >
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="form-page-grid">
                    <div class="form-page-field">
                        <label for="name">Nome</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $project->name ?? '') }}"
                            class="@error('name') is-invalid @enderror"
                            required
                        >
                        @error('name')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field form-page-field--full">
                        <input type="hidden" name="is_active" value="0">
                        <label class="form-page-checkbox">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', $project->is_active ?? true))
                            >
                            <span>Projeto ativo</span>
                        </label>
                    </div>
                </div>
            </form>

            <div class="form-page-actions">
                @if ($isEdit)
                    <form
                        method="POST"
                        action="{{ route('register.projects.destroy', $project) }}"
                        class="form-page-delete-form"
                        onsubmit="return confirm('Tem certeza que deseja excluir este projeto? Esta ação não pode ser desfeita.');"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="form-page-btn form-page-btn--danger">Excluir</button>
                    </form>
                @endif

                <div class="form-page-actions-group">
                    <a href="{{ route('register.projects.index') }}" class="form-page-btn form-page-btn--secondary">Cancelar</a>
                    <button type="submit" form="project-form" class="form-page-btn form-page-btn--primary">
                        {{ $isEdit ? 'Salvar alterações' : 'Cadastrar projeto' }}
                    </button>
                </div>
            </div>
        </div>

        @if ($isEdit)
            @include('histories.partials.audit-list', ['histories' => $histories ?? collect()])
        @endif
    </div>
</body>
</html>
