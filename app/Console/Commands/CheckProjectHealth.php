<?php

namespace App\Console\Commands;

use App\Jobs\CheckSingleProjectHealth;
use App\Models\Project;
use Illuminate\Console\Command;

class CheckProjectHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:project-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch health check jobs for all monitored projects';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $projects = Project::with('client')
            ->where('is_monitoring_enabled', true)
            ->whereNotNull('domain')
            ->get();

        $count = 0;

        foreach ($projects as $project) {
            CheckSingleProjectHealth::dispatch($project)->onQueue('monitoring');
            $count++;
        }

        $this->info("Dispatched {$count} health check jobs to the [monitoring] queue.");
    }
}
