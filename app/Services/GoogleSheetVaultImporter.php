<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Vault;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleSheetVaultImporter
{
    /**
     * Импорт данных из Google Sheets в Vault проекта.
     */
    public function import(string $url, Project $project): int
    {
        // 1. Превращаем URL в формат экспорта CSV
        $csvUrl = $this->transformUrlToCsv($url);

        // 2. Получаем CSV данные
        $response = Http::get($csvUrl);

        if ($response->failed()) {
            Log::error('Google Sheet Import HTTP Error', ['status' => $response->status(), 'url' => $csvUrl]);
            throw new \Exception('Не удалось получить данные из Google Sheet. Статус: ' . $response->status());
        }

        $contentType = $response->header('Content-Type');
        $body = $response->body();

        // Проверка, не получили ли мы HTML (страница логина Google) вместо CSV
        if (str_contains(strtolower($contentType), 'html') || str_contains($body, '<!doctype html')) {
            throw new \Exception("URL вернул HTML вместо CSV. Убедитесь, что доступ к таблице открыт 'Всем, у кого есть ссылка'.");
        }

        $rows = array_map('str_getcsv', explode("\n", $body));
        $count = 0;

        foreach ($rows as $row) {
            // Очищаем каждый элемент строки от лишних пробелов
            $row = array_map('trim', $row);

            // Согласно вашему файлу:
            // [0] - обычно пусто
            // [1] - Название сервиса (Gmail, Namecheap, WP и т.д.)
            // [2] - Email / Username
            // [3] - Password
            // [4] - Phone / URL

            $service = $row[1] ?? '';
            $username = $row[2] ?? '';
            $password = $row[3] ?? '';
            $extra = $row[4] ?? '';

            // Пропускаем строки заголовков или пустые строки
            if (empty($service) || empty($password)) {
                continue;
            }

            // Игнорируем технические строки (например, если в ячейке написано "email" или "pass")
            if (in_array(strtolower($service), ['service', 'email', 'namecheap', 'gmail', 'privateemail', 'wp'])) {
                if (in_array(strtolower($username), ['email', 'username', 'pass']))
                    continue;
            }

            $this->createVaultEntry($project, $service, $username, $password, $extra);
            $count++;
        }

        return $count;
    }

    /**
     * Преобразование ссылки Google Sheets в ссылку для прямого скачивания CSV.
     */
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

    /**
     * Создание записи в Vault с правильным маппингом полей.
     */
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

            // Если URL не указан, берем домен проекта
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
            // Универсальный вариант для прочих сервисов
            $payload = [
                ['name' => 'Username', 'value' => $username, 'is_secret' => false],
                ['name' => 'Password', 'value' => $password, 'is_secret' => true],
            ];
            if (!empty($extra)) {
                $payload[] = ['name' => 'Extra', 'value' => $extra, 'is_secret' => false];
            }
        }

        // Создаем запись в БД
        return Vault::create([
            'project_id' => $project->id,
            'name' => $name,
            'type' => $type,
            'payload' => $payload, // Поле должно быть cast к array или json в модели
        ]);
    }
}