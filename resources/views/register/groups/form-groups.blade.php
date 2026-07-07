@php
    $isEdit = isset($group);
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isEdit ? 'Editar grupo' : 'Novo grupo' }} — Chamados</title>
    @vite(['resources/css/components/form-page.css'])
</head>
<body class="form-page">
    <div class="form-page-container">
        <header class="form-page-header">
            <h1>{{ $isEdit ? 'Editar grupo' : 'Novo grupo' }}</h1>
            <p>{{ $isEdit ? 'Atualize os dados do grupo.' : 'Preencha os dados para cadastrar um novo grupo.' }}</p>
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
                action="{{ $isEdit ? route('register.groups.update', $group) : route('register.groups.store') }}"
            >
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="form-page-grid">
                    <div class="form-page-field">
                        <label for="code">Código</label>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            value="{{ old('code', $group->code ?? '') }}"
                            class="@error('code') is-invalid @enderror"
                            required
                        >
                        @error('code')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field">
                        <label for="description">Descrição</label>
                        <input
                            type="text"
                            id="description"
                            name="description"
                            value="{{ old('description', $group->description ?? '') }}"
                            class="@error('description') is-invalid @enderror"
                            required
                        >
                        @error('description')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-page-actions">
                    <a href="{{ route('register.groups.index') }}" class="form-page-btn form-page-btn--secondary">Cancelar</a>
                    <button type="submit" class="form-page-btn form-page-btn--primary">
                        {{ $isEdit ? 'Salvar alterações' : 'Cadastrar grupo' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
