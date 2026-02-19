<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Vault;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleSheetVaultImporter
{
    public function import(string $url, Project $project): int
    {
        $csvUrl = $this->transformUrlToCsv($url);

        $response = Http::get($csvUrl);

        if ($response->failed()) {
            Log::error('Google Sheet Import HTTP Error', ['status' => $response->status(), 'url' => $csvUrl]);
            throw new \Exception('Не удалось получить данные из Google Sheet. Статус: ' . $response->status());
        }

        $contentType = $response->header('Content-Type');
        $body = $response->body();

        if (str_contains(strtolower($contentType), 'html') || str_contains($body, '<!doctype html')) {
            throw new \Exception("URL вернул HTML вместо CSV. Убедитесь, что доступ к таблице открыт 'Всем, у кого есть ссылка'.");
        }

        $rows = array_map('str_getcsv', explode("\n", $body));
        $count = 0;

        foreach ($rows as $row) {
            $row = array_map('trim', $row);

            $service = $row[1] ?? '';
            $username = $row[2] ?? '';
            $password = $row[3] ?? '';
            $extra = $row[4] ?? '';

            if (empty($service) || empty($password)) {
                continue;
            }
            if (in_array(strtolower($service), ['service', 'email', 'namecheap', 'gmail', 'privateemail', 'wp'])) {
                if (in_array(strtolower($username), ['email', 'username', 'pass']))
                    continue;
            }

            $this->createVaultEntry($project, $service, $username, $password, $extra);
            $count++;
        }

        return $count;
    }

    private function transformUrlToCsv(string $url): string
    {
        preg_match('/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        $id = $matches[1] ?? null;

        if (!$id) {
            throw new \Exception('Некорректная ссылка на Google Sheet.');
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
            $name = 'Namecheap';
            $payload = [
                ['name' => 'Username', 'value' => $username, 'is_secret' => false],
                ['name' => 'Password', 'value' => $password, 'is_secret' => true],
                ['name' => 'Login URL', 'value' => 'https://www.namecheap.com/myaccount/login/', 'is_secret' => false],
            ];
        } elseif (Str::contains($lowerService, 'privateemail')) {
            $type = 'email';
            $name = 'Private Email';
            $payload = [
                ['name' => 'Email', 'value' => $username, 'is_secret' => false],
                ['name' => 'Password', 'value' => $password, 'is_secret' => true],
            ];
        } elseif ($lowerService === 'wp' || Str::contains($lowerService, 'wordpress')) {
            $type = 'wordpress';
            $name = 'WordPress Admin';

            $loginUrl = $extra;
            if (empty($loginUrl) && $project->domain) {
                $domain = rtrim($project->domain, '/');
                $loginUrl = str_starts_with($domain, 'http') ? $domain : "https://{$domain}";
                $loginUrl .= '/wp-admin';
            }

            $payload = [
                ['name' => 'Username', 'value' => $username, 'is_secret' => false],
                ['name' => 'Password', 'value' => $password, 'is_secret' => true],
                ['name' => 'Login URL', 'value' => $loginUrl, 'is_secret' => false],
            ];
        } else {
            $payload = [
                ['name' => 'Username', 'value' => $username, 'is_secret' => false],
                ['name' => 'Password', 'value' => $password, 'is_secret' => true],
            ];
            if (!empty($extra)) {
                $payload[] = ['name' => 'Extra', 'value' => $extra, 'is_secret' => false];
            }
        }

        return Vault::create([
            'project_id' => $project->id,
            'name' => $name,
            'type' => $type,
            'payload' => $payload,
        ]);
    }
}