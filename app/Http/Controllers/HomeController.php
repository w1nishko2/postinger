<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Bot;
use App\Models\Channel;
use App\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $stats = [
            'clients' => Client::count(),
            'active_clients' => Client::where('is_active', true)->count(),
            'bots' => Bot::count(),
            'active_bots' => Bot::where('is_active', true)->count(),
            'channels' => Channel::count(),
            'active_channels' => Channel::where('is_active', true)->count(),
            'posts' => Post::count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'failed_posts' => Post::where('status', 'failed')->count(),
        ];

        $recent_posts = Post::with(['client', 'channel'])
            ->latest()
            ->limit(10)
            ->get();

        return view('home', compact('stats', 'recent_posts'));
    }
}
