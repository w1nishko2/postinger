<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use App\Models\Client;
use Illuminate\Http\Request;

class BotController extends Controller
{
    /**
     * Показать список всех ботов
     */
    public function index()
    {
        $bots = Bot::with(['client', 'channels'])->latest()->get();
        return view('bots.index', compact('bots'));
    }

    /**
     * Показать форму создания нового бота
     */
    public function create()
    {
        $clients = Client::where('is_active', true)->get();
        return view('bots.create', compact('clients'));
    }

    /**
     * Сохранить нового бота
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:bots,username',
            'token' => 'required|string|unique:bots,token',
            'is_active' => 'boolean',
        ]);

        $bot = Bot::create($validated);

        // Установить webhook для бота
        $webhookUrl = route('telegram.webhook', ['bot' => $bot->id]);
        $bot->update(['webhook_url' => $webhookUrl]);

        return redirect()->route('bots.show', $bot)
            ->with('success', 'Бот успешно создан');
    }

    /**
     * Показать информацию о боте
     */
    public function show(Bot $bot)
    {
        $bot->load(['client', 'channels']);
        return view('bots.show', compact('bot'));
    }

    /**
     * Показать форму редактирования бота
     */
    public function edit(Bot $bot)
    {
        $clients = Client::where('is_active', true)->get();
        return view('bots.edit', compact('bot', 'clients'));
    }

    /**
     * Обновить информацию о боте
     */
    public function update(Request $request, Bot $bot)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:bots,username,' . $bot->id,
            'token' => 'required|string|unique:bots,token,' . $bot->id,
            'is_active' => 'boolean',
        ]);

        $bot->update($validated);

        return redirect()->route('bots.show', $bot)
            ->with('success', 'Бот успешно обновлён');
    }

    /**
     * Удалить бота
     */
    public function destroy(Bot $bot)
    {
        $bot->delete();

        return redirect()->route('bots.index')
            ->with('success', 'Бот успешно удалён');
    }
}
