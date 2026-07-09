<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Task;
use App\Models\User;
use App\Services\HistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskNoteController $taskNoteController,
        private readonly HistoryService $historyService
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
        $projects = $this->projectsForUser($request);
        $users = User::orderBy('name')->get();

        return view('tasks.form-tasks', compact('projects', 'users'));
    }

    //Cria uma nova tarefa
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id', Rule::in($this->userProjectIds($request))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip'],
        ]);

        $attachmentPath = $request->hasFile('attachment')
            ? $this->storeTaskAttachment($request->file('attachment'))
            : null;

        $assignToMe = $request->has('assign_to_me') && $request->user()->canAssignTasks();

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

        $this->historyService->recordCreated($task);

        if ($assignToMe) {
            $this->historyService->record($task, History::ACTION_ASSIGNED);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', $assignToMe
                ? 'Tarefa cadastrada, atribuída a você e colocada em andamento.'
                : 'Tarefa cadastrada com sucesso.');
    }

    //Mostra o formulário de edição de tarefa
    public function edit(Request $request, Task $task): View
    {
        $task->load(['notes.user', 'notes.auditHistories.user', 'reporter', 'assignee', 'project', 'auditHistories.user']);
        $projects = $this->projectsForUser($request, $task);
        $users = User::orderBy('name')->get();
        $taskNotes = $task->status !== 'open'
            ? $this->taskNoteController->prepareRepeaterNotes($request, $task)
            : [];
        $histories = $task->auditHistories;
        $noteHistories = $task->notes->mapWithKeys(
            fn ($note) => [$note->id => $note->auditHistories]
        );

        return view('tasks.form-tasks', compact('task', 'projects', 'users', 'taskNotes', 'histories', 'noteHistories'));
    }

    //Atualiza uma tarefa existente
    public function update(Request $request, Task $task): RedirectResponse
    {
        $assignToMe = $request->has('assign_to_me')
            && $task->status === 'open'
            && $request->user()->canAssignTasks();
        $markResolved = $request->has('mark_resolved')
            && $task->status === 'in_progress'
            && $request->user()->canAssignTasks();
        $markClosed = $request->has('mark_closed') && $task->status === 'resolved';

        if ($task->status !== 'open') {
            if ($markResolved) {
                $task->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                ]);

                $this->historyService->record($task, History::ACTION_RESOLVED);

                return redirect()
                    ->route('tasks.index')
                    ->with('success', 'Tarefa marcada como resolvida.');
            }

            if ($markClosed) {
                $task->update([
                    'status' => 'closed',
                ]);

                $this->historyService->record($task, History::ACTION_CLOSED);

                return redirect()
                    ->route('tasks.index')
                    ->with('success', 'Tarefa fechada com sucesso.');
            }

            return back()->with('success', 'Nenhuma alteração foi realizada na tarefa.');
        }

        if ((int) $request->project_id !== (int) $task->project_id
            && Task::where('project_id', $request->project_id)->where('code', $task->code)->exists()) {
            return back()
                ->withErrors(['project_id' => 'Já existe uma tarefa com este código no projeto selecionado.'])
                ->withInput();
        }

        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id', Rule::in($this->userProjectIds($request, $task))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'severity' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip'],
        ]);

        $attachmentPath = $task->attachment;

        if ($request->hasFile('attachment')) {
            $this->deleteFile($task->attachment);
            $attachmentPath = $this->storeTaskAttachment($request->file('attachment'));
        }

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
        ]);

        if ($assignToMe) {
            $this->historyService->record($task, History::ACTION_ASSIGNED);
        } else {
            $this->historyService->recordUpdated($task);
        }

        $successMessage = $assignToMe
            ? 'Tarefa atribuída a você e colocada em andamento.'
            : 'Tarefa atualizada com sucesso.';

        return redirect()
            ->route('tasks.index')
            ->with('success', $successMessage);
    }

    //Deleta uma tarefa
    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $request->user()->loadMissing('group');

        if (! $request->user()->canDeleteTask($task)) {
            abort(403);
        }

        $task->load('notes');

        $this->deleteFile($task->attachment);
        $this->taskNoteController->purgeAttachments($task);
        $this->historyService->recordDeleted($task);
        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tarefa excluída com sucesso.');
    }

    //Gera o próximo código sequencial da tarefa no projeto
    private function projectsForUser(Request $request, ?Task $task = null): Collection
    {
        $projects = $request->user()->projects()->orderBy('projects.name')->get();

        if ($task?->project_id && ! $projects->contains('id', $task->project_id)) {
            $task->loadMissing('project');

            if ($task->project) {
                $projects = $projects->push($task->project)->sortBy('name')->values();
            }
        }

        return $projects;
    }

    private function userProjectIds(Request $request, ?Task $task = null): array
    {
        $ids = $request->user()->projects()->pluck('projects.id')->all();

        if ($task?->project_id && ! in_array($task->project_id, $ids, true)) {
            $ids[] = $task->project_id;
        }

        return $ids;
    }

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
