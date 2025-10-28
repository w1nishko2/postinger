<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class BotTestController extends Controller
{
    /**
     * Тестовая страница для проверки бота
     */
    public function test(Bot $bot)
    {
        $telegramService = new TelegramService();
        
        try {
            // Получаем информацию о webhook
            $webhookInfo = \Http::get("https://api.telegram.org/bot{$bot->token}/getWebhookInfo")->json();
            
            // Получаем информацию о боте
            $botInfo = \Http::get("https://api.telegram.org/bot{$bot->token}/getMe")->json();
            
            return view('bots.test', compact('bot', 'webhookInfo', 'botInfo'));
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Установить webhook
     */
    public function setWebhook(Bot $bot)
    {
        try {
            $webhookUrl = route('telegram.webhook', ['bot' => $bot->id]);
            
            $response = \Http::post("https://api.telegram.org/bot{$bot->token}/setWebhook", [
                'url' => $webhookUrl,
            ])->json();
            
            if ($response['ok']) {
                $bot->update(['webhook_url' => $webhookUrl]);
                return back()->with('success', 'Webhook успешно установлен!');
            } else {
                return back()->with('error', 'Ошибка: ' . ($response['description'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }
    
    /**
     * Удалить webhook
     */
    public function deleteWebhook(Bot $bot)
    {
        try {
            $response = \Http::post("https://api.telegram.org/bot{$bot->token}/deleteWebhook")->json();
            
            if ($response['ok']) {
                return back()->with('success', 'Webhook удалён!');
            } else {
                return back()->with('error', 'Ошибка: ' . ($response['description'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }
}
