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
            $expiresAt = now()->addMinutes((int) $validated['expires_in']);
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

        return view('vault-shares.show', [
            'share' => $share,
            'revealed' => false,
            'secret' => null,
            'secretFields' => [],
            'secretType' => 'text',
        ]);
    }

    public function reveal($id)
    {
        $share = \App\Models\VaultShare::active()->where('id', $id)->firstOrFail();

        $share->increment('views');

        if ($share->views >= $share->allowed_views) {
            $share->update(['is_active' => false]);
        }

        $secret = $share->value;
        $secretFields = [];
        $secretType = 'text';

        // Try to parse JSON
        $decoded = json_decode($secret, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $secretFields = $decoded;
            $secretType = 'json';

            // Create a text representation for clipboard copy if needed (fallback)
            // But for the view we will use $secretFields
        }

        return view('vault-shares.show', [
            'share' => $share,
            'revealed' => true,
            'secret' => $secret,
            'secretFields' => $secretFields,
            'secretType' => $secretType,
        ]);
    }
}
