@php
    $taskNotes = old('notes');

    if ($taskNotes === null && isset($task)) {
        $taskNotes = $task->notes->map(fn ($note) => [
            'id' => $note->id,
            'user_id' => $note->user_id,
            'note' => $note->note,
            'attachment' => $note->attachment,
        ])->toArray();
    }

    $taskNotes = $taskNotes ?? [];
@endphp

<div class="form-repeater form-repeater--notes form-page-field--full" id="task-notes-repeater">
    <div class="form-repeater-header">
        <div>
            <label class="form-repeater-title">Anotações</label>
            <span class="form-page-hint">Registre o histórico de anotações do chamado.</span>
        </div>
        <button type="button" class="form-repeater-add" id="task-notes-repeater-add">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Adicionar anotação
        </button>
    </div>

    @error('notes')
        <span class="form-page-error">{{ $message }}</span>
    @enderror

    <div class="form-repeater-list form-repeater-list--notes" id="task-notes-repeater-list">
        @foreach ($taskNotes as $index => $taskNote)
            <div class="form-repeater-item form-repeater-item--note">
                @if (! empty($taskNote['id']))
                    <input type="hidden" name="notes[{{ $index }}][id]" value="{{ $taskNote['id'] }}">
                @endif
                @if (! empty($taskNote['attachment']))
                    <input type="hidden" name="notes[{{ $index }}][existing_attachment]" value="{{ $taskNote['attachment'] }}">
                @endif

                <div class="form-repeater-note-grid">
                    <div class="form-page-field">
                        <label>Usuário</label>
                        <select
                            name="notes[{{ $index }}][user_id]"
                            class="form-repeater-select @error('notes.' . $index . '.user_id') is-invalid @enderror"
                        >
                            <option value="">Selecione o usuário</option>
                            @foreach ($users as $user)
                                <option
                                    value="{{ $user->id }}"
                                    @selected((string) ($taskNote['user_id'] ?? '') === (string) $user->id)
                                >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('notes.' . $index . '.user_id')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field form-page-field--full">
                        <label>Anotação</label>
                        <textarea
                            name="notes[{{ $index }}][note]"
                            rows="3"
                            class="@error('notes.' . $index . '.note') is-invalid @enderror"
                            placeholder="Digite a anotação..."
                        >{{ $taskNote['note'] ?? '' }}</textarea>
                        @error('notes.' . $index . '.note')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-page-field form-page-field--full">
                        <label>Anexo da anotação</label>
                        <input
                            type="file"
                            name="notes[{{ $index }}][attachment]"
                            class="form-page-file @error('notes.' . $index . '.attachment') is-invalid @enderror"
                        >
                        @if (! empty($taskNote['attachment']))
                            <div class="form-page-current-file">
                                <span>Anexo atual:</span>
                                <a href="{{ asset('storage/' . $taskNote['attachment']) }}" target="_blank" rel="noopener">
                                    {{ basename($taskNote['attachment']) }}
                                </a>
                            </div>
                        @endif
                        @error('notes.' . $index . '.attachment')
                            <span class="form-page-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <button type="button" class="form-repeater-remove form-repeater-remove--note" title="Remover anotação" aria-label="Remover anotação">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Remover
                </button>
            </div>
        @endforeach
    </div>
</div>

<template id="task-notes-repeater-template">
    <div class="form-repeater-item form-repeater-item--note">
        <div class="form-repeater-note-grid">
            <div class="form-page-field">
                <label>Usuário</label>
                <select name="notes[__INDEX__][user_id]" class="form-repeater-select">
                    <option value="">Selecione o usuário</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
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

            const item = removeButton.closest('.form-repeater-item--note');
            if (item) {
                item.remove();
            }
        });
    })();
</script>
