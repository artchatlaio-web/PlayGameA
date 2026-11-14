<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function index()
    {
        $games = Game::where('is_downloadable', true)->get();
        if ($games->isEmpty()) {
            $games = collect(config('download_games', []))->map(fn($item) => (object) $item);
        }
        return view('download.index', compact('games'));
    }

    public function download($id)
    {
        $game = Game::where('is_downloadable', true)->find($id);
        if (!$game) {
            $configItem = collect(config('download_games', []))->firstWhere('id', (int)$id);
            if ($configItem) {
                $game = (object) $configItem;
            }
        }

        if ($game && isset($game->download_link) && filter_var($game->download_link, FILTER_VALIDATE_URL)) {
            return redirect()->away($game->download_link);
        }
        return back()->with('info', "Starting download for " . ($game->title ?? 'Game'));
    }
}
