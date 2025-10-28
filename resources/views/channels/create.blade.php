@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-3">
        <div class="col-md-12">
            <h1>Добавить канал</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('channels.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="client_id" class="form-label">Клиент *</label>
                            <select class="form-select @error('client_id') is-invalid @enderror" 
                                    id="client_id" name="client_id" required>
                                <option value="">Выберите клиента</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }} ({{ $client->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bot_id" class="form-label">Бот *</label>
                            <select class="form-select @error('bot_id') is-invalid @enderror" 
                                    id="bot_id" name="bot_id" required>
                                <option value="">Выберите бота</option>
                                @foreach($bots as $bot)
                                    <option value="{{ $bot->id }}" {{ old('bot_id') == $bot->id ? 'selected' : '' }}>
                                        {{ $bot->name }} (@{{ $bot->username }})
                                    </option>
                                @endforeach
                            </select>
                            @error('bot_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Название канала *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="channel_id" class="form-label">Channel ID *</label>
                            <input type="text" class="form-control @error('channel_id') is-invalid @enderror" 
                                   id="channel_id" name="channel_id" value="{{ old('channel_id') }}" 
                                   placeholder="-1001234567890" required>
                            <small class="form-text text-muted">ID канала в Telegram (начинается с -100)</small>
                            @error('channel_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username канала</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                       id="username" name="username" value="{{ old('username') }}">
                            </div>
                            <small class="form-text text-muted">Username канала (если публичный, без @)</small>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Активен</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Создать</button>
                            <a href="{{ route('channels.index') }}" class="btn btn-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h6>Как получить Channel ID?</h6>
                </div>
                <div class="card-body">
                    <ol class="small">
                        <li>Добавьте бота в канал как администратора</li>
                        <li>Дайте боту права на публикацию</li>
                        <li>Используйте @userinfobot или @getidsbot</li>
                        <li>Перешлите любое сообщение из канала боту</li>
                        <li>Скопируйте Channel ID</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
