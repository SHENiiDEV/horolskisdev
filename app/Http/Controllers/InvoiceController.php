<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function createFromTasks(Request $request)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $project = \App\Models\Project::findOrFail($request->project_id);

        // Fetch tasks and pre-calculate costs
        $tasks = $project->tasks()
            ->whereNull('invoice_id')
            ->where('status', 'done')
            ->with(['timeEntries'])
            ->get()
            ->map(function ($task) {
                // Calculate total duration in hours
                $totalSeconds = $task->timeEntries->sum('duration');
                $hours = $totalSeconds / 3600;

                // Calculate estimated cost
                if ($task->pricing_type === 'fixed') {
                    $cost = $task->rate ?? 0;
                } else {
                    $rate = $task->rate ?? 0;
                    $cost = $rate * $hours;
                }

                $task->total_hours = number_format($hours, 2);
                $task->calculated_cost = number_format($cost, 2, '.', '');
                return $task;
            });

        return view('invoices.create-from-tasks', compact('project', 'tasks'));
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Invoice::with(['client', 'project'])->latest();

        if (auth()->user()->isClient()) {
            $query->where('client_id', auth()->user()->client_id);
        }

        if ($request->query('status') === 'archived') {
            $query->onlyTrashed();
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('public_id', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->paginate(10);
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(\Illuminate\Http\Request $request)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $client_id = $request->query('client_id');
        $client = null;
        $tasks = collect();

        if ($client_id) {
            $client = \App\Models\Client::find($client_id);
            if ($client) {
                $tasks = \App\Models\Task::whereHas('project', function ($q) use ($client_id) {
                    $q->where('client_id', $client_id);
                })
                    ->where('status', 'done')
                    ->where(function ($query) {
                        $query->whereNull('invoice_id')
                            ->orWhereHas('invoice', function ($q) {
                                $q->where('status', '!=', 'paid');
                            });
                    })
                    ->with(['project', 'timeEntries'])
                    ->get()
                    ->map(function ($task) {
                        $totalSeconds = $task->timeEntries->sum('duration');
                        $hours = $totalSeconds / 3600;
                        if ($task->pricing_type === 'fixed') {
                            $cost = $task->rate ?? 0;
                        } else {
                            $rate = $task->rate ?? 0;
                            $cost = $rate * $hours;
                        }
                        $cost = number_format($cost, 2, '.', '');
                        $hours = number_format($hours, 2);

                        return array_merge($task->toArray(), [
                            'calculated_cost' => $cost,
                            'total_hours' => $hours,
                        ]);
                    });
            }
        }

        $clients = \App\Models\Client::orderBy('name')->pluck('name', 'id');

        return view('invoices.create', compact('client', 'clients', 'tasks'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:draft,sent,paid',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.amount' => 'required|numeric|min:0',
            'task_ids' => 'nullable|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        $customItemsTotal = 0;
        if (!empty($validated['items'])) {
            $customItemsTotal = collect($validated['items'])->sum('amount');
        }

        $tasksTotal = 0;
        if (!empty($validated['task_ids'])) {
            $tasks = \App\Models\Task::whereIn('id', $validated['task_ids'])->with('timeEntries')->get();
            foreach ($tasks as $task) {
                $totalSeconds = $task->timeEntries->sum('duration');
                $hours = $totalSeconds / 3600;
                if ($task->pricing_type === 'fixed') {
                    $tasksTotal += $task->rate ?? 0;
                } else {
                    $tasksTotal += ($task->rate ?? 0) * $hours;
                }
            }
        }

        // Always use calculated total to ensure accuracy
        $validated['amount'] = $customItemsTotal + $tasksTotal;

        // Generate public_id
        $lastInvoice = \App\Models\Invoice::withTrashed()->latest()->first();
        $nextId = $lastInvoice ? $lastInvoice->id + 1 : 1;
        $validated['public_id'] = 'MH ' . str_pad($nextId, 5, '0', STR_PAD_LEFT) . '/' . now()->format('my');

        $invoice = \App\Models\Invoice::create(\Illuminate\Support\Arr::except($validated, ['task_ids']));

        // Attach tasks
        if (!empty($validated['task_ids'])) {
            \App\Models\Task::whereIn('id', $validated['task_ids'])->update(['invoice_id' => $invoice->id]);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created successfully.');
    }

    public function show(\App\Models\Invoice $invoice)
    {
        if (auth()->user()->isClient() && $invoice->client_id !== auth()->user()->client_id) {
            abort(403);
        }

        $invoice->load(['client', 'project', 'tasks.timeEntries']);
        if ($invoice->language) {
            app()->setLocale($invoice->language);
        }
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $invoice = \App\Models\Invoice::findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|in:draft,sent,paid',
            'language' => 'nullable|in:en,lv',
        ]);

        $updateData = [];
        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }
        if (isset($validated['language'])) {
            $updateData['language'] = $validated['language'];
        }

        if (!empty($updateData)) {
            $invoice->update($updateData);
        }

        return back()->with('success', 'Invoice updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $invoice = \App\Models\Invoice::withTrashed()->findOrFail($id);

        if ($invoice->trashed()) {
            $invoice->forceDelete();
            return back()->with('success', 'Invoice permanently deleted.');
        }

        $invoice->delete();
        return back()->with('success', 'Invoice archived.');
    }

    public function restore(string $id)
    {
        if (auth()->user()->isClient()) {
            abort(403);
        }

        $invoice = \App\Models\Invoice::withTrashed()->findOrFail($id);
        $invoice->restore();
        return back()->with('success', 'Invoice restored.');
    }

}
