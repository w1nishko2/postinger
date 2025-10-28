<?php

namespace App\Services;

use App\Models\Bot;
use App\Models\Channel;
use App\Models\Post;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramService
{
    /**
     * Отправить текстовое сообщение в канал
     */
    public function sendMessage(Bot $bot, string $chatId, string $text): array
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$bot->token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            $result = $response->json();
            
            if (!$result['ok']) {
                throw new Exception($result['description'] ?? 'Unknown error');
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Telegram sendMessage error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Отправить фото в канал
     */
    public function sendPhoto(Bot $bot, string $chatId, string $photo, ?string $caption = null): array
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$bot->token}/sendPhoto", [
                'chat_id' => $chatId,
                'photo' => $photo,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ]);

            $result = $response->json();
            
            if (!$result['ok']) {
                throw new Exception($result['description'] ?? 'Unknown error');
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Telegram sendPhoto error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Отправить видео в канал
     */
    public function sendVideo(Bot $bot, string $chatId, string $video, ?string $caption = null): array
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$bot->token}/sendVideo", [
                'chat_id' => $chatId,
                'video' => $video,
                'caption' => $caption,
                'parse_mode' => 'HTML',
            ]);

            $result = $response->json();
            
            if (!$result['ok']) {
                throw new Exception($result['description'] ?? 'Unknown error');
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Telegram sendVideo error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Отправить медиагруппу (альбом) в канал
     */
    public function sendMediaGroup(Bot $bot, string $chatId, array $media): array
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$bot->token}/sendMediaGroup", [
                'chat_id' => $chatId,
                'media' => json_encode($media),
            ]);

            $result = $response->json();
            
            if (!$result['ok']) {
                throw new Exception($result['description'] ?? 'Unknown error');
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Telegram sendMediaGroup error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Получить файл от Telegram
     */
    public function getFile(Bot $bot, string $fileId): array
    {
        try {
            $response = Http::get("https://api.telegram.org/bot{$bot->token}/getFile", [
                'file_id' => $fileId,
            ]);

            $result = $response->json();
            
            if (!$result['ok']) {
                throw new Exception($result['description'] ?? 'Unknown error');
            }

            return $result['result'];
        } catch (Exception $e) {
            Log::error('Telegram getFile error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Скачать файл от Telegram
     */
    public function downloadFile(Bot $bot, string $filePath, string $savePath): bool
    {
        try {
            $fileUrl = "https://api.telegram.org/file/bot{$bot->token}/{$filePath}";
            $content = Http::get($fileUrl)->body();
            
            return Storage::put($savePath, $content);
        } catch (Exception $e) {
            Log::error('Telegram downloadFile error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Установить webhook для бота
     */
    public function setWebhook(Bot $bot, string $url): array
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$bot->token}/setWebhook", [
                'url' => $url,
            ]);

            $result = $response->json();
            
            if (!$result['ok']) {
                throw new Exception($result['description'] ?? 'Unknown error');
            }

            return $result;
        } catch (Exception $e) {
            Log::error('Telegram setWebhook error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Публикация поста в каналы
     */
    public function publishPost(Post $post): bool
    {
        try {
            $channel = $post->channel;
            $bot = $channel->bot;

            $result = null;

            switch ($post->media_type) {
                case 'text':
                    $result = $this->sendMessage($bot, $channel->channel_id, $post->content);
                    break;

                case 'photo':
                    $photos = $post->media_files ?? [];
                    if (count($photos) === 1) {
                        $result = $this->sendPhoto($bot, $channel->channel_id, $photos[0], $post->content);
                    } else {
                        $media = array_map(function ($photo) use ($post) {
                            return [
                                'type' => 'photo',
                                'media' => $photo,
                                'caption' => $post->content,
                            ];
                        }, $photos);
                        $result = $this->sendMediaGroup($bot, $channel->channel_id, $media);
                    }
                    break;

                case 'video':
                    $videos = $post->media_files ?? [];
                    if (count($videos) === 1) {
                        $result = $this->sendVideo($bot, $channel->channel_id, $videos[0], $post->content);
                    } else {
                        $media = array_map(function ($video) use ($post) {
                            return [
                                'type' => 'video',
                                'media' => $video,
                                'caption' => $post->content,
                            ];
                        }, $videos);
                        $result = $this->sendMediaGroup($bot, $channel->channel_id, $media);
                    }
                    break;

                case 'mixed':
                    // Обработка смешанного контента
                    $media = [];
                    foreach ($post->media_files as $file) {
                        $type = $this->detectMediaType($file);
                        $media[] = [
                            'type' => $type,
                            'media' => $file,
                            'caption' => count($media) === 0 ? $post->content : null,
                        ];
                    }
                    $result = $this->sendMediaGroup($bot, $channel->channel_id, $media);
                    break;
            }

            if ($result) {
                $messageId = $result['result']['message_id'] ?? $result['result'][0]['message_id'] ?? null;
                
                $post->update([
                    'status' => 'published',
                    'telegram_message_id' => $messageId,
                    'published_at' => now(),
                ]);

                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error('Error publishing post: ' . $e->getMessage());
            
            $post->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Определить тип медиа по расширению файла
     */
    private function detectMediaType(string $file): string
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        $photoExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv'];

        if (in_array($extension, $photoExtensions)) {
            return 'photo';
        } elseif (in_array($extension, $videoExtensions)) {
            return 'video';
        }

        return 'document';
    }
}
