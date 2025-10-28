@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>{{ $channel->name }}</h1>
                <div>
                    <a href="{{ route('channels.edit', $channel) }}" class="btn btn-warning">Изменить</a>
                    <a href="{{ route('channels.index') }}" class="btn btn-secondary">Назад</a>
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
                    <h5>Информация о канале</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Channel ID:</th>
                            <td><code>{{ $channel->channel_id }}</code></td>
                        </tr>
                        <tr>
                            <th>Username:</th>
                            <td>{{ $channel->username ? '@' . $channel->username : 'Не указан' }}</td>
                        </tr>
                        <tr>
                            <th>Клиент:</th>
                            <td>
                                <a href="{{ route('clients.show', $channel->client) }}">{{ $channel->client->name }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>Бот:</th>
                            <td>
                                <a href="{{ route('bots.show', $channel->bot) }}">@{{ $channel->bot->username }}</a>
                            </td>
                        </tr>
                        <tr>
                            <th>Статус:</th>
                            <td>
                                <span class="badge {{ $channel->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $channel->is_active ? 'Активен' : 'Неактивен' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Создан:</th>
                            <td>{{ $channel->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Статистика</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h3>{{ $channel->posts->count() }}</h3>
                            <p>Всего постов</p>
                        </div>
                        <div class="col-md-4">
                            <h3>{{ $channel->posts->where('status', 'published')->count() }}</h3>
                            <p>Опубликовано</p>
                        </div>
                        <div class="col-md-4">
                            <h3>{{ $channel->posts->where('status', 'failed')->count() }}</h3>
                            <p>Ошибок</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>История постов</h5>
                </div>
                <div class="card-body">
                    @if($channel->posts->count() > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Тип</th>
                                    <th>Содержание</th>
                                    <th>Статус</th>
                                    <th>Telegram ID</th>
                                    <th>Дата</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($channel->posts as $post)
                                    <tr>
                                        <td>{{ $post->id }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $post->media_type }}</span>
                                        </td>
                                        <td>{{ \Str::limit($post->content, 60) }}</td>
                                        <td>
                                            <span class="badge 
                                                {{ $post->status === 'published' ? 'bg-success' : '' }}
                                                {{ $post->status === 'pending' ? 'bg-warning' : '' }}
                                                {{ $post->status === 'failed' ? 'bg-danger' : '' }}
                                            ">
                                                {{ $post->status }}
                                            </span>
                                        </td>
                                        <td>{{ $post->telegram_message_id ?? '-' }}</td>
                                        <td>{{ $post->created_at->format('d.m.Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">В этом канале пока нет постов</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
