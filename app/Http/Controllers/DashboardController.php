<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Project;
use App\Models\Invoice;
use App\Models\TimeEntry;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isClient = $user->isClient();
        $clientId = $user->client_id;

        $projectsQuery = Project::query();
        $invoicesQuery = Invoice::query();

        if ($isClient) {
            $projectsQuery->where('client_id', $clientId);
            $invoicesQuery->where('client_id', $clientId);
        }

        // Calculate completed but unpaid work
        $completedWork = \App\Models\Task::where('status', 'done')
            ->where(function ($query) {
                $query->whereNull('invoice_id')
                    ->orWhereHas('invoice', function ($q) {
                        $q->where('status', '!=', 'paid');
                    });
            });

        if ($isClient) {
            $completedWork->whereHas('project', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

        $completedWorkAmount = $completedWork->with('timeEntries')->get()->sum(function ($task) {
            if ($task->pricing_type === 'fixed') {
                return $task->rate ?? 0;
            } else {
                $hours = $task->timeEntries->sum('duration') / 3600;
                return ($task->rate ?? 0) * $hours;
            }
        });

        $stats = [
            'clients' => $isClient ? 1 : Client::count(),
            'projects' => (clone $projectsQuery)->where('status', 'live')->count(),
            'unpaid_invoices' => (clone $invoicesQuery)->where('status', 'sent')->count(),
            'unpaid_amount' => (clone $invoicesQuery)->where('status', 'sent')->sum('amount'),
            'paid_income' => (clone $invoicesQuery)->where('status', 'paid')->sum('amount'),
            'completed_work' => $completedWorkAmount,
            'active_timer' => TimeEntry::where('user_id', $user->id)->whereNull('end_time')->with('task.project')->first(),
        ];

        $recent_projects = (clone $projectsQuery)->latest()->take(5)->get();
        $recent_invoices = (clone $invoicesQuery)->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recent_projects', 'recent_invoices'));
    }
}
