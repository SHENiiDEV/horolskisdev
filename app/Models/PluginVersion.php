<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PluginVersion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'changelog' => 'array', // Optional: if we want structured changelog, otherwise string is fine
    ];

    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }
}
