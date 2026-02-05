@if($project->tasks()->whereNull('invoice_id')->where('status', 'done')->count() > 0)
    <div class="mt-4 flex justify-end">
        <a href="{{ route('invoices.create-from-tasks', ['project_id' => $project->id]) }}"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
            Generate Invoice from Tasks
        </a>
    </div>
@endif