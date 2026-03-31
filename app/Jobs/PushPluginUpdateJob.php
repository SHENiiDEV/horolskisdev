<?php

namespace App\Jobs;

use App\Models\Plugin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushPluginUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $plugin;

    /**
     * Create a new job instance.
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->plugin->enable_auto_update || !$this->plugin->update_endpoint) {
            return;
        }

        $endpoint = ltrim($this->plugin->update_endpoint, '/');
        $secretKey = $this->plugin->update_secret_key;

        $this->plugin->loadMissing('projects');

        foreach ($this->plugin->projects as $project) {
            if (empty($project->domain)) {
                continue;
            }

            // Normalize domain
            $domain = rtrim(str_replace(['http://', 'https://'], '', $project->domain), '/');
            $url = 'https://' . $domain . '/' . $endpoint;

            try {
                $response = Http::timeout(10)
                    ->withToken($secretKey)
                    ->post($url);

                if (!$response->successful()) {
                    Log::warning("Failed to auto-update plugin {$this->plugin->name} on project {$project->name}. Status: {$response->status()}", [
                        'url' => $url,
                        'response' => $response->body()
                    ]);
                } else {
                    Log::info("Successfully triggered auto-update for plugin {$this->plugin->name} on project {$project->name}");
                }
            } catch (\Exception $e) {
                Log::error("Exception triggering auto-update for plugin {$this->plugin->name} on project {$project->name}: " . $e->getMessage());
            }
        }
    }
}
