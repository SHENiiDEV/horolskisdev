<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VaultShareController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'value' => 'required|string',
            'name' => 'nullable|string|max:255',
            'expires_in' => 'nullable|integer|min:1', // minutes
            'allowed_views' => 'required|integer|min:1',
        ]);

        // Use 'nullable' so we can explicitly pass null
        $expiresAt = null;
        if (!empty($validated['expires_in'])) {
            $expiresAt = now()->addMinutes($validated['expires_in']);
        }

        $share = \App\Models\VaultShare::create([
            'project_id' => $validated['project_id'],
            'value' => $validated['value'],
            'name' => $validated['name'],
            'expires_at' => $expiresAt,
            'allowed_views' => $validated['allowed_views'],
        ]);

        return response()->json([
            'link' => route('shares.show', $share->id),
            'expires_at' => $expiresAt ? $expiresAt->toDateTimeString() : 'Never',
        ]);
    }

    public function show($id)
    {
        $share = \App\Models\VaultShare::active()->where('id', $id)->firstOrFail();

        return view('vault-shares.show', compact('share'));
    }

    public function reveal($id)
    {
        $share = \App\Models\VaultShare::active()->where('id', $id)->firstOrFail();

        $share->increment('views');

        if ($share->views >= $share->allowed_views) {
            $share->update(['is_active' => false]);
        }

        return response()->json([
            'value' => $share->value,
        ]);
    }
}
