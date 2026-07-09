<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\HistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private readonly HistoryService $historyService
    ) {}
    //Busca todos os projetos
    public function index(Request $request): View
    {
        $query = Project::withCount(['users', 'tasks'])->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where('name', 'like', "%{$search}%");
        }

        $projects = $query->paginate(15)->withQueryString();

        return view('register.projects.index-projects', compact('projects'));
    }

    //Mostra o formulário de criação de projeto
    public function create(): View
    {
        return view('register.projects.form-projects');
    }

    //Cria um novo projeto
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:projects,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->historyService->recordCreated($project);

        return redirect()
            ->route('register.projects.index')
            ->with('success', 'Projeto cadastrado com sucesso.');
    }

    //Mostra o formulário de edição de projeto
    public function edit(Project $project): View
    {
        $project->load(['auditHistories.user']);

        return view('register.projects.form-projects', [
            'project' => $project,
            'histories' => $project->auditHistories,
        ]);
    }

    //Atualiza um projeto
    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('projects', 'name')->ignore($project->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $project->update([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->historyService->recordUpdated($project);

        return redirect()
            ->route('register.projects.index')
            ->with('success', 'Projeto atualizado com sucesso.');
    }

    //Deleta um projeto
    public function destroy(Project $project): RedirectResponse
    {
        $this->historyService->recordDeleted($project);
        $project->delete();

        return redirect()
            ->route('register.projects.index')
            ->with('success', 'Projeto deletado com sucesso.');
    }
}
