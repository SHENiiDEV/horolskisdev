<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaultShare extends Model
{
    protected $fillable = [
        'project_id',
        'value',
        'name',
        'expires_at',
        'allowed_views',
        'views',
        'is_active',
    ];

    protected $casts = [
        'value' => 'encrypted',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereRaw('views < allowed_views');
    }
}
