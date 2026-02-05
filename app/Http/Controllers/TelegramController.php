<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function index()
    {
        $messages = \App\Models\TelegramMessage::latest()->paginate(20);
        return view('telegram.index', compact('messages'));
    }

    public function handleWebhook(Request $request)
    {
        $data = $request->all();

        if (isset($data['message'])) {
            \App\Models\TelegramMessage::create([
                'chat_id' => $data['message']['chat']['id'],
                'sender_name' => $data['message']['from']['username'] ?? $data['message']['from']['first_name'],
                'message_text' => $data['message']['text'] ?? '',
                'payload' => $data,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
