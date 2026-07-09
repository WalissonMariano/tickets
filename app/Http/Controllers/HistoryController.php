<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->loadMissing('group')->isAdmin(), 403);

        $query = History::query()
            ->with([
                'user',
                'auditable' => fn ($morphTo) => $morphTo->morphWith([
                    Task::class => ['project'],
                    TaskNote::class => ['task'],
                ]),
            ])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($userQuery) => $userQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"))
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('auditable_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $type = match ($request->string('type')->toString()) {
                'user' => User::class,
                'project' => Project::class,
                'task' => Task::class,
                'task_note' => TaskNote::class,
                default => null,
            };

            if ($type) {
                $query->where('auditable_type', $type);
            }
        }

        if ($request->filled('action')) {
            $query->where('action', $request->string('action')->toString());
        }

        $histories = $query->paginate(20)->withQueryString();

        return view('histories.index-histories', compact('histories'));
    }
}
