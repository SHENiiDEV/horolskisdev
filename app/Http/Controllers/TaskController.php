<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function kanban()
    {
        $query = \App\Models\Task::with(['project.client', 'timeEntries', 'invoice']);

        if (auth()->user()->isClient()) {
            $query->whereHas('project', function ($q) {
                $q->where('client_id', auth()->user()->client_id);
            });
        }

        $tasks = $query->get();

        $todaysTimeEntries = \App\Models\TimeEntry::whereDate('start_time', now())
            ->whereNull('end_time')
            ->first();

        return view('tasks.kanban', compact('tasks'));
    }

    public function clientKanban(\App\Models\Client $client)
    {
        $tasks = \App\Models\Task::with(['project.client', 'timeEntries', 'invoice'])
            ->whereHas('project', function ($q) use ($client) {
                $q->where('client_id', $client->id);
            })
            ->get();

        return view('clients.kanban', compact('tasks', 'client'));
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

        return view('tasks.create', compact('project'));
    }

    public function store(\Illuminate\Http\Request $request, \App\Services\TelegramService $telegram)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,done',
            'pricing_type' => 'required|in:hourly,fixed',
            'rate' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $project = \App\Models\Project::find($validated['project_id']);
        if (auth()->user()->isClient() && $project->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $task = \App\Models\Task::create($validated);

        // Notify Admin via Telegram if created by Client
        if (auth()->user()->isClient()) {
            $clientName = auth()->user()->name; // Assuming User model has name
            $projectName = $project->name;
            $taskTitle = $task->title;

            $message = "🆕 <b>New Task Created</b>\n\n" .
                "👤 <b>Client:</b> {$clientName}\n" .
                "📂 <b>Project:</b> {$projectName}\n" .
                "📝 <b>Task:</b> {$taskTitle}\n" .
                "🔗 <a href='" . route('tasks.show', $task->id) . "'>View Task</a>";

            $telegram->sendMessage($message);
        }

        return redirect()->route('projects.show', ['project' => $validated['project_id']])
            ->with('success', 'Task created successfully.');
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\Task $task)
    {
        if (auth()->user()->isClient() && $task->project->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,done',
            'pricing_type' => 'sometimes|required|in:hourly,fixed',
            'rate' => 'nullable|numeric|min:0',
            'currency' => 'sometimes|required|string|size:3',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $task->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Task updated.', 'task' => $task]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        if (auth()->user()->isClient() && $task->project->client_id !== auth()->user()->client_id) {
            abort(403);
        }
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        if (auth()->user()->isClient() && $task->project->client_id !== auth()->user()->client_id) {
            abort(403);
        }
        return view('tasks.edit', compact('task'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }
        $task->delete();
        return redirect()->route('projects.show', $task->project_id)->with('success', 'Task deleted successfully.');
    }
}
