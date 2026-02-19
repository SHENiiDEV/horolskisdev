<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ServerStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Project::where('is_monitoring_enabled', true)
            ->whereNotNull('domain')
            ->with([
                'uptimeChecks' => function ($q) {
                    $q->latest()->limit(60);
                }
            ])
            ->withAvg('uptimeChecks', 'response_time_ms')
            ->withMin('uptimeChecks', 'response_time_ms')
            ->withMax('uptimeChecks', 'response_time_ms');

        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->isClient()) {
            $query->where('client_id', $user->client_id);
        }

        $projects = $query->orderByRaw("CASE WHEN last_check_status = 'down' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->map(function (\App\Models\Project $project) {
                // Calculate stats in memory from the last 60 checks
                $checks = $project->uptimeChecks;
                $project->uptime_checks_avg_response_time_ms = $checks->avg('response_time_ms');
                $project->uptime_checks_min_response_time_ms = $checks->min('response_time_ms');
                $project->uptime_checks_max_response_time_ms = $checks->max('response_time_ms');

                // Reverse checks for chronological display (oldest -> newest)
                $project->setRelation('uptimeChecks', $project->uptimeChecks->reverse());
                return $project;
            });

        return view('server-status.index', compact('projects'));
    }
}
