<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectIntegration;

class Project extends Model
{
    protected $guarded = [];

    protected $casts = [
        'last_check_time' => 'datetime',
        'down_since' => 'datetime',
        'is_monitoring_enabled' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function vaults()
    {
        return $this->hasMany(Vault::class);
    }

    public function integrations()
    {
        return $this->hasMany(ProjectIntegration::class);
    }

    public function uptimeChecks()
    {
        return $this->hasMany(ProjectUptimeCheck::class);
    }

    public function plugins()
    {
        return $this->belongsToMany(Plugin::class, 'plugin_project')
            ->withPivot('plugin_version_id')
            ->withTimestamps();
    }

    public function getSshVault()
    {
        // Prioritize a vault strictly named 'SSH' or type 'ssh'
        return $this->vaults->first(function ($vault) {
            return $vault->type === 'ssh' || str_contains(strtolower($vault->name), 'ssh');
        });
    }
}
