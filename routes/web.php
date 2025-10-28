<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\TelegramWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('clients.index');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Админ панель - управление клиентами, ботами и каналами
Route::middleware(['auth'])->group(function () {
    Route::resource('clients', ClientController::class);
    Route::resource('bots', BotController::class);
    Route::resource('channels', ChannelController::class);
    
    // Тестирование ботов
    Route::get('bots/{bot}/test', [App\Http\Controllers\BotTestController::class, 'test'])->name('bots.test');
    Route::post('bots/{bot}/set-webhook', [App\Http\Controllers\BotTestController::class, 'setWebhook'])->name('bots.set-webhook');
    Route::post('bots/{bot}/delete-webhook', [App\Http\Controllers\BotTestController::class, 'deleteWebhook'])->name('bots.delete-webhook');
});

// Webhook для Telegram (без авторизации)
Route::post('/telegram/webhook/{bot}', [TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook');
