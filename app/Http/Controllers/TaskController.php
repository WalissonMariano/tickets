<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskNote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $query = Task::with(['project', 'reporter', 'assignee'])
            ->orderByDesc('requested_at');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $tasks = $query->paginate(15)->withQueryString();

        return view('tasks.index-tasks', compact('tasks'));
    }

    public function create(): View
    {
        $projects = Project::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('tasks.form-tasks', compact('projects', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeOptionalFields($request);

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks', 'code')->where('project_id', $request->project_id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
            'reporter_id' => ['required', 'exists:users,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'requested_at' => ['required', 'date'],
            'resolved_at' => ['nullable', 'date', 'after_or_equal:requested_at'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip'],
            'notes' => ['nullable', 'array'],
            'notes.*.id' => ['nullable', 'integer', 'exists:task_notes,id'],
            'notes.*.user_id' => ['required_with:notes.*.note', 'nullable', 'exists:users,id'],
            'notes.*.note' => ['required_with:notes.*.user_id', 'nullable', 'string'],
            'notes.*.attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip'],
        ]);

        $attachmentPath = $request->hasFile('attachment')
            ? $this->storeTaskAttachment($request->file('attachment'))
            : null;

        $task = Task::create([
            ...$validated,
            'assignee_id' => $validated['assignee_id'] ?? null,
            'resolved_at' => $validated['resolved_at'] ?? null,
            'attachment' => $attachmentPath,
        ]);

        $this->syncTaskNotes($task, $request);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tarefa cadastrada com sucesso.');
    }

    public function edit(Task $task): View
    {
        $task->load(['notes.user']);
        $projects = Project::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('tasks.form-tasks', compact('task', 'projects', 'users'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->normalizeOptionalFields($request);

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tasks', 'code')
                    ->where('project_id', $request->project_id)
                    ->ignore($task->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['required', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
            'reporter_id' => ['required', 'exists:users,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'requested_at' => ['required', 'date'],
            'resolved_at' => ['nullable', 'date', 'after_or_equal:requested_at'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip'],
            'notes' => ['nullable', 'array'],
            'notes.*.id' => ['nullable', 'integer', 'exists:task_notes,id'],
            'notes.*.user_id' => ['required_with:notes.*.note', 'nullable', 'exists:users,id'],
            'notes.*.note' => ['required_with:notes.*.user_id', 'nullable', 'string'],
            'notes.*.attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip'],
        ]);

        $attachmentPath = $task->attachment;

        if ($request->hasFile('attachment')) {
            $this->deleteFile($task->attachment);
            $attachmentPath = $this->storeTaskAttachment($request->file('attachment'));
        }

        $task->update([
            ...$validated,
            'assignee_id' => $validated['assignee_id'] ?? null,
            'resolved_at' => $validated['resolved_at'] ?? null,
            'attachment' => $attachmentPath,
        ]);

        $this->syncTaskNotes($task, $request);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tarefa atualizada com sucesso.');
    }

    private function normalizeOptionalFields(Request $request): void
    {
        $request->merge([
            'assignee_id' => $request->filled('assignee_id') ? $request->assignee_id : null,
            'resolved_at' => $request->filled('resolved_at') ? $request->resolved_at : null,
        ]);
    }

    private function syncTaskNotes(Task $task, Request $request): void
    {
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

    private function storeTaskAttachment(UploadedFile $file): string
    {
        return $file->store('tasks/attachments', 'public');
    }

    private function storeNoteAttachment(UploadedFile $file): string
    {
        return $file->store('task-notes/attachments', 'public');
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
