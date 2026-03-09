<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function data()
    {
        // 1. Income per Month (Last 12 Months)
        $incomeData = Invoice::where('status', 'paid')
            ->where('created_at', '>=', now()->subYear())
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [];
        $income = [];

        // Fill in missing months
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $months[] = $date->format('M Y');

            $found = $incomeData->firstWhere('month', $monthKey);
            $income[] = $found ? $found->total : 0;
        }

        // 2. Hours per Project
        $hoursData = TimeEntry::whereNotNull('end_time')
            ->with('project')
            ->select('project_id', DB::raw('SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) as total_seconds'))
            ->groupBy('project_id')
            ->get()
            ->map(function ($entry) {
                return [
                    'project' => $entry->project ? $entry->project->name : 'Unknown',
                    'hours' => round($entry->total_seconds / 3600, 1)
                ];
            });

        // 3. Most Productive Days (TimeEntries count by Day of Week)
        $daysData = TimeEntry::select(DB::raw('DAYNAME(start_time) as day'), DB::raw('COUNT(*) as count'))
            ->groupBy('day')
            ->orderByRaw('FIELD(day, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")')
            ->get();

        // 4. Task Financials
        $tasksData = \App\Models\Task::with('timeEntries')->get();

        $doneCount = 0;
        $doneValue = 0;
        $potentialCount = 0;
        $potentialValue = 0;

        foreach ($tasksData as $task) {
            if ($task->status === 'done') {
                $doneCount++;
                if ($task->pricing_type === 'fixed') {
                    $doneValue += $task->rate;
                } else {
                    // For hourly, use actual tracked time
                    $hours = $task->timeEntries->sum('duration') / 3600;
                    $doneValue += $task->rate * $hours;
                }
            } else {
                $potentialCount++;
                if ($task->pricing_type === 'fixed') {
                    $potentialValue += $task->rate;
                } else {
                    // For pending/in_progress, use estimated hours
                    $potentialValue += $task->rate * $task->estimated_hours;
                }
            }
        }

        return response()->json([
            'income' => [
                'labels' => $months,
                'data' => $income
            ],
            'projects' => [
                'labels' => $hoursData->pluck('project'),
                'data' => $hoursData->pluck('hours')
            ],
            'days' => [
                'labels' => $daysData->pluck('day'),
                'data' => $daysData->pluck('count')
            ],
            'tasks' => [
                'done_count' => $doneCount,
                'done_value' => round($doneValue, 2),
                'potential_count' => $potentialCount,
                'potential_value' => round($potentialValue, 2)
            ]
        ]);
    }

    public function taskProgress(Request $request)
    {
        $dateFrom = $request->input('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $dateTo = $request->input('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfDay();

        $clientId = $request->input('client_id');
        $clients = \App\Models\Client::orderBy('name')->get();

        // Build project query with optional client filter
        $query = Project::with([
            'client',
            'tasks' => function ($q) use ($dateFrom, $dateTo) {
                $q->where(function ($sub) use ($dateFrom, $dateTo) {
                    $sub->whereBetween('updated_at', [$dateFrom, $dateTo])
                        ->orWhereBetween('created_at', [$dateFrom, $dateTo]);
                });
            }
        ]);

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $projects = $query->get()
            ->filter(fn($p) => $p->tasks->count() > 0)
            ->map(function ($project) {
                $tasks = $project->tasks;
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'domain' => $project->domain,
                    'status' => $project->status,
                    'client' => $project->client?->name,
                    'tasks' => $tasks,
                    'done' => $tasks->where('status', 'done')->count(),
                    'in_progress' => $tasks->where('status', 'in_progress')->count(),
                    'pending' => $tasks->where('status', 'pending')->count(),
                    'total' => $tasks->count(),
                ];
            })->values();

        $summary = [
            'projects' => $projects->count(),
            'total_tasks' => $projects->sum('total'),
            'done' => $projects->sum('done'),
            'in_progress' => $projects->sum('in_progress'),
            'pending' => $projects->sum('pending'),
        ];

        return view('reports.task-progress', compact(
            'projects',
            'summary',
            'dateFrom',
            'dateTo',
            'clients',
            'clientId'
        ));
    }
}
