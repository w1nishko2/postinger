@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1>Тестирование бота: {{ $bot->name }}</h1>
            <a href="{{ route('bots.show', $bot) }}" class="btn btn-secondary">← Назад</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Информация о боте -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Информация о боте</h5>
                </div>
                <div class="card-body">
                    @if(isset($botInfo['ok']) && $botInfo['ok'])
                        <table class="table table-sm">
                            <tr>
                                <th>ID:</th>
                                <td>{{ $botInfo['result']['id'] }}</td>
                            </tr>
                            <tr>
                                <th>Username:</th>
                                <td>@{{ $botInfo['result']['username'] }}</td>
                            </tr>
                            <tr>
                                <th>Имя:</th>
                                <td>{{ $botInfo['result']['first_name'] }}</td>
                            </tr>
                            <tr>
                                <th>Статус:</th>
                                <td>
                                    <span class="badge bg-success">✓ Бот работает</span>
                                </td>
                            </tr>
                        </table>
                        
                        <div class="alert alert-success mb-0">
                            <strong>✓ Бот доступен!</strong> Токен правильный.
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <strong>✗ Ошибка подключения к боту!</strong><br>
                            {{ $botInfo['description'] ?? 'Проверьте токен бота' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Информация о Webhook -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Статус Webhook</h5>
                </div>
                <div class="card-body">
                    @if(isset($webhookInfo['ok']) && $webhookInfo['ok'])
                        @php
                            $webhook = $webhookInfo['result'];
                            $hasWebhook = !empty($webhook['url']);
                            $expectedUrl = route('telegram.webhook', ['bot' => $bot->id]);
                            $isCorrectUrl = $hasWebhook && $webhook['url'] === $expectedUrl;
                        @endphp

                        @if($hasWebhook)
                            <div class="mb-3">
                                <strong>URL:</strong><br>
                                <code class="small">{{ $webhook['url'] }}</code>
                            </div>

                            @if($isCorrectUrl)
                                <div class="alert alert-success mb-2">
                                    <strong>✓ Webhook настроен правильно!</strong>
                                </div>
                            @else
                                <div class="alert alert-warning mb-2">
                                    <strong>⚠ Webhook указывает на другой URL</strong><br>
                                    Ожидается: <code class="small">{{ $expectedUrl }}</code>
                                </div>
                            @endif

                            @if(isset($webhook['pending_update_count']) && $webhook['pending_update_count'] > 0)
                                <div class="alert alert-warning mb-2">
                                    Ожидает обработки: {{ $webhook['pending_update_count'] }} сообщений
                                </div>
                            @endif

                            @if(isset($webhook['last_error_date']))
                                <div class="alert alert-danger mb-2">
                                    <strong>Последняя ошибка:</strong><br>
                                    {{ $webhook['last_error_message'] ?? 'Unknown error' }}<br>
                                    <small>{{ date('d.m.Y H:i:s', $webhook['last_error_date']) }}</small>
                                </div>
                            @endif

                            <form action="{{ route('bots.delete-webhook', $bot) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Удалить webhook?')">
                                    Удалить Webhook
                                </button>
                            </form>
                        @else
                            <div class="alert alert-warning mb-3">
                                <strong>⚠ Webhook не установлен!</strong><br>
                                Бот не будет получать сообщения.
                            </div>

                            <form action="{{ route('bots.set-webhook', $bot) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    Установить Webhook
                                </button>
                            </form>

                            <div class="mt-3 p-3 bg-light rounded">
                                <strong>URL для webhook:</strong><br>
                                <code class="small">{{ $expectedUrl }}</code>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-danger">
                            Не удалось получить информацию о webhook
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Инструкции -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📋 Инструкция по настройке</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li class="mb-2">
                            <strong>Убедитесь, что бот работает</strong> (см. "Информация о боте")
                        </li>
                        <li class="mb-2">
                            <strong>Установите webhook</strong> (если не установлен)
                        </li>
                        <li class="mb-2">
                            <strong>Найдите бота в Telegram:</strong> 
                            <a href="https://t.me/{{ $bot->username }}" target="_blank" class="btn btn-sm btn-primary">
                                Открыть @{{ $bot->username }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <strong>Отправьте команду /start</strong>
                        </li>
                        <li class="mb-2">
                            <strong>Получите свой Telegram ID:</strong>
                            <ol type="a">
                                <li>Откройте <a href="https://t.me/userinfobot" target="_blank">@userinfobot</a></li>
                                <li>Отправьте ему любое сообщение</li>
                                <li>Скопируйте ваш ID</li>
                                <li>Укажите его в настройках клиента</li>
                            </ol>
                        </li>
                        <li class="mb-2">
                            <strong>Добавьте бота в канал:</strong>
                            <ol type="a">
                                <li>Откройте ваш канал в Telegram</li>
                                <li>Настройки → Администраторы → Добавить администратора</li>
                                <li>Найдите @{{ $bot->username }}</li>
                                <li>Дайте права на публикацию сообщений</li>
                            </ol>
                        </li>
                        <li class="mb-2">
                            <strong>Получите Channel ID:</strong>
                            <ol type="a">
                                <li>Перешлите любое сообщение из канала боту @userinfobot</li>
                                <li>Скопируйте Channel ID (например: -1001234567890)</li>
                                <li>Добавьте канал в системе с этим ID</li>
                            </ol>
                        </li>
                        <li class="mb-2">
                            <strong>Протестируйте:</strong> Отправьте боту сообщение, фото или видео
                        </li>
                    </ol>

                    <div class="alert alert-info mt-3">
                        <strong>💡 Важно для локальной разработки:</strong><br>
                        Telegram не может отправлять webhook на localhost. Используйте <a href="https://ngrok.com" target="_blank">ngrok</a>:
                        <pre class="mb-0 mt-2"><code>ngrok http 8000</code></pre>
                        Затем обновите APP_URL в .env на HTTPS URL от ngrok и переустановите webhook.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
