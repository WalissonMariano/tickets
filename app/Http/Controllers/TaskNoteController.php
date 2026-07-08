<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TaskNoteController extends Controller
{
    //Retorna as regras de validação das anotações
    public static function validationRules(): array
    {
        return [
            'notes' => ['nullable', 'array'],
            'notes.*.id' => ['nullable', 'integer', 'exists:task_notes,id'],
            'notes.*.user_id' => ['required_with:notes.*.note', 'nullable', 'exists:users,id'],
            'notes.*.note' => ['required_with:notes.*.user_id', 'nullable', 'string'],
            'notes.*.attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip'],
        ];
    }

    //Prepara os dados das anotações para o repeater
    public function prepareRepeaterNotes(Request $request, ?Task $task = null): array
    {
        if ($request->old('notes') !== null) {
            return $request->old('notes');
        }

        if ($task) {
            return $task->notes->map(fn (TaskNote $note) => [
                'id' => $note->id,
                'user_id' => $note->user_id,
                'note' => $note->note,
                'attachment' => $note->attachment,
            ])->toArray();
        }

        return [];
    }

    //Redireciona para a edição da tarefa
    public function notes(Task $task): RedirectResponse
    {
        return redirect()->route('tasks.edit', $task);
    }

    //Cria uma nova anotação na tarefa
    public function storeNote(Request $request, Task $task): RedirectResponse
    {
        $this->ensureTaskAcceptsNotes($task);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'note' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip'],
        ]);

        $attachmentPath = $request->hasFile('attachment')
            ? $this->storeNoteAttachment($request->file('attachment'))
            : null;

        $task->notes()->create([
            'user_id' => $validated['user_id'],
            'note' => $validated['note'],
            'attachment' => $attachmentPath,
        ]);

        return back()->with('success', 'Anotação adicionada com sucesso.');
    }

    //Deleta uma anotação da tarefa
    public function destroyNote(Task $task, TaskNote $note): RedirectResponse
    {
        $this->ensureTaskAcceptsNotes($task);

        if ($note->task_id !== $task->id) {
            abort(404);
        }

        $this->deleteFile($note->attachment);
        $note->delete();

        return back()->with('success', 'Anotação excluída com sucesso.');
    }

    //Sincroniza as anotações da tarefa
    public function sync(Task $task, Request $request): void
    {
        $this->ensureTaskAcceptsNotes($task);

        $notes = $request->input('notes', []);
        $submittedIds = [];

        foreach ($notes as $index => $noteData) {
            $noteText = trim($noteData['note'] ?? '');
            $userId = $noteData['user_id'] ?? null;

            if ($noteText === '' && empty($userId)) {
                continue;
            }

            $attachmentPath = $noteData['existing_attachment'] ?? null;
            $file = $request->file("notes.{$index}.attachment");

            if ($file) {
                $this->deleteFile($attachmentPath);
                $attachmentPath = $this->storeNoteAttachment($file);
            }

            $noteId = $noteData['id'] ?? null;

            if ($noteId) {
                $taskNote = TaskNote::where('task_id', $task->id)->find($noteId);

                if ($taskNote) {
                    $taskNote->update([
                        'user_id' => $userId,
                        'note' => $noteText,
                        'attachment' => $attachmentPath,
                    ]);
                    $submittedIds[] = $taskNote->id;
                }

                continue;
            }

            $taskNote = $task->notes()->create([
                'user_id' => $userId,
                'note' => $noteText,
                'attachment' => $attachmentPath,
            ]);

            $submittedIds[] = $taskNote->id;
        }

        $task->notes()
            ->whereNotIn('id', $submittedIds)
            ->get()
            ->each(function (TaskNote $note) {
                $this->deleteFile($note->attachment);
                $note->delete();
            });
    }

    //Remove os arquivos das anotações da tarefa
    public function purgeAttachments(Task $task): void
    {
        $task->notes->each(function (TaskNote $note) {
            $this->deleteFile($note->attachment);
        });
    }

    //Armazena o arquivo da anotação da tarefa
    private function storeNoteAttachment(UploadedFile $file): string
    {
        return $file->store('task-notes/attachments', 'public');
    }

    //Impede anotações em tarefas com status aberto
    private function ensureTaskAcceptsNotes(Task $task): void
    {
        if ($task->status === 'open') {
            abort(403);
        }
    }

    //Deleta o arquivo da anotação da tarefa
    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
