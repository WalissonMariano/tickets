@php
    $selectedProjects = old('projects', isset($user) ? $user->projects->pluck('id')->toArray() : []);

    if (empty($selectedProjects)) {
        $selectedProjects = [''];
    }
@endphp

<div class="form-repeater form-page-field--full" id="project-user-repeater">
    <div class="form-repeater-header">
        <div>
            <label class="form-repeater-title">Projetos</label>
            <span class="form-page-hint">Selecione os projetos dos quais o usuário faz parte.</span>
        </div>
        <button type="button" class="form-repeater-add" id="project-repeater-add">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Adicionar projeto
        </button>
    </div>

    @error('projects')
        <span class="form-page-error">{{ $message }}</span>
    @enderror
    @error('projects.*')
        <span class="form-page-error">{{ $message }}</span>
    @enderror

    <div class="form-repeater-list" id="project-repeater-list">
        @foreach ($selectedProjects as $index => $selectedProjectId)
            <div class="form-repeater-item">
                <select
                    name="projects[]"
                    class="form-repeater-select @error('projects.' . $index) is-invalid @enderror"
                >
                    <option value="">Selecione um projeto</option>
                    @foreach ($projects as $project)
                        <option
                            value="{{ $project->id }}"
                            @selected((string) $selectedProjectId === (string) $project->id)
                        >
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
                <button type="button" class="form-repeater-remove" title="Remover projeto" aria-label="Remover projeto">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endforeach
    </div>
</div>

<template id="project-repeater-template">
    <div class="form-repeater-item">
        <select name="projects[]" class="form-repeater-select">
            <option value="">Selecione um projeto</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </select>
        <button type="button" class="form-repeater-remove" title="Remover projeto" aria-label="Remover projeto">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

<script>
    (function () {
        const list = document.getElementById('project-repeater-list');
        const addButton = document.getElementById('project-repeater-add');
        const template = document.getElementById('project-repeater-template');

        if (!list || !addButton || !template) {
            return;
        }

        function updateRemoveButtons() {
            const items = list.querySelectorAll('.form-repeater-item');
            items.forEach((item) => {
                const removeButton = item.querySelector('.form-repeater-remove');
                if (removeButton) {
                    removeButton.disabled = items.length === 1;
                }
            });
        }

        addButton.addEventListener('click', () => {
            list.appendChild(template.content.cloneNode(true));
            updateRemoveButtons();
        });

        list.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.form-repeater-remove');
            if (!removeButton || removeButton.disabled) {
                return;
            }

            const item = removeButton.closest('.form-repeater-item');
            if (item) {
                item.remove();
                updateRemoveButtons();
            }
        });

        updateRemoveButtons();
    })();
</script>
