<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GlobalLogController extends Controller
{
    public function index()
    {
        return view('logs.index');
    }

    public function stream()
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return response()->json(['error' => 'Log file not found.'], 404);
        }

        return new StreamedResponse(function () use ($logPath) {
            if (ob_get_level())
                ob_end_clean();

            echo "Tailing local {$logPath}...\n";
            flush();

            // Simple tail implementation using shell_exec or similar
            // Since we are on local/server, we can use `tail` command if available
            // Or use PHP to read file. `tail -f` is most robust for following.

            $handle = popen("tail -n 50 -f " . escapeshellarg($logPath), 'r');

            while (!feof($handle)) {
                $line = fgets($handle);
                echo $line;
                flush();
            }

            pclose($handle);

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
