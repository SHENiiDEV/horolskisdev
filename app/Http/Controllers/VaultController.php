<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VaultController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(\Illuminate\Http\Request $request)
    {
        $project_id = $request->query('project_id');
        $project = \App\Models\Project::findOrFail($project_id);

        if (auth()->user()->isClient() && $project->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        return view('vaults.create', compact('project'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:wordpress,ssh,hosting,gmail,database,custom,ftp',
            'payload' => 'required|array', // We will store dynamic fields here
        ]);

        $project = \App\Models\Project::find($validated['project_id']);
        if (auth()->user()->isClient() && $project->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        // Normalize payload values
        $validated['payload'] = collect($validated['payload'])->map(function ($item) {
            return [
                'name' => $item['name'] ?? '',
                'value' => $item['value'] ?? '',
                'is_secret' => filter_var($item['is_secret'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ];
        })->values()->all();

        \App\Models\Vault::create($validated);

        return redirect()->route('projects.show', $validated['project_id'])->with('success', 'Credential added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vault = \App\Models\Vault::findOrFail($id);
        if (auth()->user()->isClient() && $vault->project->client_id !== auth()->user()->client_id) {
            abort(403);
        }
        return view('vaults.edit', compact('vault'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $vault = \App\Models\Vault::findOrFail($id);
        if (auth()->user()->isClient() && $vault->project->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:wordpress,ssh,hosting,gmail,database,custom,ftp',
            'payload' => 'required|array',
        ]);

        // Normalize payload values
        $payload = collect($validated['payload'])->map(function ($item) {
            return [
                'name' => $item['name'] ?? '',
                'value' => $item['value'] ?? '',
                'is_secret' => filter_var($item['is_secret'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ];
        })->values()->all();

        $vault->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'payload' => $payload
        ]);

        return redirect()->route('projects.show', $vault->project_id)->with('success', 'Credential updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vault = \App\Models\Vault::findOrFail($id);
        if (auth()->user()->isClient() && $vault->project->client_id !== auth()->user()->client_id) {
            abort(403);
        }
        $project_id = $vault->project_id;
        $vault->delete();

        return redirect()->route('projects.show', $project_id)->with('success', 'Credential deleted successfully.');
    }
}
