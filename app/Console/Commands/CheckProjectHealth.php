<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    protected $description = 'Check health status of projects concurrently using HTTP pools';

    /**
     * Execute the console command.
     */
    public function handle(TelegramService $telegram)
    {
        /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Project[] $projects */
        $projects = Project::with('client')
            ->where('is_monitoring_enabled', true)
            ->whereNotNull('domain')
            ->get();

        $this->info("Starting health check for " . $projects->count() . " projects...");

        // Process in chunks of 30 to stay within shared hosting limits
        foreach ($projects->chunk(30) as $chunk) {
            $responses = Http::pool(function (Pool $pool) use ($chunk) {
                foreach ($chunk as $project) {
                    $url = $project->domain;
                    if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                        $url = 'https://' . $url;
                    }

                    $pool->as((string) $project->id)
                        ->withHeaders([
                            'User-Agent' => 'HorolskisBot',
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                        ])
                        ->withoutVerifying()
                        ->withOptions([
                            'curl' => [
                                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                            ],
                        ])
                        ->connectTimeout(10)
                        ->timeout(20)
                        ->retry(2, 2000)
                        ->get($url);
                }
            });

            foreach ($chunk as $project) {
                $response = $responses[(string) $project->id];
                
                $isUp = false;
                $statusCode = 0;
                $responseTime = null;
                $errorMessage = null;

                if ($response instanceof Response) {
                    $statusCode = $response->status();
                    if ($response->successful()) {
                        $isUp = true;
                        $stats = $response->handlerStats();
                        $responseTime = isset($stats['total_time']) ? (int)round($stats['total_time'] * 1000) : null;
                    } else {
                        $errorMessage = "Status code: {$statusCode}";
                    }
                } elseif ($response instanceof \Exception) {
                    $errorMessage = $response->getMessage();
                }

                $this->processProjectResult($telegram, $project, $isUp, $statusCode, $responseTime, $errorMessage);
            }
        }

        $this->info("Health check completed.");
    }

    /**
     * Handle results for a single project
     */
    protected function processProjectResult(TelegramService $telegram, Project $project, bool $isUp, int $statusCode, ?int $responseTime, ?string $errorMessage)
    {
        $status = $isUp ? 'up' : 'down';

        // Log check in database
        $project->uptimeChecks()->create([
            'status' => $status,
            'response_time_ms' => $responseTime,
            'status_code' => $statusCode,
            'error_message' => $errorMessage,
        ]);

        $currentStatus = $status;
        $previousStatus = $project->last_check_status;

        if ($currentStatus !== $previousStatus) {
            if ($currentStatus === 'down') {
                $project->update([
                    'last_check_status' => 'down',
                    'down_since' => now(),
                    'last_check_time' => now(),
                ]);

                $msg = "🚨 <b>Project Down!</b>\n" .
                    "Project: {$project->name}\n" .
                    "URL: {$project->domain}\n" .
                    "Error: " . ($errorMessage ?: "Status $statusCode") . "\n" .
                    "Time: " . now()->toDateTimeString();

                $this->sendNotification($telegram, $project, $msg);
                Log::error("Project DOWN: {$project->name} ({$project->domain})");
            } else {
                // Recovered
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

                $this->sendNotification($telegram, $project, $msg);
                Log::info("Project RECOVERED: {$project->name} ({$project->domain})");
            }
        } else {
            $project->update(['last_check_time' => now()]);
        }
    }

    protected function sendNotification(TelegramService $telegram, Project $project, string $message)
    {
        $chatId = $project->client->telegram_chat_id ?? null;
        if ($chatId) {
            $telegram->sendMessage($message, $chatId);
        } else {
            $telegram->sendMessage($message);
        }
    }
}
