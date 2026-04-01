<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PluginApiController extends Controller
{
    /**
     * Download the latest version of a plugin.
     * Expects ?folder_name=... and ?key=... (or Authorization: Bearer <key>)
     */
    public function download(Request $request)
    {
        $folderName = $request->get('folder_name');

        if (!$folderName) {
            return response()->json(['error' => 'folder_name is required'], 400);
        }

        $plugin = Plugin::where('folder_name', $folderName)->first();

        if (!$plugin) {
            return response()->json(['error' => 'Plugin not found for the given folder_name'], 404);
        }

        $providedKey = $request->bearerToken() ?? $request->get('key');

        if (empty($plugin->update_secret_key) || $plugin->update_secret_key !== $providedKey) {
            return response()->json(['error' => 'Unauthorized or invalid key'], 403);
        }

        $latestVersion = $plugin->latestVersion;

        if (!$latestVersion || !Storage::exists($latestVersion->file_path)) {
            return response()->json(['error' => 'No active version found or file missing'], 404);
        }

        return Storage::download($latestVersion->file_path, $plugin->folder_name . '-latest.zip');
    }
}
