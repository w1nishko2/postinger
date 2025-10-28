@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>{{ $bot->name }}</h1>
                <div>
                    <a href="{{ route('bots.test', $bot) }}" class="btn btn-info">🔧 Тестирование</a>
                    <a href="{{ route('bots.edit', $bot) }}" class="btn btn-warning">Изменить</a>
                    <a href="{{ route('bots.index') }}" class="btn btn-secondary">Назад</a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Информация о боте</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Username:</th>
                            <td>@{{ $bot->username }}</td>
                        </tr>
                        <tr>
                            <th>Клиент:</th>
                            <td>
                                <a href="{{ route('clients.show', $bot->client) }}">{{ $bot->client->name }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>Статус:</th>
                            <td>
                                <span class="badge {{ $bot->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $bot->is_active ? 'Активен' : 'Неактивен' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Webhook URL:</th>
                            <td>
                                <small class="font-monospace">{{ $bot->webhook_url }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Создан:</th>
                            <td>{{ $bot->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Инструкция для клиента</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li>Найдите бота <strong>@{{ $bot->username }}</strong> в Telegram</li>
                        <li>Нажмите кнопку "Start" или отправьте /start</li>
                        <li>Отправляйте боту текст, фото или видео</li>
                        <li>Бот автоматически опубликует контент в ваши каналы</li>
                    </ol>
                    <div class="alert alert-info mt-3">
                        <strong>Важно!</strong> Убедитесь, что у клиента указан правильный Telegram ID
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Каналы</h5>
                    <a href="{{ route('channels.create', ['bot_id' => $bot->id]) }}" class="btn btn-sm btn-primary">Добавить канал</a>
                </div>
                <div class="card-body">
                    @if($bot->channels->count() > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Имя</th>
                                    <th>Channel ID</th>
                                    <th>Username</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bot->channels as $channel)
                                    <tr>
                                        <td>{{ $channel->name }}</td>
                                        <td><code>{{ $channel->channel_id }}</code></td>
                                        <td>{{ $channel->username ? '@' . $channel->username : '-' }}</td>
                                        <td>
                                            <span class="badge {{ $channel->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $channel->is_active ? 'Активен' : 'Неактивен' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('channels.show', $channel) }}" class="btn btn-sm btn-info">Просмотр</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">У бота пока нет привязанных каналов</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
