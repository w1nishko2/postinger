@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Панель управления Postinger</h1>
            <p class="text-muted">Добро пожаловать в систему управления постингом в Telegram</p>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Клиенты</h5>
                    <h2 class="text-primary">{{ $stats['clients'] }}</h2>
                    <p class="text-muted mb-0">Активных: {{ $stats['active_clients'] }}</p>
                    <a href="{{ route('clients.index') }}" class="btn btn-sm btn-outline-primary mt-2">Управление</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Боты</h5>
                    <h2 class="text-success">{{ $stats['bots'] }}</h2>
                    <p class="text-muted mb-0">Активных: {{ $stats['active_bots'] }}</p>
                    <a href="{{ route('bots.index') }}" class="btn btn-sm btn-outline-success mt-2">Управление</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Каналы</h5>
                    <h2 class="text-info">{{ $stats['channels'] }}</h2>
                    <p class="text-muted mb-0">Активных: {{ $stats['active_channels'] }}</p>
                    <a href="{{ route('channels.index') }}" class="btn btn-sm btn-outline-info mt-2">Управление</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Посты</h5>
                    <h2 class="text-warning">{{ $stats['posts'] }}</h2>
                    <p class="text-muted mb-0">
                        <span class="text-success">✓ {{ $stats['published_posts'] }}</span> / 
                        <span class="text-danger">✗ {{ $stats['failed_posts'] }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Последние посты -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Последние посты</h5>
                </div>
                <div class="card-body">
                    @if($recent_posts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Клиент</th>
                                        <th>Канал</th>
                                        <th>Тип</th>
                                        <th>Содержание</th>
                                        <th>Статус</th>
                                        <th>Дата</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_posts as $post)
                                        <tr>
                                            <td>{{ $post->id }}</td>
                                            <td>
                                                <a href="{{ route('clients.show', $post->client) }}">
                                                    {{ $post->client->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ route('channels.show', $post->channel) }}">
                                                    {{ $post->channel->name }}
                                                </a>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $post->media_type }}</span></td>
                                            <td>{{ \Str::limit($post->content, 40) }}</td>
                                            <td>
                                                <span class="badge 
                                                    {{ $post->status === 'published' ? 'bg-success' : '' }}
                                                    {{ $post->status === 'pending' ? 'bg-warning' : '' }}
                                                    {{ $post->status === 'failed' ? 'bg-danger' : '' }}
                                                ">
                                                    {{ $post->status }}
                                                </span>
                                            </td>
                                            <td>{{ $post->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted">Пока нет ни одного поста</p>
                            <a href="{{ route('clients.create') }}" class="btn btn-primary">Создать первого клиента</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Быстрые действия -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Быстрые действия</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('clients.create') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-person-plus"></i> Добавить клиента
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('bots.create') }}" class="btn btn-outline-success">
                                    <i class="bi bi-robot"></i> Добавить бота
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('channels.create') }}" class="btn btn-outline-info">
                                    <i class="bi bi-broadcast"></i> Добавить канал
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
