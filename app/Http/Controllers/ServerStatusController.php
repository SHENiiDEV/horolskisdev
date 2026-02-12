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
                // Reverse checks for chronological display
                $project->setRelation('uptimeChecks', $project->uptimeChecks->reverse());
                return $project;
            });

        return view('server-status.index', compact('projects'));
    }
}
