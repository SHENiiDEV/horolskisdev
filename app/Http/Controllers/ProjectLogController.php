<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectLogController extends Controller
{
    public function stream(Project $project)
    {
        // 1. Get SSH Vault
        $vault = $project->getSshVault();
        if (!$vault) {
            return response()->json(['error' => 'No SSH Vault found for this project.'], 404);
        }

        // 2. Get Credentials
        $creds = $vault->getSshCredentials();
        if (!$creds['host']) {
            return response()->json(['error' => 'SSH Host not configured.'], 400);
        }

        // 3. Connect via SSH
        try {
            $ssh = new SSH2($creds['host'], $creds['port']);

            if ($creds['private_key']) {
                $key = PublicKeyLoader::load($creds['private_key'], $creds['password']); // Password is passphrase if key exists
                if (!$ssh->login($creds['username'], $key)) {
                    throw new \Exception('Login failed with Private Key');
                }
            } elseif ($creds['password']) {
                if (!$ssh->login($creds['username'], $creds['password'])) {
                    throw new \Exception('Login failed with Password');
                }
            } else {
                return response()->json(['error' => 'No authentication method found (Password or Key).'], 400);
            }

            // 4. Stream output
            return new StreamedResponse(function () use ($ssh, $creds) {
                // Disable output buffering
                if (ob_get_level())
                    ob_end_clean();

                // Determine log path - try standard locations or user might need to specify?
                // For now hardcode typical Laravel path, or maybe just `tail -f laravel.log` if in root.
                // Better: Check typical paths.
                // Default: /var/www/html/storage/logs/laravel.log or similar.
                // Let's assume standard Laravel structure relative to user home or absolute?
                // For simplicity, let's try a few common paths or just one.
                // User requirement: "laravel.log".

                $logPath = 'storage/logs/laravel.log'; // Relative to where SSH lands?
                // Probably better to let user configure, but we'll try to find it.
                // Let's execute `tail -f storage/logs/laravel.log` and also try absolute paths if that fails?
                // Simpler: Just run it.

                // Send explicit flush to keep connection alive
                echo "Connected to {$creds['host']}...\n";
                echo "Tailing {$logPath}...\n";
                flush();

                $ssh->setTimeout(0); // No timeout
                $ssh->exec("tail -n 50 -f {$logPath}", function ($str) {
                    echo $str;
                    flush();
                });
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no', // Nginx
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'SSH Connection Failed: ' . $e->getMessage()], 500);
        }
    }
}
