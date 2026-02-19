<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Vault;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleSheetVaultImporter
{
    public function import(string $url, Project $project)
    {
        // 1. Transform URL to export format
        $csvUrl = $this->transformUrlToCsv($url);

        // 2. Fetch CSV
        $response = Http::get($csvUrl);
        if ($response->failed()) {
            throw new \Exception('Failed to fetch Google Sheet. Please ensure the link is publicly accessible or shared with the server.');
        }

        $csvData = $response->body();
        $rows = array_map('str_getcsv', explode("\n", $csvData));

        // Remove header if it exists (heuristic: check for common header keywords)
        if (isset($rows[0])) {
            $firstrow = array_map('strtolower', $rows[0]);
            $keywords = ['email', 'pass', 'username', 'service', 'phone', 'extra'];
            foreach ($firstrow as $cell) {
                foreach ($keywords as $keyword) {
                    if (Str::contains($cell, $keyword)) {
                        array_shift($rows);
                        break 2;
                    }
                }
            }
        }

        $count = 0;
        foreach ($rows as $row) {
            if (count($row) < 3)
                continue; // Skip empty or invalid rows

            $service = trim($row[0] ?? '');
            $col2 = trim($row[1] ?? ''); // Username/Email
            $col3 = trim($row[2] ?? ''); // Password
            $col4 = trim($row[3] ?? ''); // Extra (Phone/URL)

            if (empty($service))
                continue;

            $this->createVaultEntry($project, $service, $col2, $col3, $col4);
            $count++;
        }

        return $count;
    }

    private function transformUrlToCsv(string $url): string
    {
        // https://docs.google.com/spreadsheets/d/ID/edit?gid=0#gid=0 -> /export?format=csv&gid=0

        // simple regex to extract ID and GID
        preg_match('/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        $id = $matches[1] ?? null;

        if (!$id) {
            throw new \Exception('Invalid Google Sheet URL.');
        }

        $gid = '0';
        if (preg_match('/gid=([0-9]+)/', $url, $matches)) {
            $gid = $matches[1];
        }

        return "https://docs.google.com/spreadsheets/d/{$id}/export?format=csv&gid={$gid}";
    }

    private function createVaultEntry(Project $project, string $service, string $username, string $password, string $extra)
    {
        $type = 'other';
        $name = $service;
        $payload = [];
        $lowerService = strtolower($service);

        if (Str::contains($lowerService, 'gmail')) {
            $type = 'gmail';
            $payload = [
                ['name' => 'Email', 'value' => $username, 'is_secret' => false],
                ['name' => 'Password', 'value' => $password, 'is_secret' => true],
                ['name' => 'Phone', 'value' => $extra, 'is_secret' => false],
            ];
        } elseif (Str::contains($lowerService, 'namecheap')) {
            $type = 'hosting';
            $payload = [
                ['name' => 'Username', 'value' => $username, 'is_secret' => false],
                ['name' => 'Password', 'value' => $password, 'is_secret' => true],
                ['name' => 'Login URL', 'value' => 'https://www.namecheap.com/myaccount/login/?ReturnUrl=%2f', 'is_secret' => false],
            ];
        } elseif (Str::contains($lowerService, 'privateemail')) {
            $type = 'email';
            $name = 'Private Email'; // Normalize name? Or keep "privateemail"? User used "privateemail" in lower case in example. 
            // User example: "privateemail" -> "info@..."
            // Let's keep formatted name if possible, or just use input. 
            // User said: "primateemail [sic] это будет email"
            if ($lowerService === 'privateemail')
                $name = 'Private Email';

            $payload = [
                ['name' => 'Email', 'value' => $username, 'is_secret' => false],
                ['name' => 'Password', 'value' => $password, 'is_secret' => true],
            ];
        } elseif (Str::contains($lowerService, 'wp') || Str::contains($lowerService, 'wordpress')) {
            $type = 'wordpress';
            $name = 'Wordpress Admin Login';

            // "ссылка может братся из проекта и добавлятся /wp-admin"
            // If extra is empty, use project domain + /wp-admin
            $loginUrl = $extra;
            if (empty($loginUrl) && $project->domain) {
                $loginUrl = rtrim($project->domain, '/') . '/wp-admin';
                if (!Str::startsWith($loginUrl, 'http')) {
                    $loginUrl = 'https://' . $loginUrl;
                }
            }

            $payload = [
                ['name' => 'Username', 'value' => $username, 'is_secret' => false],
                ['name' => 'Password', 'value' => $password, 'is_secret' => true],
                ['name' => 'Login URL', 'value' => $loginUrl, 'is_secret' => false],
            ];
        } else {
            // Default fallback
            $payload = [
                ['name' => 'Username', 'value' => $username, 'is_secret' => false],
                ['name' => 'Password', 'value' => $password, 'is_secret' => true],
                ['name' => 'Extra', 'value' => $extra, 'is_secret' => false],
            ];
        }

        // Create Vault
        Vault::create([
            'project_id' => $project->id,
            'name' => $name,
            'type' => $type,
            'payload' => $payload, // The model casts this to encrypted JSON
        ]);
    }
}
