<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Channel;
use App\Models\Post;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    private $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Обработка входящих сообщений от Telegram
     */
    public function handle(Request $request, $botId)
    {
        try {
            $bot = Bot::findOrFail($botId);
            $update = $request->all();

            Log::info('Telegram webhook received', ['bot_id' => $botId, 'update' => $update]);

            // Проверяем наличие сообщения
            if (!isset($update['message'])) {
                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $userId = $message['from']['id'] ?? null;

            // Находим клиента по telegram_user_id
            $client = $bot->client;
            
            if (!$client || $client->telegram_user_id != $userId) {
                // Отправляем сообщение о том, что пользователь не авторизован
                $this->telegramService->sendMessage(
                    $bot,
                    $message['chat']['id'],
                    '❌ У вас нет доступа к этому боту.'
                );
                return response()->json(['ok' => true]);
            }

            // Получаем активные каналы клиента для этого бота
            $channels = Channel::where('client_id', $client->id)
                ->where('bot_id', $bot->id)
                ->where('is_active', true)
                ->get();

            if ($channels->isEmpty()) {
                $this->telegramService->sendMessage(
                    $bot,
                    $message['chat']['id'],
                    '❌ У вас нет привязанных каналов.'
                );
                return response()->json(['ok' => true]);
            }

            // Обработка контента
            $post = $this->processMessage($message, $client, $channels);

            if ($post) {
                // Публикуем пост в каналы
                foreach ($channels as $channel) {
                    $channelPost = Post::create([
                        'channel_id' => $channel->id,
                        'client_id' => $client->id,
                        'content' => $post['content'],
                        'media_type' => $post['media_type'],
                        'media_files' => $post['media_files'],
                        'status' => 'pending',
                    ]);

                    $this->telegramService->publishPost($channelPost);
                }

                $this->telegramService->sendMessage(
                    $bot,
                    $message['chat']['id'],
                    '✅ Сообщение успешно опубликовано в ' . $channels->count() . ' канал(ов)!'
                );
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Webhook handling error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Обработка сообщения и подготовка данных для поста
     */
    private function processMessage($message, $client, $channels)
    {
        $content = $message['text'] ?? $message['caption'] ?? '';
        $mediaFiles = [];
        $mediaType = 'text';

        // Обработка фото
        if (isset($message['photo'])) {
            $mediaType = 'photo';
            $photo = end($message['photo']); // Берем самое большое фото
            $mediaFiles[] = $photo['file_id'];
        }

        // Обработка видео
        if (isset($message['video'])) {
            $mediaType = 'video';
            $mediaFiles[] = $message['video']['file_id'];
        }

        // Обработка документа
        if (isset($message['document'])) {
            $mediaType = 'document';
            $mediaFiles[] = $message['document']['file_id'];
        }

        // Обработка медиагруппы (альбома)
        if (isset($message['media_group_id'])) {
            $mediaType = 'mixed';
            // Здесь нужна дополнительная логика для сбора всех элементов медиагруппы
            // Это требует хранения временных данных о медиагруппе
        }

        if (empty($content) && empty($mediaFiles)) {
            return null;
        }

        return [
            'content' => $content,
            'media_type' => $mediaType,
            'media_files' => $mediaFiles,
        ];
    }
}
