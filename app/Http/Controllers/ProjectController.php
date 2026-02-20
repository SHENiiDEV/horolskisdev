<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Project::with('client')->withCount('tasks')->latest();

        if (auth()->user()->isClient()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($qClient) use ($search) {
                        $qClient->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $projects = $query->paginate(15)->withQueryString();

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Clients cannot create projects
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $clients = \App\Models\Client::orderBy('name')->pluck('name', 'id');
        return view('projects.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'status' => 'required|in:development,live,paused',
            'pricing_type' => 'required|in:fixed,hourly',
        ]);

        $project = \App\Models\Project::create($validated);

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(\App\Models\Project $project)
    {
        if (auth()->user()->isClient() && $project->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $project->load([
            'tasks' => function ($query) {
                $query->latest();
            },
            'vaults',
            'integrations',
            'plugins',
            'uptimeChecks' => function ($query) {
                $query->latest()->limit(60); // Get last 60 checks (1 hour)
            }
        ]);

        // Calculate stats in memory from the last 60 checks to avoid full-table scans
        $checks = $project->uptimeChecks;
        $project->uptime_checks_avg_response_time_ms = $checks->avg('response_time_ms');
        $project->uptime_checks_min_response_time_ms = $checks->min('response_time_ms');
        $project->uptime_checks_max_response_time_ms = $checks->max('response_time_ms');

        // Reverse checks for chronological display
        $project->setRelation('uptimeChecks', $project->uptimeChecks->reverse());

        // Fetch Vault Share Access Logs
        $accessLogs = \App\Models\VaultShareAccess::whereHas('vaultShare', function ($q) use ($project) {
            $q->where('project_id', $project->id);
        })->with('vaultShare')->latest()->take(50)->get();

        return view('projects.show', compact('project', 'accessLogs')); // Updated compact and removed activeTimer
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(\App\Models\Project $project)
    {
        // Clients cannot edit projects
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $clients = \App\Models\Client::orderBy('name')->pluck('name', 'id');
        return view('projects.edit', compact('project', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Project $project)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'status' => 'required|in:development,live,paused',
            'pricing_type' => 'required|in:fixed,hourly',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Project $project)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    public function importVault(Request $request, \App\Models\Project $project, \App\Services\GoogleSheetVaultImporter $importer)
    {
        $request->validate([
            'sheet_url' => 'required|url|active_url',
        ]);

        try {
            $count = $importer->import($request->sheet_url, $project);
            return back()->with('success', "Successfully imported {$count} vault entries.");
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
