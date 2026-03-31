<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Models\PluginVersion;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PluginController extends Controller
{
    public function index()
    {
        $plugins = Plugin::with('latestVersion')->get();
        // If user is client, maybe show only plugins assigned to their projects? 
        // For now, assuming admin manages plugins.
        if (auth()->user()->isClient()) {
            // For client, show plugins available in their projects? Or just block access?
            // "Clients will plugins loaded in their projects". 
            // Let's restrict management to Admin for now, but Client can see their project plugins if needed.
            // Requirement says: "I can see all plugins uploaded, in which projects they are".
            // Implementation plan: Admin manages plugins.
        }

        return view('plugins.index', compact('plugins'));
    }

    public function create()
    {
        return view('plugins.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'version' => 'required|string|max:50',
            'file' => 'required|file', // Removed mimes to avoid issues, or can keep original
            'changelog' => 'nullable|string',
            'enable_auto_update' => 'nullable|boolean',
            'update_endpoint' => 'nullable|string|max:255',
            'revert_endpoint' => 'nullable|string|max:255',
            'update_secret_key' => 'nullable|string|max:255',
        ]);

        $plugin = Plugin::create([
            'name' => $request->name,
            'description' => $request->description,
            'enable_auto_update' => $request->boolean('enable_auto_update'),
            'update_endpoint' => $request->update_endpoint,
            'revert_endpoint' => $request->revert_endpoint,
            'update_secret_key' => $request->update_secret_key,
        ]);

        $path = $request->file('file')->store('plugins');

        $plugin->versions()->create([
            'version' => $request->version,
            'file_path' => $path,
            'changelog' => $request->changelog,
        ]);

        return redirect()->route('plugins.index')->with('success', 'Plugin created successfully.');
    }

    public function show(Plugin $plugin)
    {
        $plugin->load('versions', 'projects');
        return view('plugins.show', compact('plugin'));
    }

    public function update(Request $request, Plugin $plugin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'enable_auto_update' => 'nullable|boolean',
            'update_endpoint' => 'nullable|string|max:255',
            'revert_endpoint' => 'nullable|string|max:255',
            'update_secret_key' => 'nullable|string|max:255',
        ]);

        $plugin->update([
            'name' => $request->name,
            'description' => $request->description,
            'enable_auto_update' => $request->boolean('enable_auto_update'),
            'update_endpoint' => $request->update_endpoint,
            'revert_endpoint' => $request->revert_endpoint,
            'update_secret_key' => $request->update_secret_key,
        ]);

        return back()->with('success', 'Plugin updated successfully.');
    }

    public function uploadVersion(Request $request, Plugin $plugin)
    {
        $request->validate([
            'version' => 'required|string|max:50',
            'file' => 'required|file',
            'changelog' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('plugins');

        $plugin->versions()->create([
            'version' => $request->version,
            'file_path' => $path,
            'changelog' => $request->changelog,
        ]);

        if ($plugin->enable_auto_update) {
            \App\Jobs\PushPluginUpdateJob::dispatch($plugin);
        }

        return back()->with('success', 'New version uploaded.');
    }

    public function revertAll(Plugin $plugin)
    {
        if (!$plugin->revert_endpoint) {
            return back()->with('error', 'Revert endpoint is not configured for this plugin.');
        }

        \App\Jobs\PushPluginRevertJob::dispatch($plugin);

        return back()->with('success', 'Revert job dispatched for all projects.');
    }

    public function assignToProject(Request $request, Plugin $plugin)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        $latestVersion = $plugin->latestVersion;
        if (!$latestVersion) {
            return back()->with('error', 'Plugin has no versions.');
        }

        // Attach or sync? Pivot has unique constraint on project_id + plugin_id
        // We attach specifically version_id
        $plugin->projects()->syncWithoutDetaching([
            $request->project_id => ['plugin_version_id' => $latestVersion->id]
        ]);

        return back()->with('success', 'Plugin assigned to project.');
    }

    public function upgradeProjectPlugin(Request $request, Project $project, Plugin $plugin)
    {
        $latestVersion = $plugin->latestVersion;
        if (!$latestVersion) {
            return back()->with('error', 'Plugin has no versions.');
        }

        $project->plugins()->updateExistingPivot($plugin->id, [
            'plugin_version_id' => $latestVersion->id
        ]);

        return back()->with('success', 'Project plugin upgraded to version ' . $latestVersion->version);
    }

    public function removeProjectPlugin(Project $project, Plugin $plugin)
    {
        $project->plugins()->detach($plugin->id);
        return back()->with('success', 'Plugin removed from project.');
    }

    public function downloadVersion(PluginVersion $pluginVersion)
    {
        if (!Storage::exists($pluginVersion->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::download($pluginVersion->file_path, $pluginVersion->plugin->name . '-v' . $pluginVersion->version . '.zip');
    }
}
