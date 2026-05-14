<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckSingleProjectHealth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Project $project;

    /**
     * Time in seconds before the job is considered failed.
     * 3 attempts × 30s timeout + sleep delays = safe buffer.
     */
    public int $timeout = 120;

    /**
     * Number of times the job may be attempted.
     * We handle retries manually inside handle(), so set to 1 here.
     */
    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegram): void
    {
        // Refresh model in case data changed while the job was queued
        $this->project->refresh();

        $url = $this->project->domain;
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }

        $isUp = false;
        $statusCode = 0;
        $responseTime = null;
        $checkAttempts = 3;
        $errorMessage = null;

        for ($i = 1; $i <= $checkAttempts; $i++) {
            $start = microtime(true);
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'HorolskisBot',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Connection' => 'keep-alive',
                ])
                    ->withoutVerifying()
                    ->withOptions([
                        'curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4],
                    ])
                    ->connectTimeout(15)
                    ->timeout(30)
                    ->get($url);

                if ($response->successful()) {
                    $isUp = true;
                    $statusCode = $response->status();
                    $responseTime = round((microtime(true) - $start) * 1000);
                    $errorMessage = null;
                    break;
                }

                $statusCode = $response->status();
                $errorMessage = "Status code: {$statusCode}";
                Log::warning("Attempt {$i} failed for {$this->project->name}: Status {$statusCode}");
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                Log::warning("Attempt {$i} failed for {$this->project->name}: " . $e->getMessage());
            }

            if ($i < $checkAttempts) {
                sleep(rand(2, 5));
            }
        }

        $this->processResults($telegram, $isUp, $statusCode, $responseTime, $errorMessage);
    }

    /**
     * Handle saving results to DB and sending Telegram notifications.
     */
    protected function processResults(
        TelegramService $telegram,
        bool $isUp,
        int $statusCode,
        ?int $responseTime,
        ?string $errorMessage
    ): void {
        $status = $isUp ? 'up' : 'down';

        $this->project->uptimeChecks()->create([
            'status' => $status,
            'response_time_ms' => $responseTime,
            'status_code' => $statusCode,
            'error_message' => $errorMessage,
        ]);

        $currentStatus = $isUp ? 'up' : 'down';
        $previousStatus = $this->project->last_check_status;

        $chatId = $this->project->client->telegram_chat_id ?? null;

        if ($currentStatus !== $previousStatus) {
            if ($currentStatus === 'down') {
                $this->project->update([
                    'last_check_status' => 'down',
                    'down_since' => now(),
                    'last_check_time' => now(),
                ]);

                $msg = "🚨 <b>Project Down!</b>\n" .
                    "Project: {$this->project->name}\n" .
                    "URL: {$this->project->domain}\n" .
                    "Time: " . now()->toDateTimeString();

                $telegram->sendMessage($msg, $chatId);
            } else {
                $downtimeDuration = $this->project->down_since
                    ? $this->project->down_since->diff(now())->format('%i minutes %s seconds')
                    : 'unknown time';

                if ($this->project->down_since && $this->project->down_since->diff(now())->h > 0) {
                    $downtimeDuration = $this->project->down_since->diff(now())->format('%h hours %i minutes');
                }

                $this->project->update([
                    'last_check_status' => 'up',
                    'down_since' => null,
                    'last_check_time' => now(),
                ]);

                $msg = "✅ <b>Project Recovered!</b>\n" .
                    "Project: {$this->project->name}\n" .
                    "URL: {$this->project->domain}\n" .
                    "Downtime: {$downtimeDuration}";

                $telegram->sendMessage($msg, $chatId);
            }
        } else {
            $this->project->update(['last_check_time' => now()]);
        }

        Log::info("Health check done [{$status}]: {$this->project->name}");
    }
}
