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
            ->whereNotNull('domain');

        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->isClient()) {
            $query->where('client_id', $user->client_id);
        }

        $projects = $query->orderByRaw("CASE WHEN last_check_status = 'down' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        return view('server-status.index', compact('projects'));
    }
}
