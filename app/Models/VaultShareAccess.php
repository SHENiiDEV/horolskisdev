<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VaultShareAccess extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'vault_share_id',
        'ip_address',
        'user_agent',
    ];

    public function vaultShare()
    {
        return $this->belongsTo(VaultShare::class);
    }
}
