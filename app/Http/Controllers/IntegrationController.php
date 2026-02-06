<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // Max 10MB
            'documentation_url' => 'nullable|url',
            'test_cards' => 'nullable|string',
        ]);

        $path = $request->file('file')->store('integrations', 'public');

        \App\Models\ProjectIntegration::create([
            'project_id' => $validated['project_id'],
            'name' => $validated['name'],
            'file_path' => $path,
            'documentation_url' => $validated['documentation_url'],
            'test_cards' => $validated['test_cards'],
        ]);

        return back()->with('success', 'Integration uploaded successfully.');
    }

    public function destroy(\App\Models\ProjectIntegration $integration)
    {
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($integration->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($integration->file_path);
        }

        $integration->delete();

        return back()->with('success', 'Integration deleted successfully.');
    }

    public function download(\App\Models\ProjectIntegration $integration)
    {
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($integration->file_path)) {
            abort(404, 'File not found.');
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($integration->file_path, $integration->name . '.' . pathinfo($integration->file_path, PATHINFO_EXTENSION));
    }
}