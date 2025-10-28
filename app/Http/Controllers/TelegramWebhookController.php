<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Channel;
use App\Models\Post;
use App\Models\UserSession;
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

            // Обработка callback query (нажатие на inline кнопки)
            if (isset($update['callback_query'])) {
                return $this->handleCallbackQuery($bot, $update['callback_query']);
            }

            // Проверяем наличие сообщения
            if (!isset($update['message'])) {
                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $userId = $message['from']['id'] ?? null;
            $chatId = $message['chat']['id'] ?? null;

            // Находим клиента по telegram_user_id
            $client = $bot->client;
            
            if (!$client || $client->telegram_user_id != $userId) {
                // Отправляем сообщение о том, что пользователь не авторизован
                $this->telegramService->sendMessage(
                    $bot,
                    $chatId,
                    '❌ У вас нет доступа к этому боту.'
                );
                return response()->json(['ok' => true]);
            }

            // Получаем или создаем сессию пользователя
            $session = UserSession::firstOrCreate(
                [
                    'bot_id' => $bot->id,
                    'telegram_user_id' => $userId,
                ],
                [
                    'client_id' => $client->id,
                    'telegram_chat_id' => $chatId,
                    'state' => 'idle',
                ]
            );

            // Обработка команд
            if (isset($message['text']) && str_starts_with($message['text'], '/')) {
                return $this->handleCommand($bot, $client, $session, $message);
            }

            // Обработка в зависимости от состояния сессии
            return $this->handleMessage($bot, $client, $session, $message);

        } catch (\Exception $e) {
            Log::error('Webhook handling error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Обработка команд
     */
    private function handleCommand(Bot $bot, $client, UserSession $session, array $message): \Illuminate\Http\JsonResponse
    {
        $command = explode(' ', $message['text'])[0];
        $chatId = $message['chat']['id'];

        switch ($command) {
            case '/start':
                $keyboard = $this->telegramService->createReplyKeyboard([
                    [['text' => '📝 Создать запись']],
                    [['text' => '📋 Мои каналы'], ['text' => '❌ Отмена']],
                ]);

                $this->telegramService->sendMessage(
                    $bot,
                    $chatId,
                    "👋 Добро пожаловать!\n\n" .
                    "Используйте кнопку <b>📝 Создать запись</b> для создания нового поста.\n\n" .
                    "Вы можете отправлять:\n" .
                    "• Текстовые сообщения\n" .
                    "• Фотографии с подписями\n" .
                    "• Видео с подписями\n" .
                    "• Несколько фото/видео (альбомы)",
                    $keyboard
                );

                $session->update(['state' => 'idle']);
                break;

            case '/cancel':
                $this->cancelSession($bot, $session);
                break;

            default:
                $this->telegramService->sendMessage(
                    $bot,
                    $chatId,
                    '❓ Неизвестная команда. Используйте /start для начала работы.'
                );
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Обработка сообщений в зависимости от состояния
     */
    private function handleMessage(Bot $bot, $client, UserSession $session, array $message): \Illuminate\Http\JsonResponse
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        // Обработка кнопок главного меню
        if ($text === '📝 Создать запись') {
            return $this->startCreatingPost($bot, $client, $session, $chatId);
        }

        if ($text === '📋 Мои каналы') {
            return $this->showChannels($bot, $client, $chatId);
        }

        if ($text === '❌ Отмена') {
            return $this->cancelSession($bot, $session);
        }

        // Обработка в зависимости от состояния
        switch ($session->state) {
            case 'creating_post':
                return $this->collectPostContent($bot, $client, $session, $message);

            case 'idle':
            default:
                $this->telegramService->sendMessage(
                    $bot,
                    $chatId,
                    'Используйте кнопку <b>📝 Создать запись</b> для создания поста.'
                );
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Начать создание поста
     */
    private function startCreatingPost(Bot $bot, $client, UserSession $session, string $chatId): \Illuminate\Http\JsonResponse
    {
        $channels = Channel::where('client_id', $client->id)
            ->where('bot_id', $bot->id)
            ->where('is_active', true)
            ->get();

        if ($channels->isEmpty()) {
            $this->telegramService->sendMessage(
                $bot,
                $chatId,
                '❌ У вас нет активных каналов. Добавьте каналы через административную панель.'
            );
            return response()->json(['ok' => true]);
        }

        $keyboard = $this->telegramService->createReplyKeyboard([
            [['text' => '✅ Готово, отправить'], ['text' => '❌ Отмена']],
        ]);

        $this->telegramService->sendMessage(
            $bot,
            $chatId,
            "📝 <b>Создание новой записи</b>\n\n" .
            "Отправьте текст, фото или видео для вашего поста.\n" .
            "Вы можете отправить несколько сообщений - они будут объединены.\n\n" .
            "Когда закончите, нажмите <b>✅ Готово, отправить</b>",
            $keyboard
        );

        $session->update([
            'state' => 'creating_post',
            'post_content' => null,
            'media_type' => null,
            'media_files' => [],
            'selected_channels' => [],
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Собрать контент поста
     */
    private function collectPostContent(Bot $bot, $client, UserSession $session, array $message): \Illuminate\Http\JsonResponse
    {
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        // Проверка на кнопку "Готово"
        if ($text === '✅ Готово, отправить') {
            if (empty($session->post_content) && empty($session->media_files)) {
                $this->telegramService->sendMessage(
                    $bot,
                    $chatId,
                    '❌ Пост пустой! Добавьте текст или медиа файлы.'
                );
                return response()->json(['ok' => true]);
            }

            return $this->showChannelSelection($bot, $client, $session, $chatId);
        }

        if ($text === '❌ Отмена') {
            return $this->cancelSession($bot, $session);
        }

        // Сбор контента
        $content = $session->post_content ?? '';
        $mediaFiles = $session->media_files ?? [];
        $mediaType = $session->media_type ?? 'text';

        // Добавление текста
        if (isset($message['text'])) {
            $content .= ($content ? "\n\n" : '') . $message['text'];
        }

        // Добавление подписи к медиа
        if (isset($message['caption'])) {
            $content .= ($content ? "\n\n" : '') . $message['caption'];
        }

        // Обработка фото
        if (isset($message['photo'])) {
            $photo = end($message['photo']);
            $mediaFiles[] = $photo['file_id'];
            $mediaType = empty($mediaFiles) || $mediaType === 'text' ? 'photo' : 'mixed';
        }

        // Обработка видео
        if (isset($message['video'])) {
            $mediaFiles[] = $message['video']['file_id'];
            $mediaType = empty($mediaFiles) || $mediaType === 'text' ? 'video' : 'mixed';
        }

        // Обработка документа
        if (isset($message['document'])) {
            $mediaFiles[] = $message['document']['file_id'];
            $mediaType = 'document';
        }

        $session->update([
            'post_content' => $content,
            'media_files' => $mediaFiles,
            'media_type' => $mediaType,
        ]);

        $mediaCount = count($mediaFiles);
        $this->telegramService->sendMessage(
            $bot,
            $chatId,
            "✅ Добавлено!\n\n" .
            "📄 Текст: " . (strlen($content) > 0 ? mb_substr($content, 0, 50) . '...' : 'нет') . "\n" .
            "📎 Медиа файлов: {$mediaCount}\n\n" .
            "Продолжайте добавлять контент или нажмите <b>✅ Готово, отправить</b>"
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Показать выбор каналов
     */
    private function showChannelSelection(Bot $bot, $client, UserSession $session, string $chatId): \Illuminate\Http\JsonResponse
    {
        $channels = Channel::where('client_id', $client->id)
            ->where('bot_id', $bot->id)
            ->where('is_active', true)
            ->get();

        $buttons = [];
        
        // Кнопка "Все каналы"
        $buttons[] = [
            ['text' => '✅ Выбрать все каналы', 'callback_data' => 'select_all_channels']
        ];

        // Кнопки для каждого канала
        foreach ($channels as $channel) {
            $buttons[] = [
                [
                    'text' => $channel->name,
                    'callback_data' => 'toggle_channel_' . $channel->id
                ]
            ];
        }

        // Кнопка подтверждения
        $buttons[] = [
            ['text' => '🚀 Опубликовать', 'callback_data' => 'publish_post'],
            ['text' => '❌ Отмена', 'callback_data' => 'cancel_post']
        ];

        $keyboard = $this->telegramService->createInlineKeyboard($buttons);

        $preview = $this->getPostPreview($session);

        $this->telegramService->sendMessage(
            $bot,
            $chatId,
            "📢 <b>Выберите каналы для публикации</b>\n\n" .
            "<b>Предварительный просмотр:</b>\n" .
            $preview . "\n\n" .
            "Выберите каналы, в которые хотите опубликовать этот пост:",
            $keyboard
        );

        $session->update(['state' => 'selecting_channels']);

        return response()->json(['ok' => true]);
    }

    /**
     * Обработка нажатий на inline кнопки
     */
    private function handleCallbackQuery(Bot $bot, array $callbackQuery): \Illuminate\Http\JsonResponse
    {
        $callbackId = $callbackQuery['id'];
        $data = $callbackQuery['data'];
        $userId = $callbackQuery['from']['id'];
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];

        $session = UserSession::where('bot_id', $bot->id)
            ->where('telegram_user_id', $userId)
            ->first();

        if (!$session) {
            $this->telegramService->answerCallbackQuery($bot, $callbackId, 'Сессия не найдена', true);
            return response()->json(['ok' => true]);
        }

        $client = $session->client;

        // Выбрать все каналы
        if ($data === 'select_all_channels') {
            $channels = Channel::where('client_id', $client->id)
                ->where('bot_id', $bot->id)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            $session->update(['selected_channels' => $channels]);

            $this->telegramService->answerCallbackQuery($bot, $callbackId, '✅ Выбраны все каналы');
            $this->updateChannelSelectionMessage($bot, $session, $chatId, $messageId);
            return response()->json(['ok' => true]);
        }

        // Переключить канал
        if (str_starts_with($data, 'toggle_channel_')) {
            $channelId = (int) str_replace('toggle_channel_', '', $data);
            $selectedChannels = $session->selected_channels ?? [];

            if (in_array($channelId, $selectedChannels)) {
                $selectedChannels = array_diff($selectedChannels, [$channelId]);
                $message = '❌ Канал убран из списка';
            } else {
                $selectedChannels[] = $channelId;
                $message = '✅ Канал добавлен';
            }

            $session->update(['selected_channels' => array_values($selectedChannels)]);

            $this->telegramService->answerCallbackQuery($bot, $callbackId, $message);
            $this->updateChannelSelectionMessage($bot, $session, $chatId, $messageId);
            return response()->json(['ok' => true]);
        }

        // Опубликовать пост
        if ($data === 'publish_post') {
            $selectedChannels = $session->selected_channels ?? [];

            if (empty($selectedChannels)) {
                $this->telegramService->answerCallbackQuery($bot, $callbackId, '❌ Выберите хотя бы один канал', true);
                return response()->json(['ok' => true]);
            }

            return $this->publishPost($bot, $client, $session, $chatId, $callbackId);
        }

        // Отменить публикацию
        if ($data === 'cancel_post') {
            $this->telegramService->answerCallbackQuery($bot, $callbackId, 'Отменено');
            return $this->cancelSession($bot, $session);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Обновить сообщение с выбором каналов
     */
    private function updateChannelSelectionMessage(Bot $bot, UserSession $session, string $chatId, int $messageId): void
    {
        $client = $session->client;
        $selectedChannels = $session->selected_channels ?? [];

        $channels = Channel::where('client_id', $client->id)
            ->where('bot_id', $bot->id)
            ->where('is_active', true)
            ->get();

        $buttons = [];
        
        // Кнопка "Все каналы"
        $allSelected = count($selectedChannels) === $channels->count();
        $buttons[] = [
            ['text' => ($allSelected ? '✅' : '☑️') . ' Выбрать все каналы', 'callback_data' => 'select_all_channels']
        ];

        // Кнопки для каждого канала
        foreach ($channels as $channel) {
            $isSelected = in_array($channel->id, $selectedChannels);
            $buttons[] = [
                [
                    'text' => ($isSelected ? '✅ ' : '') . $channel->name,
                    'callback_data' => 'toggle_channel_' . $channel->id
                ]
            ];
        }

        // Кнопка подтверждения
        $buttons[] = [
            ['text' => '🚀 Опубликовать (' . count($selectedChannels) . ')', 'callback_data' => 'publish_post'],
            ['text' => '❌ Отмена', 'callback_data' => 'cancel_post']
        ];

        $keyboard = $this->telegramService->createInlineKeyboard($buttons);
        $preview = $this->getPostPreview($session);

        $this->telegramService->editMessageText(
            $bot,
            $chatId,
            $messageId,
            "📢 <b>Выберите каналы для публикации</b>\n\n" .
            "<b>Предварительный просмотр:</b>\n" .
            $preview . "\n\n" .
            "Выбрано каналов: <b>" . count($selectedChannels) . "</b>",
            $keyboard
        );
    }

    /**
     * Опубликовать пост
     */
    private function publishPost(Bot $bot, $client, UserSession $session, string $chatId, string $callbackId): \Illuminate\Http\JsonResponse
    {
        $selectedChannels = $session->selected_channels ?? [];
        $channels = Channel::whereIn('id', $selectedChannels)->get();

        $successCount = 0;
        $failCount = 0;

        foreach ($channels as $channel) {
            try {
                $post = Post::create([
                    'channel_id' => $channel->id,
                    'client_id' => $client->id,
                    'content' => $session->post_content,
                    'media_type' => $session->media_type ?? 'text',
                    'media_files' => $session->media_files ?? [],
                    'status' => 'pending',
                ]);

                if ($this->telegramService->publishPost($post)) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Exception $e) {
                Log::error('Error publishing to channel: ' . $e->getMessage());
                $failCount++;
            }
        }

        $keyboard = $this->telegramService->createReplyKeyboard([
            [['text' => '📝 Создать запись']],
            [['text' => '📋 Мои каналы'], ['text' => '❌ Отмена']],
        ]);

        $message = "🎉 <b>Публикация завершена!</b>\n\n";
        $message .= "✅ Успешно: {$successCount}\n";
        if ($failCount > 0) {
            $message .= "❌ Ошибок: {$failCount}\n";
        }

        $this->telegramService->answerCallbackQuery($bot, $callbackId, 'Публикация началась...');
        $this->telegramService->sendMessage($bot, $chatId, $message, $keyboard);

        // Сброс сессии
        $session->update([
            'state' => 'idle',
            'post_content' => null,
            'media_type' => null,
            'media_files' => [],
            'selected_channels' => [],
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Отменить сессию
     */
    private function cancelSession(Bot $bot, UserSession $session): \Illuminate\Http\JsonResponse
    {
        $keyboard = $this->telegramService->createReplyKeyboard([
            [['text' => '📝 Создать запись']],
            [['text' => '📋 Мои каналы'], ['text' => '❌ Отмена']],
        ]);

        $this->telegramService->sendMessage(
            $bot,
            $session->telegram_chat_id,
            '❌ Действие отменено. Используйте кнопки для начала работы.',
            $keyboard
        );

        $session->update([
            'state' => 'idle',
            'post_content' => null,
            'media_type' => null,
            'media_files' => [],
            'selected_channels' => [],
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Показать список каналов
     */
    private function showChannels(Bot $bot, $client, string $chatId): \Illuminate\Http\JsonResponse
    {
        $channels = Channel::where('client_id', $client->id)
            ->where('bot_id', $bot->id)
            ->where('is_active', true)
            ->get();

        if ($channels->isEmpty()) {
            $message = "📋 <b>Ваши каналы</b>\n\n❌ У вас нет активных каналов.";
        } else {
            $message = "📋 <b>Ваши каналы</b>\n\n";
            foreach ($channels as $index => $channel) {
                $message .= ($index + 1) . ". " . $channel->name . "\n";
                $message .= "   ID: <code>" . $channel->channel_id . "</code>\n\n";
            }
        }

        $this->telegramService->sendMessage($bot, $chatId, $message);

        return response()->json(['ok' => true]);
    }

    /**
     * Получить предварительный просмотр поста
     */
    private function getPostPreview(UserSession $session): string
    {
        $preview = '';

        if ($session->post_content) {
            $content = mb_strlen($session->post_content) > 100 
                ? mb_substr($session->post_content, 0, 100) . '...' 
                : $session->post_content;
            $preview .= "📄 {$content}\n\n";
        }

        $mediaFiles = $session->media_files ?? [];
        if (!empty($mediaFiles)) {
            $preview .= "📎 Медиа файлов: " . count($mediaFiles) . "\n";
            $preview .= "📷 Тип: " . ($session->media_type ?? 'text') . "\n";
        }

        return $preview ?: '(Пустой пост)';
    }
}
