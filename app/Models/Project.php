<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectIntegration;

class Project extends Model
{
    protected $guarded = [];

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
}
