<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View
    {
        $query = Group::withCount('users')->orderBy('code');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $groups = $query->paginate(15)->withQueryString();

        return view('register.groups.index-groups', compact('groups'));
    }

    public function create(): View
    {
        return view('register.groups.form-groups');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:groups,code'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        Group::create($validated);

        return redirect()
            ->route('register.groups.index')
            ->with('success', 'Grupo cadastrado com sucesso.');
    }

    public function edit(Group $group): View
    {
        return view('register.groups.form-groups', compact('group'));
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', Rule::unique('groups', 'code')->ignore($group->id)],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $group->update($validated);

        return redirect()
            ->route('register.groups.index')
            ->with('success', 'Grupo atualizado com sucesso.');
    }
}
