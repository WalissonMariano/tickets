<?php

namespace App\Http\Controllers;

use App\Services\TaskStatsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly TaskStatsService $taskStats
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $statusCounts = $this->taskStats->countByStatus($user);
        $severityCounts = $this->taskStats->countBySeverity($user);

        $stats = [
            'resolution_rate' => $this->taskStats->resolutionRate($statusCounts),
            'average_resolution' => $this->taskStats->formatAverageResolutionDays(
                $this->taskStats->averageResolutionDays($user)
            ),
            'active_users' => $this->taskStats->activeUsersCount(),
            'active_projects' => $this->taskStats->activeProjectsCount($user),
        ];

        $statusBars = $this->taskStats->statusBarWidths($statusCounts);
        $weeklyActivity = $this->taskStats->weeklyActivity($user);

        return view('dashboard.dashboard', compact(
            'stats',
            'statusCounts',
            'statusBars',
            'severityCounts',
            'weeklyActivity',
        ));
    }
}
