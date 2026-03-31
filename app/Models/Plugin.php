<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plugin extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enable_auto_update' => 'boolean',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(PluginVersion::class)->orderBy('created_at', 'desc');
    }

    public function latestVersion()
    {
        return $this->hasOne(PluginVersion::class)->latestOfMany();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('plugin_version_id')
            ->withTimestamps();
    }
}
