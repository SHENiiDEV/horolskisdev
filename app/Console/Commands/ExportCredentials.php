<?php

namespace App\Console\Commands;

use App\Models\Vault;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportCredentials extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-credentials {path? : Path to save the exported JSON file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all credentials (vaults) to a JSON file formatted for the new system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path') ?: storage_path('app/credentials_export.json');
        
        $this->info("Fetching credentials from the database...");

        try {
            $vaults = Vault::with('project')->get();
        } catch (\Exception $e) {
            $this->error("Failed to fetch credentials from the database: " . $e->getMessage());
            return 1;
        }

        if ($vaults->isEmpty()) {
            $this->warn("No credentials found in the database.");
            return 0;
        }

        $this->info("Found " . $vaults->count() . " credentials. Mapping fields...");

        $exported = [];

        foreach ($vaults as $vault) {
            $project = $vault->project;
            
            // Normalize website url
            $websiteUrl = $project->domain ?? null;
            if ($websiteUrl && !str_starts_with($websiteUrl, 'http://') && !str_starts_with($websiteUrl, 'https://')) {
                $websiteUrl = 'https://' . $websiteUrl;
            }

            // Map fields payload to a flat normalized array for easier fuzzy matching
            $normalizedFields = [];
            foreach ($vault->fields as $field) {
                if (isset($field['name']) && isset($field['value'])) {
                    $normalizedKey = strtolower(str_replace(['_', '-', ' '], '', $field['name']));
                    $normalizedFields[$normalizedKey] = [
                        'original_name' => $field['name'],
                        'value' => $field['value']
                    ];
                }
            }

            $usedKeys = [];

            // Extract primary attributes with fallback priority list
            $login = $this->extractField($normalizedFields, ['login', 'username', 'user', 'email'], $usedKeys);
            $password = $this->extractField($normalizedFields, ['password', 'pass', 'pwd', 'secret'], $usedKeys);
            $providerUrl = $this->extractField($normalizedFields, ['providerurl', 'url', 'loginurl', 'link', 'panel', 'panelurl'], $usedKeys);
            
            // If providerUrl not found, check if 'host' looks like a URL
            if (!$providerUrl && isset($normalizedFields['host'])) {
                $hostVal = $normalizedFields['host']['value'];
                if (str_starts_with($hostVal, 'http://') || str_starts_with($hostVal, 'https://')) {
                    $providerUrl = $hostVal;
                    $usedKeys[] = 'host';
                }
            }

            $comments = $this->extractField($normalizedFields, ['comments', 'comment', 'notes', 'note', 'description', 'info', 'instruction'], $usedKeys);

            // Leftover custom fields go into the "fields" JSON attribute
            $customFields = [];
            foreach ($normalizedFields as $key => $info) {
                if (!in_array($key, $usedKeys)) {
                    $customFields[$info['original_name']] = $info['value'];
                }
            }

            // Map type
            $type = $this->mapType($vault->type, $vault->name);

            // Construct new credential object
            $exported[] = [
                'company_name' => $project->name ?? null,
                'company_id' => $vault->project_id,
                'project_name' => $project->name ?? null,
                'project_id' => $vault->project_id,
                'website_url' => $websiteUrl,
                'name' => $vault->name,
                'type' => $type,
                'provider_url' => $providerUrl,
                'login' => $login,
                'username' => $login, // Include both for system compatibility
                'password' => $password,
                'comments' => $comments,
                'fields' => !empty($customFields) ? $customFields : null,
            ];
        }

        // Write output
        try {
            $directory = dirname($path);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($path, json_encode($exported, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } catch (\Exception $e) {
            $this->error("Failed to write JSON file to {$path}: " . $e->getMessage());
            return 1;
        }

        $this->info("Successfully exported " . count($exported) . " credentials to: " . $path);
        return 0;
    }

    /**
     * Extract a field's value based on candidates and mark the key as used.
     */
    private function extractField(array $normalizedFields, array $candidates, array &$usedKeys): ?string
    {
        foreach ($candidates as $cand) {
            if (isset($normalizedFields[$cand])) {
                $usedKeys[] = $cand;
                return $normalizedFields[$cand]['value'];
            }
        }
        return null;
    }

    /**
     * Map old type/name combinations to target credential types.
     */
    private function mapType(?string $type, ?string $name): string
    {
        $type = strtolower(trim($type ?? ''));
        $name = strtolower(trim($name ?? ''));

        // Match DB
        if (in_array($type, ['db', 'mysql', 'database', 'postgres', 'postgresql', 'mongodb', 'mariadb', 'redis'])) {
            return 'db';
        }
        // Match SSH
        if (in_array($type, ['ssh', 'sftp', 'ftp', 'server'])) {
            if ($type === 'ssh' || $type === 'sftp') {
                return 'ssh';
            }
            return 'hosting';
        }
        // Match Email
        if (in_array($type, ['email', 'mail', 'smtp'])) {
            return 'email';
        }
        // Match CMS
        if (in_array($type, ['cms', 'wordpress', 'wp', 'joomla', 'drupal'])) {
            return 'cms';
        }
        // Match Hosting
        if (in_array($type, ['hosting', 'cpanel', 'plesk', 'ispmanager', 'panel'])) {
            return 'hosting';
        }
        
        // Casing/name fallback checks
        $combined = $type . ' ' . $name;
        if (str_contains($combined, 'ssh') || str_contains($combined, 'sftp')) {
            return 'ssh';
        }
        if (str_contains($combined, 'db') || str_contains($combined, 'mysql') || str_contains($combined, 'database') || str_contains($combined, 'postgres') || str_contains($combined, 'phpmyadmin') || str_contains($combined, 'redis') || str_contains($combined, 'sql')) {
            return 'db';
        }
        if (str_contains($combined, 'wordpress') || str_contains($combined, 'wp-admin') || str_contains($combined, 'cms') || str_contains($combined, 'opencart') || str_contains($combined, 'prestashop') || str_contains($combined, 'admin panel') || str_contains($combined, 'bitrix') || str_contains($combined, 'modx')) {
            return 'cms';
        }
        if (str_contains($combined, 'hosting') || str_contains($combined, 'cpanel') || str_contains($combined, 'plesk') || str_contains($combined, 'panel') || str_contains($combined, 'server') || str_contains($combined, 'vps') || str_contains($combined, 'ftp') || str_contains($combined, 'beget') || str_contains($combined, 'hetzner') || str_contains($combined, 'reg.ru')) {
            return 'hosting';
        }
        if (str_contains($combined, 'email') || str_contains($combined, 'mail') || str_contains($combined, 'smtp') || str_contains($combined, 'yandex mail') || str_contains($combined, 'google workspace') || str_contains($combined, 'pop3') || str_contains($combined, 'imap')) {
            return 'email';
        }
        if (str_contains($combined, 'payment') || str_contains($combined, 'gateway') || str_contains($combined, 'stripe') || str_contains($combined, 'paypal') || str_contains($combined, 'fondy') || str_contains($combined, 'yookassa')) {
            return 'payment_gateway';
        }

        return 'other';
    }
}
