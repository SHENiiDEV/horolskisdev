<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use phpseclib3\Net\SFTP;
use phpseclib3\Crypt\PublicKeyLoader;
use App\Models\Project;

class ProjectFileController extends Controller
{
    private function connectSftp(Project $project)
    {
        $vault = $project->getSshVault();
        if (!$vault) {
            abort(404, 'SSH Config not found');
        }

        $creds = $vault->getSshCredentials();
        if (!$creds['host']) {
            abort(400, 'Host configuration missing');
        }

        $sftp = new SFTP($creds['host'], $creds['port']);

        if ($creds['private_key']) {
            $key = PublicKeyLoader::load($creds['private_key'], $creds['password']);
            if (!$sftp->login($creds['username'], $key)) {
                throw new \Exception('Login failed with Private Key');
            }
        } elseif ($creds['password']) {
            if (!$sftp->login($creds['username'], $creds['password'])) {
                throw new \Exception('Login failed with Password');
            }
        } else {
            throw new \Exception('No authentication method available');
        }

        return $sftp;
    }

    public function index(Request $request, Project $project)
    {
        $path = $request->input('path', '.'); // Default to home/current

        try {
            $sftp = $this->connectSftp($project);

            // Resolve absolute path if needed, but '.' works for initial
            $rawList = $sftp->rawlist($path);

            if ($rawList === false) {
                return response()->json(['error' => 'Could not list directory'], 400);
            }

            // Format list
            $files = [];
            foreach ($rawList as $filename => $info) {
                if ($filename === '.' || $filename === '..')
                    continue;

                $files[] = [
                    'name' => $filename,
                    'type' => $info['type'] == 2 ? 'directory' : 'file', // 1: file, 2: dir (phpseclib specific)
                    'size' => $info['size'],
                    'permissions' => $info['permissions'] ?? '', // Fallback for permissions
                    'path' => $path === '.' ? $filename : $path . '/' . $filename
                ];
            }

            // Sort: Directories first, then files
            usort($files, function ($a, $b) {
                if ($a['type'] === $b['type']) {
                    return strcasecmp($a['name'], $b['name']);
                }
                return $a['type'] === 'directory' ? -1 : 1;
            });

            return response()->json([
                'path' => $sftp->pwd(), // Return actual PWD
                'files' => $files
            ]);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, Project $project)
    {
        $path = $request->input('path');

        try {
            $sftp = $this->connectSftp($project);

            if (!$sftp->is_file($path)) {
                return response()->json(['error' => 'Not a file'], 400);
            }

            // Limit size? 1MB max for safety
            if ($sftp->filesize($path) > 1024 * 1024) {
                return response()->json(['error' => 'File too large to edit (Max 1MB)'], 400);
            }

            $content = $sftp->get($path);

            return response()->json(['content' => $content]);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'path' => 'required|string',
            'content' => 'required|string',
        ]);

        $path = $request->input('path');
        $content = $request->input('content');

        try {
            $sftp = $this->connectSftp($project);

            if (!$sftp->put($path, $content)) {
                throw new \Exception('Failed to save file');
            }

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
