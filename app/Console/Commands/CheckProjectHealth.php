<?php

namespace App\Console\Commands;

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
    protected $description = 'Check health status of projects and notify via Telegram';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\TelegramService $telegram)
    {
        $projects = \App\Models\Project::where('is_monitoring_enabled', true)
            ->whereNotNull('domain')
            ->get();

        foreach ($projects as $project) {
            $url = $project->domain;
            if (!str_starts_with($url, 'http')) {
                $url = 'https://' . $url;
            }

            $isUp = false;
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
                if ($response->successful()) {
                    $isUp = true;
                }
            } catch (\Exception $e) {
                // $isUp remains false
            }

            $currentStatus = $isUp ? 'up' : 'down';
            $previousStatus = $project->last_check_status;

            if ($currentStatus !== $previousStatus) {
                if ($currentStatus === 'down') {
                    // Went DOWN
                    $project->update([
                        'last_check_status' => 'down',
                        'down_since' => now(),
                        'last_check_time' => now(),
                    ]);

                    $msg = "🚨 <b>Project Down!</b>\n" .
                        "Project: {$project->name}\n" .
                        "URL: {$project->domain}\n" .
                        "Time: " . now()->toDateTimeString();

                    $telegram->sendMessage($msg);

                } else {
                    // Recovered UP
                    $downtime = 0;
                    if ($project->down_since) {
                        $downtime = now()->diffInMinutes($project->down_since);
                    }

                    $project->update([
                        'last_check_status' => 'up',
                        'down_since' => null,
                        'last_check_time' => now(),
                    ]);

                    $msg = "✅ <b>Project Recovered!</b>\n" .
                        "Project: {$project->name}\n" .
                        "URL: {$project->domain}\n" .
                        "Downtime: {$downtime} mins";

                    $telegram->sendMessage($msg);
                }
            } else {
                // Status not changed, just update check time
                $project->update(['last_check_time' => now()]);
            }
        }
    }
}
