@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Клиенты</h1>
                <a href="{{ route('clients.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Добавить клиента
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
                                <th>Email</th>
                                <th>Telegram ID</th>
                                <th>Боты</th>
                                <th>Каналы</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clients as $client)
                                <tr>
                                    <td>{{ $client->id }}</td>
                                    <td>{{ $client->name }}</td>
                                    <td>{{ $client->email }}</td>
                                    <td>{{ $client->telegram_user_id ?? 'Не указан' }}</td>
                                    <td>{{ $client->bots->count() }}</td>
                                    <td>{{ $client->channels->count() }}</td>
                                    <td>
                                        <span class="badge {{ $client->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $client->is_active ? 'Активен' : 'Неактивен' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-info">Просмотр</a>
                                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-warning">Изменить</a>
                                        <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline" onsubmit="return confirm('Вы уверены?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Нет клиентов</td>
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
