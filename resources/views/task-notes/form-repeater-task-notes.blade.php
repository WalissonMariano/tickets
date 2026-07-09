@php
    $taskNotes = $taskNotes ?? [];
    $errorBag = $errorBag ?? 'default';
    $notesErrors = $errors->getBag($errorBag);
    $currentUser = auth()->user();
    $notesLocked = $notesLocked ?? false;
    $noteHistories = $noteHistories ?? collect();
@endphp

<div class="form-repeater form-repeater--notes form-page-field--full" id="task-notes-repeater">
    <div class="form-repeater-header">
        <div>
            <label class="form-repeater-title">Anotações</label>
            <span class="form-page-hint">
                @if ($notesLocked)
                    Histórico de anotações do chamado (somente leitura).
                @else
                    Registre o histórico de anotações do chamado.
                @endif
            </span>
        </div>
        @unless ($notesLocked)
            <button type="button" class="form-repeater-add" id="task-notes-repeater-add">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Adicionar anotação
            </button>
        @endunless
    </div>

    @if ($notesErrors->has('notes'))
        <span class="form-page-error">{{ $notesErrors->first('notes') }}</span>
    @endif

    <div class="form-repeater-list form-repeater-list--notes" id="task-notes-repeater-list">
        @foreach ($taskNotes as $index => $taskNote)
            @php
                $isPersistedNote = ! empty($taskNote['id']);
                $noteUserId = $isPersistedNote
                    ? ($taskNote['user_id'] ?? null)
                    : $currentUser->id;
                $noteUserName = $isPersistedNote
                    ? ($users->firstWhere('id', $noteUserId)?->name ?? '—')
                    : $currentUser->name;
                $canEditNote = ! $notesLocked && (
                    ! $isPersistedNote || (int) $noteUserId === (int) $currentUser->id
                );
            @endphp
            <div class="form-repeater-item form-repeater-item--note">
                @if ($isPersistedNote && ! $notesLocked)
                    <input type="hidden" name="notes[{{ $index }}][id]" value="{{ $taskNote['id'] }}">
                @endif
                @if (! empty($taskNote['attachment']) && $canEditNote)
                    <input type="hidden" name="notes[{{ $index }}][existing_attachment]" value="{{ $taskNote['attachment'] }}">
                @endif

                <div class="form-repeater-note-grid">
                    <div class="form-page-field">
                        <label>Usuário</label>
                        @if ($isPersistedNote && ! $notesLocked)
                            <input type="hidden" name="notes[{{ $index }}][user_id]" value="{{ $noteUserId }}">
                        @endif
                        <input
                            type="text"
                            value="{{ $noteUserName }}"
                            class="form-page-input--readonly"
                            readonly
                        >
                    </div>

                    <div class="form-page-field form-page-field--full">
                        <label>Anotação</label>
                        <textarea
                            @if ($canEditNote) name="notes[{{ $index }}][note]" @endif
                            rows="3"
                            class="{{ ! $canEditNote ? 'form-page-input--readonly' : '' }} {{ $notesErrors->has('notes.' . $index . '.note') ? 'is-invalid' : '' }}"
                            placeholder="Digite a anotação..."
                            @readonly(! $canEditNote)
                        >{{ $taskNote['note'] ?? '' }}</textarea>
                        @if ($notesErrors->has('notes.' . $index . '.note'))
                            <span class="form-page-error">{{ $notesErrors->first('notes.' . $index . '.note') }}</span>
                        @endif
                    </div>

                    <div class="form-page-field form-page-field--full">
                        <label>Anexo da anotação</label>
                        @if ($canEditNote)
                            <input
                                type="file"
                                name="notes[{{ $index }}][attachment]"
                                class="form-page-file {{ $notesErrors->has('notes.' . $index . '.attachment') ? 'is-invalid' : '' }}"
                            >
                        @endif
                        @if (! empty($taskNote['attachment']))
                            <div class="form-page-current-file">
                                <span>Anexo atual:</span>
                                <a href="{{ asset('storage/' . $taskNote['attachment']) }}" target="_blank" rel="noopener">
                                    {{ basename($taskNote['attachment']) }}
                                </a>
                            </div>
                        @elseif (! $canEditNote && $isPersistedNote)
                            <input
                                type="text"
                                value="Nenhum anexo"
                                class="form-page-input--readonly"
                                readonly
                            >
                        @endif
                        @if ($notesErrors->has('notes.' . $index . '.attachment'))
                            <span class="form-page-error">{{ $notesErrors->first('notes.' . $index . '.attachment') }}</span>
                        @endif
                    </div>
                </div>

                @if ($isPersistedNote)
                    @include('histories.partials.audit-list-compact', [
                        'histories' => $noteHistories->get($taskNote['id'], collect()),
                    ])
                @endif

                @if ($canEditNote)
                    @if (! empty($taskNote['id']) && isset($task))
                        <button
                            type="button"
                            class="form-repeater-remove form-repeater-remove--note form-repeater-remove--persisted"
                            data-destroy-url="{{ route('tasks.destroyNote', [$task, $taskNote['id']]) }}"
                            title="Excluir anotação"
                            aria-label="Excluir anotação"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Excluir
                        </button>
                    @else
                        <button type="button" class="form-repeater-remove form-repeater-remove--note" title="Remover anotação" aria-label="Remover anotação">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Remover
                        </button>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
</div>

@unless ($notesLocked)
<template id="task-notes-repeater-template">
    <div class="form-repeater-item form-repeater-item--note">
        <div class="form-repeater-note-grid">
            <div class="form-page-field">
                <label>Usuário</label>
                <input
                    type="text"
                    value="{{ $currentUser->name }}"
                    class="form-page-input--readonly"
                    readonly
                >
            </div>

            <div class="form-page-field form-page-field--full">
                <label>Anotação</label>
                <textarea name="notes[__INDEX__][note]" rows="3" placeholder="Digite a anotação..."></textarea>
            </div>

            <div class="form-page-field form-page-field--full">
                <label>Anexo da anotação</label>
                <input type="file" name="notes[__INDEX__][attachment]" class="form-page-file">
            </div>
        </div>

        <button type="button" class="form-repeater-remove form-repeater-remove--note" title="Remover anotação" aria-label="Remover anotação">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
            Remover
        </button>
    </div>
</template>

<script>
    (function () {
        const list = document.getElementById('task-notes-repeater-list');
        const addButton = document.getElementById('task-notes-repeater-add');
        const template = document.getElementById('task-notes-repeater-template');

        if (!list || !addButton || !template) {
            return;
        }

        let noteIndex = {{ count($taskNotes) }};

        function bindIndex(item, index) {
            item.querySelectorAll('[name]').forEach((field) => {
                field.name = field.name.replace('__INDEX__', String(index));
            });
        }

        addButton.addEventListener('click', () => {
            const fragment = template.content.cloneNode(true);
            const item = fragment.querySelector('.form-repeater-item--note');
            bindIndex(item, noteIndex);
            noteIndex += 1;
            list.appendChild(fragment);
        });

        list.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.form-repeater-remove--note');
            if (!removeButton) {
                return;
            }

            if (removeButton.classList.contains('form-repeater-remove--persisted')) {
                if (!confirm('Tem certeza que deseja excluir esta anotação?')) {
                    return;
                }

                const destroyUrl = removeButton.dataset.destroyUrl;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                if (!destroyUrl || !csrfToken) {
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = destroyUrl;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();

                return;
            }

            const item = removeButton.closest('.form-repeater-item--note');
            if (item) {
                item.remove();
            }
        });
    })();
</script>
@endunless
