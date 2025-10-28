@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>{{ $client->name }}</h1>
                <div>
                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning">Изменить</a>
                    <a href="{{ route('clients.index') }}" class="btn btn-secondary">Назад</a>
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
                    <h5>Информация о клиенте</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Email:</th>
                            <td>{{ $client->email }}</td>
                        </tr>
                        <tr>
                            <th>Telegram ID:</th>
                            <td>{{ $client->telegram_user_id ?? 'Не указан' }}</td>
                        </tr>
                        <tr>
                            <th>Статус:</th>
                            <td>
                                <span class="badge {{ $client->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $client->is_active ? 'Активен' : 'Неактивен' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Описание:</th>
                            <td>{{ $client->description ?? 'Нет описания' }}</td>
                        </tr>
                        <tr>
                            <th>Создан:</th>
                            <td>{{ $client->created_at->format('d.m.Y H:i') }}</td>
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
                            <h3>{{ $client->bots->count() }}</h3>
                            <p>Ботов</p>
                        </div>
                        <div class="col-md-4">
                            <h3>{{ $client->channels->count() }}</h3>
                            <p>Каналов</p>
                        </div>
                        <div class="col-md-4">
                            <h3>{{ $client->posts->count() }}</h3>
                            <p>Постов</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Боты</h5>
                    <a href="{{ route('bots.create', ['client_id' => $client->id]) }}" class="btn btn-sm btn-primary">Добавить бота</a>
                </div>
                <div class="card-body">
                    @if($client->bots->count() > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Имя</th>
                                    <th>Username</th>
                                    <th>Каналы</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client->bots as $bot)
                                    <tr>
                                        <td>{{ $bot->name }}</td>
                                        <td>@{{ $bot->username }}</td>
                                        <td>{{ $bot->channels->count() }}</td>
                                        <td>
                                            <span class="badge {{ $bot->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $bot->is_active ? 'Активен' : 'Неактивен' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('bots.show', $bot) }}" class="btn btn-sm btn-info">Просмотр</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">У клиента пока нет ботов</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Последние посты</h5>
                </div>
                <div class="card-body">
                    @if($client->posts->count() > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Канал</th>
                                    <th>Тип</th>
                                    <th>Содержание</th>
                                    <th>Статус</th>
                                    <th>Дата</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client->posts as $post)
                                    <tr>
                                        <td>{{ $post->id }}</td>
                                        <td>{{ $post->channel->name }}</td>
                                        <td>{{ $post->media_type }}</td>
                                        <td>{{ \Str::limit($post->content, 50) }}</td>
                                        <td>
                                            <span class="badge 
                                                {{ $post->status === 'published' ? 'bg-success' : '' }}
                                                {{ $post->status === 'pending' ? 'bg-warning' : '' }}
                                                {{ $post->status === 'failed' ? 'bg-danger' : '' }}
                                            ">
                                                {{ $post->status }}
                                            </span>
                                        </td>
                                        <td>{{ $post->created_at->format('d.m.Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">У клиента пока нет постов</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
