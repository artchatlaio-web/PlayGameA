<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::where('is_downloadable', false)->get();
        $categories = $games->groupBy('category');
        return view('games.index', compact('games', 'categories'));
    }

    public function show($slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        $scores = GameScore::where('game_id', $game->id)->with('user')->orderByDesc('score')->take(10)->get();
        return view('games.show', compact('game', 'scores'));
    }

    public function score(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'score' => 'required|integer',
        ]);

        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $record = GameScore::create([
            'user_id' => auth()->id(),
            'game_id' => $request->game_id,
            'score' => $request->score,
        ]);

        return response()->json(['success' => true, 'score' => $record->score]);
    }

    public function lang($locale)
    {
        if (in_array($locale, ['ru', 'en', 'es'])) {
            session(['locale' => $locale]);
        }
        return back();
    }
}

