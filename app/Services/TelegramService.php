<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $adminId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->adminId = config('services.telegram.admin_id');
    }

    public function sendMessage($message, $chatId = null)
    {
        if (!$this->botToken) {
            Log::warning('Telegram Bot Token not configured.');
            return false;
        }

        $chatId = $chatId ?? $this->adminId;

        if (!$chatId) {
            Log::warning('Telegram Chat ID not configured.');
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API Error: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram Service Exception: ' . $e->getMessage());
            return false;
        }
    }
}
