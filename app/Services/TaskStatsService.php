<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TaskStatsService
{
    public function tasksForUser(User $user): Builder
    {
        $projectIds = $user->projects()->pluck('projects.id');

        return Task::query()->whereIn('project_id', $projectIds);
    }

    public function countByStatus(User $user): array
    {
        $counts = $this->tasksForUser($user)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'open' => (int) ($counts['open'] ?? 0),
            'in_progress' => (int) ($counts['in_progress'] ?? 0),
            'resolved' => (int) ($counts['resolved'] ?? 0),
            'closed' => (int) ($counts['closed'] ?? 0),
        ];
    }

    public function countBySeverity(User $user): array
    {
        $counts = $this->tasksForUser($user)
            ->selectRaw('severity, count(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');

        return [
            'low' => (int) ($counts['low'] ?? 0),
            'medium' => (int) ($counts['medium'] ?? 0),
            'high' => (int) ($counts['high'] ?? 0),
            'critical' => (int) ($counts['critical'] ?? 0),
        ];
    }

    public function totalTasks(array $statusCounts): int
    {
        return array_sum($statusCounts);
    }

    public function resolvedOrClosedCount(array $statusCounts): int
    {
        return $statusCounts['resolved'] + $statusCounts['closed'];
    }

    public function resolutionRate(array $statusCounts): int
    {
        $total = $this->totalTasks($statusCounts);

        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->resolvedOrClosedCount($statusCounts) / $total) * 100);
    }

    public function averageResolutionDays(User $user): ?float
    {
        $tasks = $this->tasksForUser($user)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereNotNull('resolved_at')
            ->whereNotNull('requested_at')
            ->get(['requested_at', 'resolved_at']);

        if ($tasks->isEmpty()) {
            return null;
        }

        $totalDays = $tasks->sum(
            fn (Task $task) => $task->requested_at->diffInMinutes($task->resolved_at) / 1440
        );

        return round($totalDays / $tasks->count(), 1);
    }

    public function recentTasks(User $user, int $limit = 5): Collection
    {
        return $this->tasksForUser($user)
            ->with('project')
            ->orderByDesc('requested_at')
            ->limit($limit)
            ->get();
    }

    public function weeklyActivity(User $user): array
    {
        $start = now()->startOfWeek(Carbon::MONDAY);

        $counts = $this->tasksForUser($user)
            ->where('requested_at', '>=', $start)
            ->get(['requested_at'])
            ->groupBy(fn (Task $task) => $task->requested_at->toDateString())
            ->map(fn (Collection $tasks) => $tasks->count());

        $days = [];
        $labels = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $dayKey = $date->toDateString();

            $days[] = [
                'label' => $labels[$i],
                'count' => (int) ($counts->get($dayKey) ?? 0),
            ];
        }

        $max = max(array_column($days, 'count')) ?: 1;

        return array_map(function (array $day) use ($max) {
            $day['height'] = (int) round(($day['count'] / $max) * 100);

            return $day;
        }, $days);
    }

    public function statusBarWidths(array $statusCounts): array
    {
        $max = max($statusCounts) ?: 1;

        return array_map(
            fn (int $count) => (int) round(($count / $max) * 100),
            $statusCounts
        );
    }

    public function activeProjectsCount(User $user): int
    {
        return $user->projects()->where('projects.is_active', true)->count();
    }

    public function activeUsersCount(): int
    {
        return User::query()->where('is_active', true)->count();
    }

    public function formatAverageResolutionDays(?float $days): string
    {
        if ($days === null) {
            return '—';
        }

        return number_format($days, 1, ',', '.') . 'd';
    }
}
