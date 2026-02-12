<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
        /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $projects */
        $projects = \App\Models\Project::where('is_monitoring_enabled', true)
            ->whereNotNull('domain')
            ->get();

        foreach ($projects as $project) {
            $url = $project->domain;
            if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                $url = 'https://' . $url;
            }

            \Illuminate\Support\Facades\Log::info("Checking project health: {$project->name} ({$url})");

            $isUp = false;
            $start = microtime(true);
            $responseTime = null;
            $statusCode = 0;
            try {
                $response = Http::withUserAgent('HorolskisBot')
                    ->timeout(10)
                    ->get($url);

                $responseTime = round((microtime(true) - $start) * 1000);
                $isUp = $response->successful();
                $statusCode = $response->status();
            } catch (\Exception $e) {
                $isUp = false;
                $responseTime = null;
                $statusCode = 0;
                \Illuminate\Support\Facades\Log::error("Project health check failed for {$project->name}: " . $e->getMessage());
            }

            $status = $isUp ? 'up' : 'down';

            // Log check
            $project->uptimeChecks()->create([
                'status' => $status,
                'response_time_ms' => $responseTime,
                'status_code' => $statusCode, // Added status_code to the log
            ]);

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

                    if ($project->client && $project->client->telegram_chat_id) {
                        $telegram->sendMessage($msg, $project->client->telegram_chat_id);
                    } else {
                        $telegram->sendMessage($msg);
                    }

                } else {
                    // Recovered UP
                    $downtimeDuration = $project->down_since
                        ? $project->down_since->diff(now())->format('%i minutes %s seconds')
                        : 'unknown time';

                    if ($project->down_since && $project->down_since->diff(now())->h > 0) {
                        $downtimeDuration = $project->down_since->diff(now())->format('%h hours %i minutes');
                    }

                    $project->update([
                        'last_check_status' => 'up',
                        'down_since' => null,
                        'last_check_time' => now(),
                    ]);

                    $msg = "✅ <b>Project Recovered!</b>\n" .
                        "Project: {$project->name}\n" .
                        "URL: {$project->domain}\n" .
                        "Downtime: {$downtimeDuration}";

                    if ($project->client && $project->client->telegram_chat_id) {
                        $telegram->sendMessage($msg, $project->client->telegram_chat_id);
                    } else {
                        $telegram->sendMessage($msg);
                    }
                }
            } else {
                // Status not changed, just update check time
                $project->update(['last_check_time' => now()]);
            }
        }
    }
}
