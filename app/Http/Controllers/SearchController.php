<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');

        if (empty($query)) {
            return view('search.results', [
                'query' => $query,
                'tasks' => [],
                'projects' => [],
                'clients' => [],
                'invoices' => [],
            ]);
        }

        $tasks = Task::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        $projects = Project::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        $clients = Client::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        $invoices = Invoice::where('public_id', 'like', "%{$query}%")
            ->orWhere('id', 'like', "%{$query}%") // Exact match usually, but like works for partial ID too
            ->orWhere('notes', 'like', "%{$query}%")
            ->orWhere('status', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return view('search.results', compact('tasks', 'projects', 'clients', 'invoices', 'query'));
    }
}
