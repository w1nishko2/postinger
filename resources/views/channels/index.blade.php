@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Каналы</h1>
                <a href="{{ route('channels.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Добавить канал
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Имя</th>
                                <th>Channel ID</th>
                                <th>Username</th>
                                <th>Клиент</th>
                                <th>Бот</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($channels as $channel)
                                <tr>
                                    <td>{{ $channel->id }}</td>
                                    <td>{{ $channel->name }}</td>
                                    <td><code>{{ $channel->channel_id }}</code></td>
                                    <td>{{ $channel->username ? '@' . $channel->username : '-' }}</td>
                                    <td>{{ $channel->client->name }}</td>
                                    <td>@{{ $channel->bot->username }}</td>
                                    <td>
                                        <span class="badge {{ $channel->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $channel->is_active ? 'Активен' : 'Неактивен' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('channels.show', $channel) }}" class="btn btn-sm btn-info">Просмотр</a>
                                        <a href="{{ route('channels.edit', $channel) }}" class="btn btn-sm btn-warning">Изменить</a>
                                        <form action="{{ route('channels.destroy', $channel) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Нет каналов</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
