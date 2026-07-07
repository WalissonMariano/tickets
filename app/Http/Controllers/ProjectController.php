<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
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

    public function create(): View
    {
        return view('register.projects.form-projects');
    }

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

    public function edit(Project $project): View
    {
        return view('register.projects.form-projects', compact('project'));
    }

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
}
