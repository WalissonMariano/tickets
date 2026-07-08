<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskNoteController $taskNoteController
    ) {}

    //Busca todas as tarefas
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

    //Mostra o formulário de criação de tarefa
    public function create(Request $request): View
    {
        $projects = Project::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $taskNotes = $this->taskNoteController->prepareRepeaterNotes($request);

        return view('tasks.form-tasks', compact('projects', 'users', 'taskNotes'));
    }

    //Cria uma nova tarefa
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip'],
        ]);

        $attachmentPath = $request->hasFile('attachment')
            ? $this->storeTaskAttachment($request->file('attachment'))
            : null;

        $assignToMe = $request->has('assign_to_me');

        $task = Task::create([
            ...$validated,
            'code' => $this->generateNextCode($validated['project_id']),
            'reporter_id' => $request->user()->id,
            'assignee_id' => $assignToMe ? $request->user()->id : null,
            'requested_at' => now(),
            'resolved_at' => null,
            'status' => $assignToMe ? 'in_progress' : 'open',
            'attachment' => $attachmentPath,
        ]);

        return redirect()
            ->route('tasks.index')
            ->with('success', $assignToMe
                ? 'Tarefa cadastrada, atribuída a você e colocada em andamento.'
                : 'Tarefa cadastrada com sucesso.');
    }

    //Mostra o formulário de edição de tarefa
    public function edit(Request $request, Task $task): View
    {
        $task->load(['notes.user', 'reporter', 'assignee']);
        $projects = Project::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $taskNotes = $task->status !== 'open'
            ? $this->taskNoteController->prepareRepeaterNotes($request, $task)
            : [];

        return view('tasks.form-tasks', compact('task', 'projects', 'users', 'taskNotes'));
    }

    //Atualiza uma tarefa existente
    public function update(Request $request, Task $task): RedirectResponse
    {
        if ((int) $request->project_id !== (int) $task->project_id
            && Task::where('project_id', $request->project_id)->where('code', $task->code)->exists()) {
            return back()
                ->withErrors(['project_id' => 'Já existe uma tarefa com este código no projeto selecionado.'])
                ->withInput();
        }

        $rules = [
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip'],
        ];

        if ($task->status !== 'open') {
            $rules = array_merge($rules, TaskNoteController::validationRules());
        }

        $validated = $request->validate($rules);

        $attachmentPath = $task->attachment;

        if ($request->hasFile('attachment')) {
            $this->deleteFile($task->attachment);
            $attachmentPath = $this->storeTaskAttachment($request->file('attachment'));
        }

        $assignToMe = $request->has('assign_to_me') && $task->status === 'open';
        $markResolved = $request->has('mark_resolved') && $task->status === 'in_progress';
        $markClosed = $request->has('mark_closed') && $task->status === 'resolved';

        $task->update([
            'project_id' => $validated['project_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'severity' => $validated['severity'],
            'attachment' => $attachmentPath,
            ...($assignToMe ? [
                'assignee_id' => $request->user()->id,
                'status' => 'in_progress',
            ] : []),
            ...($markResolved ? [
                'status' => 'resolved',
                'resolved_at' => now(),
            ] : []),
            ...($markClosed ? [
                'status' => 'closed',
            ] : []),
        ]);

        if ($task->status !== 'open') {
            $this->taskNoteController->sync($task, $request);
        }

        $successMessage = match (true) {
            $markClosed => 'Tarefa fechada com sucesso.',
            $markResolved => 'Tarefa marcada como resolvida.',
            $assignToMe => 'Tarefa atribuída a você e colocada em andamento.',
            default => 'Tarefa atualizada com sucesso.',
        };

        return redirect()
            ->route('tasks.index')
            ->with('success', $successMessage);
    }

    //Deleta uma tarefa
    public function destroy(Task $task): RedirectResponse
    {
        $task->load('notes');

        $this->deleteFile($task->attachment);
        $this->taskNoteController->purgeAttachments($task);
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tarefa excluída com sucesso.');
    }

    //Gera o próximo código sequencial da tarefa no projeto
    private function generateNextCode(int $projectId): string
    {
        return (string) DB::transaction(function () use ($projectId) {
            $maxCode = Task::where('project_id', $projectId)
                ->lockForUpdate()
                ->pluck('code')
                ->map(fn (string $code) => ctype_digit($code) ? (int) $code : 0)
                ->max() ?? 0;

            return (string) ($maxCode + 1);
        });
    }

    //Armazena o arquivo da tarefa
    private function storeTaskAttachment(UploadedFile $file): string
    {
        return $file->store('tasks/attachments', 'public');
    }

    //Deleta o arquivo da tarefa
    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
