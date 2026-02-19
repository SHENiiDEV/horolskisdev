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
            try {
                if (ob_get_level())
                    ob_end_clean();

                echo "Tailing local {$logPath}...\n";
                flush();

                $handle = popen("tail -n 50 -f " . escapeshellarg($logPath), 'r');

                while (!feof($handle)) {
                    $line = fgets($handle);
                    echo $line;
                    flush();
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
