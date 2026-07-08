<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
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

        Project::create([
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('register.projects.index')
            ->with('success', 'Projeto cadastrado com sucesso.');
    }

    //Mostra o formulário de edição de projeto
    public function edit(Project $project): View
    {
        return view('register.projects.form-projects', compact('project'));
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

        return redirect()
            ->route('register.projects.index')
            ->with('success', 'Projeto atualizado com sucesso.');
    }

    //Deleta um projeto
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('register.projects.index')
            ->with('success', 'Projeto deletado com sucesso.');
    }
}
