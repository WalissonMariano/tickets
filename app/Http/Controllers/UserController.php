<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('group')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('register.users.index-users', compact('users'));
    }

    public function create(): View
    {
        $groups = Group::orderBy('description')->get();
        $projects = Project::orderBy('name')->get();

        return view('register.users.form-users', compact('groups', 'projects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeProjectsInput($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'group_id' => ['required', 'exists:groups,id'],
            'is_active' => ['nullable', 'boolean'],
            'projects' => ['nullable', 'array'],
            'projects.*' => ['nullable', 'integer', 'exists:projects,id', 'distinct'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'group_id' => $validated['group_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $user->projects()->sync($validated['projects'] ?? []);

        return redirect()
            ->route('register.users.index')
            ->with('success', 'Usuário cadastrado com sucesso.');
    }

    public function edit(User $user): View
    {
        $user->load('projects');
        $groups = Group::orderBy('description')->get();
        $projects = Project::orderBy('name')->get();

        return view('register.users.form-users', compact('user', 'groups', 'projects'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->normalizeProjectsInput($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'group_id' => ['required', 'exists:groups,id'],
            'is_active' => ['nullable', 'boolean'],
            'projects' => ['nullable', 'array'],
            'projects.*' => ['nullable', 'integer', 'exists:projects,id', 'distinct'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'group_id' => $validated['group_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        $user->projects()->sync($validated['projects'] ?? []);

        return redirect()
            ->route('register.users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    private function normalizeProjectsInput(Request $request): void
    {
        $request->merge([
            'projects' => collect($request->input('projects', []))
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
        ]);
    }
}
