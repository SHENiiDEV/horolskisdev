<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = \App\Models\Project::with('client')->withCount('tasks')->latest();

        if (auth()->user()->isClient()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        $projects = $query->paginate(15);
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

        // Reverse checks to show oldest to newest (left to right)
        $uptimeChecks = $project->uptimeChecks->reverse();

        return view('projects.show', compact('project', 'uptimeChecks')); // Updated compact and removed activeTimer
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
    public function destroy(string $id)
    {
        //
    }
}
