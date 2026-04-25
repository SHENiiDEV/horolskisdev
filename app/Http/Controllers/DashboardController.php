<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\TimeEntry;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isClient = $user->isClient();
        $clientId = $user->client_id;

        // Date Filtering
        $period = $request->query('period', 'this_month');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $startDate = null;
        $endDate = null;

        if ($period === 'this_month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($period === 'last_month') {
            $startDate = now()->subMonth()->startOfMonth();
            $endDate = now()->subMonth()->endOfMonth();
        } elseif ($period === 'custom' && $dateFrom && $dateTo) {
            $startDate = \Carbon\Carbon::parse($dateFrom)->startOfDay();
            $endDate = \Carbon\Carbon::parse($dateTo)->endOfDay();
        }
        // 'all_time' leaves dates as null

        $applyDateFilter = function ($query, $column = 'created_at') use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween($column, [$startDate, $endDate]);
            }
        };

        $projectsQuery = Project::query();
        $invoicesQuery = Invoice::query();

        $applyDateFilter($invoicesQuery, 'created_at'); // Filter invoices by creation date? Or paid date for paid ones?
        // Usually dashboard overview shows "Invoices created in this period". 
        // But for "Paid Income", we want "Paid in this period". 
        // Let's keep general counts filtered by creation.

        if ($isClient) {
            $projectsQuery->where('client_id', $clientId);
            $invoicesQuery->where('client_id', $clientId);
        }

        // Helper to calculate task value
        $calculateTaskValue = function ($tasks) {
            return $tasks->sum(function ($task) {
                if ($task->pricing_type === 'fixed') {
                    return $task->rate ?? 0;
                } else {
                    $hours = $task->timeEntries->sum('duration') / 3600;
                    return ($task->rate ?? 0) * $hours;
                }
            });
        };

        // 1. Paid Tasks (Done + Invoice Paid)
        // Filter by Invoice updated_at (approx paid date)
        $paidTasksQuery = \App\Models\Task::where('status', 'done')
            ->whereHas('invoice', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'paid');
                if ($startDate && $endDate) {
                    $q->whereBetween('updated_at', [$startDate, $endDate]);
                }
            })
            ->with('timeEntries');

        // 2. Completed Tasks (Done + Not Paid)
        // Filter by Task updated_at (completion date)
        $completedUnpaidTasksQuery = \App\Models\Task::where('status', 'done')
            ->where(function ($query) {
                $query->whereNull('invoice_id')
                    ->orWhereHas('invoice', function ($q) {
                        $q->where('status', '!=', 'paid');
                    });
            })
            ->with('timeEntries');

        $applyDateFilter($completedUnpaidTasksQuery, 'updated_at');

        $potentialQuery = \App\Models\Task::whereIn('status', ['pending', 'in_progress'])
            ->with('timeEntries');

        if ($endDate) {
            $potentialQuery->where('created_at', '<=', $endDate);
        }

        if ($isClient) {
            $paidTasksQuery->whereHas('project', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
            $completedUnpaidTasksQuery->whereHas('project', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
            $potentialQuery->whereHas('project', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

        $paidTasksValue = $calculateTaskValue($paidTasksQuery->get());
        $completedUnpaidTasksValue = $calculateTaskValue($completedUnpaidTasksQuery->get());

        // Custom calculator for potential to include estimated hours
        $potentialEarnings = $potentialQuery->get()->sum(function ($task) {
            if ($task->pricing_type === 'fixed') {
                return $task->rate ?? 0;
            } else {
                // For potential, if no time logged, use estimated
                $seconds = $task->timeEntries->sum('duration');
                if ($seconds > 0) {
                    $hours = $seconds / 3600;
                } else {
                    $hours = $task->estimated_hours ?? 0;
                }
                return ($task->rate ?? 0) * $hours;
            }
        });

        // Other aggregates (filtered by creation date generally)
        $stats = [
            'clients' => $isClient ? 1 : Client::count(),
            'projects' => (clone $projectsQuery)->where('status', 'live')->count(), // Projects usually long running, maybe don't filter?
            'unpaid_invoices' => (clone $invoicesQuery)->where('status', 'sent')->count(),
            'unpaid_amount' => (clone $invoicesQuery)->where('status', 'sent')->sum('amount'), // This is mostly for "Current Outstanding", maybe shouldn't be filtered by date?

            // New Metrics
            'paid_tasks_value' => $paidTasksValue,
            'completed_unpaid_tasks_value' => $completedUnpaidTasksValue,
            'potential_earnings' => $potentialEarnings,

            'active_timer' => TimeEntry::where('user_id', $user->id)->whereNull('end_time')->with('task.project')->first(),
        ];

        // Decide if we filter "Unpaid Amount" by date. 
        // "Unpaid Invoices" usually means "Currently Unpaid". 
        // If I say "Last Month", do I mean "Invoices created last month that are still unpaid"? 
        // Yes, likely. 
        // The $invoicesQuery has filter applied above at $applyDateFilter($invoicesQuery, 'created_at');

        $recent_projects = (clone $projectsQuery)->latest()->take(5)->get();
        $recent_invoices = (clone $invoicesQuery)->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recent_projects', 'recent_invoices', 'period', 'dateFrom', 'dateTo'));
    }
}
