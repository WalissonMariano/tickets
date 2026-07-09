<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Project;
use App\Models\User;
use App\Services\HistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly HistoryService $historyService
    ) {}

    //busca todos os usuários
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

    //Exibe os dados do usuário (conta do usuário logado ou por ID)
    public function show(Request $request, ?User $user = null): View
    {
        $user = ($user ?? $request->user())->load(['group', 'projects']);

        return view('account.index-account', compact('user'));
    }

    //exibe o formulário de criação de usuário
    public function create(): View
    {
        $groups = Group::orderBy('description')->get();
        $projects = Project::orderBy('name')->get();

        return view('register.users.form-users', compact('groups', 'projects'));
    }

    //cria um novo usuário
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
            'password' => bcrypt($validated['password']),
            'group_id' => $validated['group_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $user->projects()->sync($validated['projects'] ?? []);

        $this->historyService->recordCreated($user);

        return redirect()
            ->route('register.users.index')
            ->with('success', 'Usuário cadastrado com sucesso.');
    }

    //exibe o formulário de edição de usuário
    public function edit(User $user): View
    {
        $user->load(['projects', 'auditHistories.user']);
        $groups = Group::orderBy('description')->get();
        $projects = Project::orderBy('name')->get();
        $histories = $user->auditHistories;

        return view('register.users.form-users', compact('user', 'groups', 'projects', 'histories'));
    }

    //atualiza um usuário existente
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

        $this->historyService->recordUpdated($user);

        return redirect()
            ->route('register.users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    //exclui um usuário existente
    public function destroy(User $user): RedirectResponse
    {
        $this->historyService->recordDeleted($user);
        $user->delete();

        return redirect()
            ->route('register.users.index')
            ->with('success', 'Usuário excluído com sucesso.');
    }

    //normaliza o input de projetos
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
