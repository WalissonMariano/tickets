<?php

namespace App\Http\Controllers;

use App\Services\TaskStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly TaskStatsService $taskStats
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $statusCounts = $this->taskStats->countByStatus($user);

        $stats = [
            'open' => $statusCounts['open'],
            'in_progress' => $statusCounts['in_progress'],
            'resolved_closed' => $this->taskStats->resolvedOrClosedCount($statusCounts),
            'total' => $this->taskStats->totalTasks($statusCounts),
        ];

        $recentTasks = $this->taskStats->recentTasks($user);

        return view('home.home', compact('stats', 'recentTasks', 'statusCounts'));
    }
}
