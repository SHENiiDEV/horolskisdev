<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectIntegration extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'file_path',
        'documentation_url',
        'test_cards',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
