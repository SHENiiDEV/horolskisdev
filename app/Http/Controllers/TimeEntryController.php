<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function start(\Illuminate\Http\Request $request, \App\Models\Task $task)
    {
        // Stop any currently running timer for this user
        $activeEntry = \App\Models\TimeEntry::where('user_id', auth()->id())
            ->whereNull('end_time')
            ->first();

        if ($activeEntry) {
            $now = now();
            $activeEntry->update([
                'end_time' => $now,
                'duration' => $activeEntry->start_time->diffInSeconds($now, true),
            ]);
        }

        \App\Models\TimeEntry::create([
            'user_id' => auth()->id(),
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'start_time' => now(),
            'description' => 'Working on: ' . $task->title,
        ]);

        $task->update(['status' => 'in_progress']);

        return back()->with('success', 'Timer started for task: ' . $task->title);
    }

    public function stop()
    {
        $activeEntry = \App\Models\TimeEntry::where('user_id', auth()->id())
            ->whereNull('end_time')
            ->latest()
            ->first();

        if ($activeEntry) {
            $now = now();
            $activeEntry->update([
                'end_time' => $now,
                'duration' => $activeEntry->start_time->diffInSeconds($now, true),
            ]);
            return back()->with('success', 'Timer stopped.');
        }

        return back()->with('warning', 'No active timer found.');
    }

    public function index()
    {
        $entries = \App\Models\TimeEntry::with(['project', 'task'])->where('user_id', auth()->id())->latest()->paginate(25);
        return view('time-entries.index', compact('entries'));
    }

    public function update(Request $request, \App\Models\TimeEntry $timeEntry)
    {
        if ($timeEntry->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|numeric|min:0', // Optional manual override helper
        ]);

        $start = \Illuminate\Support\Carbon::parse($validated['start_time']);
        $end = $validated['end_time'] ? \Illuminate\Support\Carbon::parse($validated['end_time']) : null;

        $duration = 0;

        if ($request->has('duration_minutes') && $request->duration_minutes !== null) {
            // Manual override takes precedence? Or just use it if provided.
            $duration = (int) ($validated['duration_minutes'] * 60);
            // If end time is not provided, maybe infer it? 
            // Let's stick to: if duration is provided, use it. If not, calc from start/end.
            // But usually duration is calculated from start/end.
            // If user wants to set "2 hours", they might not care about end time.
            // But end time is useful for timeline.
            // Let's autoset end time if duration provided and end is null.
            if (!$end) {
                $end = $start->copy()->addSeconds($duration);
            }
        } elseif ($end) {
            $duration = $start->diffInSeconds($end, true);
        }

        $timeEntry->update([
            'start_time' => $start,
            'end_time' => $end,
            'duration' => $duration,
            'description' => $validated['description'] ?? $timeEntry->description,
        ]);

        return back()->with('success', 'Time entry updated.');
    }

    public function destroy(\App\Models\TimeEntry $timeEntry)
    {
        if ($timeEntry->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $timeEntry->delete();
        return back()->with('success', 'Time entry deleted.');
    }
}
