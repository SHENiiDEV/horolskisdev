<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
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

            $ssh->setTimeout(30); // 30s timeout for commands

            // 4. Construct Command with CWD tracking
            // We strip any trailing semi-colons or newlines from input command slightly securely? 
            // Actually, we just want to run it.
            // We use a delimiter to extract the new CWD.
            $delimiter = '---CWD-DELIMITER---';

            // Escape cwd slightly to prevent breaking out of cd, though user is authorized.
            // $wrappedCommand = "cd \"$cwd\" && $command; echo \"\"; echo \"$delimiter\"; pwd";
            // Better handling for cd failure:
            // "if cd '$cwd'; then $command; else echo 'Failed to change directory'; fi; echo '$delimiter'; pwd"

            // Note: complex commands with ; inside might be tricky if we don't group.
            // ( cd "$cwd" && ( $command ) ); echo "$delimiter"; pwd
            // We use parentheses to run in subshell? No, we want to know if 'cd' inside command changed dir?
            // Actually, in a stateless terminal, 'cd newdir' only works if we capture the new PWD.
            // So: cd "$cwd"; $command; echo "$delimiter"; pwd

            // But if $command is "cd /var", we want the FINAL pwd.
            // So: cd "$cwd" && { $command; }; echo "$delimiter"; pwd

            $safeCwd = str_replace('"', '\"', $cwd); // Basic escaping

            // We need to force a PTY or strict handling?
            // exec() typically uses a non-interactive shell.
            // If user runs `cd ..`, we want the next prompt to be distinct.

            $fullCommand = "cd \"{$safeCwd}\" && { {$command}; }; echo \"\n{$delimiter}\"; pwd";

            $output = $ssh->exec($fullCommand);

            // 5. Parse Output
            $parts = explode("\n{$delimiter}\n", $output);

            if (count($parts) < 2) {
                // Fallback if delimiter not found (maybe command messed with output stream or killed shell)
                // Try simpler split or just return output and keep old cwd
                $parts = explode($delimiter, $output);
            }

            if (count($parts) >= 2) {
                $cmdOutput = trim($parts[0]);
                $newCwd = trim($parts[1]);
            } else {
                $cmdOutput = $output;
                $newCwd = $cwd; // Failed to get new CWD
            }

            return response()->json([
                'output' => $cmdOutput,
                'cwd' => $newCwd,
            ]);

        } catch (\Throwable $e) {
            return response()->json(['error' => 'SSH Error: ' . $e->getMessage()], 500);
        }
    }
}
