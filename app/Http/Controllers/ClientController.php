<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Показать список всех клиентов
     */
    public function index()
    {
        $clients = Client::with(['bots', 'channels'])->latest()->get();
        return view('clients.index', compact('clients'));
    }

    /**
     * Показать форму создания нового клиента
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Сохранить нового клиента
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'telegram_user_id' => 'nullable|string|unique:clients,telegram_user_id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $client = Client::create($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Клиент успешно создан');
    }

    /**
     * Показать информацию о клиенте
     */
    public function show(Client $client)
    {
        $client->load(['bots.channels', 'posts' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('clients.show', compact('client'));
    }

    /**
     * Показать форму редактирования клиента
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Обновить информацию о клиенте
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->id,
            'telegram_user_id' => 'nullable|string|unique:clients,telegram_user_id,' . $client->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Клиент успешно обновлён');
    }

    /**
     * Удалить клиента
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Клиент успешно удалён');
    }
}
