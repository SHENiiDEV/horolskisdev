<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

class ProjectTerminalController extends Controller
{
    public function execute(Request $request, Project $project)
    {
        $request->validate([
            'command' => 'required|string',
            'cwd' => 'nullable|string',
        ]);

        $command = $request->input('command');
        $cwd = $request->input('cwd') ?: '~';

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
                $key = PublicKeyLoader::load($creds['private_key'], $creds['password']);
                if (!$ssh->login($creds['username'], $key)) {
                    throw new \Exception('Login failed with Private Key');
                }
            } elseif ($creds['password']) {
                if (!$ssh->login($creds['username'], $creds['password'])) {
                    throw new \Exception('Login failed with Password');
                }
            } else {
                return response()->json(['error' => 'No authentication method found.'], 400);
            }

            $ssh->setTimeout(30); // 30s timeout

            // 4. Construct Command with CWD tracking
            $delimiter = '---CWD-DELIMITER-' . Str::random(16) . '---';

            // Build command prefix: only cd if we are not at ~ (initial state)
            // And ensure we use a safe, absolute path from then on.
            $cmdPrefix = '';
            if ($cwd !== '~') {
                $safeCwd = str_replace('"', '\"', $cwd);
                $cmdPrefix = "cd \"{$safeCwd}\" && ";
            }

            // Use braces to group the user command, enabling complex syntax like `cd foo; ls` to work
            // within the logic flow.
            // We append `pwd` after the delimiter to capture the new CWD.
            $fullCommand = "{$cmdPrefix}{ {$command}; }; echo \"\n{$delimiter}\"; pwd";

            $output = $ssh->exec($fullCommand);

            // 5. Parse Output
            // Split by the delimiter. We expect: [Command Output] [Delimiter] [New CWD]
            $parts = explode($delimiter, $output);

            if (count($parts) >= 2) {
                $cmdOutput = trim($parts[0]);
                // The CWD might have a newline at the end or garbage if something weird happened
                $newCwd = trim($parts[1]);
            } else {
                // Fallback: something went wrong with the container/echo
                // Return raw output and keep old CWD (safest)
                $cmdOutput = $output;
                $newCwd = $cwd;
            }

            return response()->json([
                'output' => $cmdOutput,
                'cwd' => $newCwd,
            ]);

        } catch (\Throwable $e) {
            return response()->json(['error' => 'SSH Error: ' . $e->getMessage()], 500);
        }
    }

    public function autocomplete(Request $request, Project $project)
    {
        $request->validate([
            'input' => 'nullable|string',
            'cwd' => 'required|string',
        ]);

        $input = $request->input('input', '');
        $cwd = $request->input('cwd');

        // 1. Get SSH
        $vault = $project->getSshVault();
        if (!$vault)
            return response()->json([]);
        $creds = $vault->getSshCredentials();
        if (!$creds['host'])
            return response()->json([]);

        try {
            $ssh = new SSH2($creds['host'], $creds['port']);
            if ($creds['private_key']) {
                $key = PublicKeyLoader::load($creds['private_key'], $creds['password']);
                $ssh->login($creds['username'], $key);
            } elseif ($creds['password']) {
                $ssh->login($creds['username'], $creds['password']);
            } else {
                return response()->json([]);
            }

            $ssh->setTimeout(5);

            // Handle CWD same as execute
            $cmdPrefix = '';
            if ($cwd !== '~') {
                $safeCwd = str_replace('"', '\"', $cwd);
                $cmdPrefix = "cd \"{$safeCwd}\" && ";
            }

            // Simple logic: use 'ls -d $input*' to find matches? 
            // Or better: compgen if bash is available. `bash -c "compgen -f $input"`
            // But strict 'ls -1d' is often safer for minimal environments.
            // We want to complete files and folders.

            // If input has spaces, it might break. Basic handling:
            $safeInput = escapeshellarg($input . '*');

            // "ls -1dF" gives us slash at end of dirs
            $cmd = "{$cmdPrefix}ls -1dF {$input}* 2>/dev/null";

            $output = $ssh->exec($cmd);

            // Limit results
            $suggestions = collect(explode("\n", $output))
                ->map(fn($line) => trim($line))
                ->filter()
                ->take(10)
                ->values();

            return response()->json($suggestions);

        } catch (\Throwable $e) {
            return response()->json([]);
        }
    }
}
