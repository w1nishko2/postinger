<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Channel;
use App\Models\Client;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    /**
     * Показать список всех каналов
     */
    public function index()
    {
        $channels = Channel::with(['client', 'bot'])->latest()->get();
        return view('channels.index', compact('channels'));
    }

    /**
     * Показать форму создания нового канала
     */
    public function create()
    {
        $clients = Client::where('is_active', true)->get();
        $bots = Bot::where('is_active', true)->get();
        return view('channels.create', compact('clients', 'bots'));
    }

    /**
     * Сохранить новый канал
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'bot_id' => 'required|exists:bots,id',
            'name' => 'required|string|max:255',
            'channel_id' => 'required|string|unique:channels,channel_id',
            'username' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $channel = Channel::create($validated);

        return redirect()->route('channels.show', $channel)
            ->with('success', 'Канал успешно создан');
    }

    /**
     * Показать информацию о канале
     */
    public function show(Channel $channel)
    {
        $channel->load(['client', 'bot', 'posts' => function ($query) {
            $query->latest()->limit(20);
        }]);

        return view('channels.show', compact('channel'));
    }

    /**
     * Показать форму редактирования канала
     */
    public function edit(Channel $channel)
    {
        $clients = Client::where('is_active', true)->get();
        $bots = Bot::where('is_active', true)->get();
        return view('channels.edit', compact('channel', 'clients', 'bots'));
    }

    /**
     * Обновить информацию о канале
     */
    public function update(Request $request, Channel $channel)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'bot_id' => 'required|exists:bots,id',
            'name' => 'required|string|max:255',
            'channel_id' => 'required|string|unique:channels,channel_id,' . $channel->id,
            'username' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $channel->update($validated);

        return redirect()->route('channels.show', $channel)
            ->with('success', 'Канал успешно обновлён');
    }

    /**
     * Удалить канал
     */
    public function destroy(Channel $channel)
    {
        $channel->delete();

        return redirect()->route('channels.index')
            ->with('success', 'Канал успешно удалён');
    }
}
