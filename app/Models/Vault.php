<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vault extends Model
{
    protected $fillable = ['project_id', 'name', 'type', 'payload'];

    protected $appends = ['fields'];

    protected $casts = [
        'payload' => 'encrypted:array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get normalized fields from the payload.
     * Ensures backward compatibility with old key-value payloads.
     */
    public function getFieldsAttribute()
    {
        $payload = $this->payload ?? [];

        // If it's already in the new format (list of objects with 'name' and 'value')
        if (isset($payload[0]) && is_array($payload[0]) && isset($payload[0]['name'])) {
            return collect($payload)->map(function ($field) {
                $field['is_secret'] = filter_var($field['is_secret'] ?? false, FILTER_VALIDATE_BOOLEAN);
                return $field;
            })->all();
        }

        // Convert legacy format to new format
        $fields = [];
        foreach ($payload as $key => $value) {
            // Skip empty values if desired, or keep them.
            $isSecret = in_array(strtolower($key), ['password', 'key', 'secret', 'token', 'auth_key']);
            $fields[] = [
                'name' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
                'is_secret' => $isSecret,
            ];
        }

        return $fields;
    }

    /**
     * Get a quick connection command/string based on the type.
     */
    public function getConnectionCommandAttribute()
    {
        $fields = collect($this->fields);

        if ($this->type === 'ssh') {
            $user = $fields->firstWhere(fn($f) => in_array(strtolower($f['name']), ['user', 'username', 'login']))['value'] ?? 'root';
            // Try to find host/hostname/ip
            $host = $fields->firstWhere(fn($f) => in_array(strtolower($f['name']), ['host', 'hostname', 'ip', 'server']))['value'] ?? null;
            $port = $fields->firstWhere(fn($f) => strtolower($f['name']) === 'port')['value'] ?? null;

            if ($host) {
                $cmd = "ssh {$user}@{$host}";
                if ($port && $port != 22) {
                    $cmd .= " -p {$port}";
                }
                return $cmd;
            }
        }

        if ($this->type === 'mysql' || $this->type === 'database') {
            $user = $fields->firstWhere(fn($f) => in_array(strtolower($f['name']), ['user', 'username', 'login']))['value'] ?? 'root';
            $host = $fields->firstWhere(fn($f) => in_array(strtolower($f['name']), ['host', 'hostname', 'ip', 'server']))['value'] ?? null;

            if ($host) {
                // Verify if password exists just to determine if -p flag is needed (optional, or just always add -p)
                $pass = $fields->firstWhere(fn($f) => in_array(strtolower($f['name']), ['password', 'pass', 'pwd', 'secret']))['value'] ?? null;
                return "mysql -h {$host} -u {$user}" . ($pass ? " -p" : "");
            }
        }

        return null;
    }

    public function getSshCredentials()
    {
        if ($this->type !== 'ssh') {
            return null;
        }

        $fields = collect($this->fields);

        $host = $fields->firstWhere(fn($f) => in_array(strtolower($f['name']), ['host', 'hostname', 'ip', 'server', 'host/ip']))['value'] ?? null;
        $port = $fields->firstWhere(fn($f) => strtolower($f['name']) === 'port')['value'] ?? 22;
        $username = $fields->firstWhere(fn($f) => in_array(strtolower($f['name']), ['user', 'username', 'login']))['value'] ?? 'root';
        $password = $fields->firstWhere(fn($f) => in_array(strtolower($f['name']), ['password', 'pass', 'pwd', 'secret']))['value'] ?? null;
        $privateKey = $fields->firstWhere(fn($f) => in_array(strtolower($f['name']), ['key', 'private_key', 'ssh_key']))['value'] ?? null;

        return [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'private_key' => $privateKey,
        ];
    }
}
