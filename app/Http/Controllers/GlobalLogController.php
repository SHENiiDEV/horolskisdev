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

    public function stream(Request $request)
    {
        $logPath = storage_path('logs/laravel.log');

        if (!file_exists($logPath)) {
            return response()->json(['error' => 'Log file not found.'], 404);
        }

        return new StreamedResponse(function () use ($logPath, $request) {
            try {
                if (ob_get_level())
                    ob_end_clean();

                echo "Tailing local {$logPath}...\n";
                flush();

                $search = $request->query('search');

                // If there's a specific search, we grep the file. 
                // We use -A 50 to get stack traces attached to the matched log line.
                if (!empty($search)) {
                    $escapedSearch = escapeshellarg($search);
                    $cmd = "grep -i -A 50 {$escapedSearch} " . escapeshellarg($logPath);
                } else {
                    // Otherwise just tail the last 2000 lines to give a good history
                    $cmd = "tail -n 2000 -f " . escapeshellarg($logPath);
                }

                $handle = popen($cmd, 'r');

                // If it's a static search, we just read until EOF.
                // If it's a tail -f, feof might block, but stream handles it.
                while (!feof($handle)) {
                    $line = fgets($handle);
                    if ($line !== false) {
                        echo $line;
                        flush();
                    }
                }

                pclose($handle);
            } catch (\Throwable $e) {
                echo "Error: " . $e->getMessage() . "\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
