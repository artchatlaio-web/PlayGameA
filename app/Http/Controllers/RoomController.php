<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Room;
use App\Models\RoomPlayer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function handle(Request $request, string $action, ?string $code = null)
    {
        if ($action === 'create') {
            return $this->create($request);
        }
        if ($action === 'join') {
            return $this->join($request);
        }
        if ($action === 'sync') {
            return $this->sync($request, $code);
        }
        if ($action === 'leave') {
            return $this->leave($request, $code);
        }
        if ($action === 'view') {
            return $this->view($code);
        }
        abort(404);
    }

    public function create(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'player_name' => 'required|string|max:20',
        ]);

        $code = strtoupper(Str::random(6));
        $hostId = auth()->id() ?? 1;

        $room = Room::create([
            'code' => $code,
            'game_id' => $request->game_id,
            'host_id' => $hostId,
            'status' => 'waiting',
        ]);

        $player = RoomPlayer::create([
            'room_id' => $room->id,
            'user_id' => auth()->id(),
            'name' => $request->player_name,
            'team' => 'blue',
            'x' => 100,
            'y' => 100,
            'score' => 0,
            'last_seen' => now(),
        ]);

        session(["room_player_{$code}" => $player->id]);

        return redirect()->route('room', ['action' => 'view', 'code' => $code]);
    }

    public function join(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'player_name' => 'required|string|max:20',
        ]);

        $code = strtoupper($request->code);
        $room = Room::where('code', $code)->firstOrFail();

        $activeCount = RoomPlayer::where('room_id', $room->id)
            ->where('last_seen', '>=', now()->subSeconds(30))
            ->count();

        if ($activeCount >= 20) {
            return back()->withErrors(['code' => 'Room is full (max 20 players).']);
        }

        $team = $activeCount % 2 === 0 ? 'blue' : 'red';

        $player = RoomPlayer::create([
            'room_id' => $room->id,
            'user_id' => auth()->id(),
            'name' => $request->player_name,
            'team' => $team,
            'x' => 200 + ($activeCount * 20),
            'y' => 200,
            'score' => 0,
            'last_seen' => now(),
        ]);

        session(["room_player_{$code}" => $player->id]);

        return redirect()->route('room', ['action' => 'view', 'code' => $code]);
    }

    public function view(?string $code)
    {
        $room = Room::where('code', $code)->with(['game', 'players'])->firstOrFail();
        $playerId = session("room_player_{$code}");
        $currentPlayer = RoomPlayer::find($playerId);

        return view('rooms.view', compact('room', 'currentPlayer'));
    }

    public function sync(Request $request, ?string $code)
    {
        $room = Room::where('code', $code)->first();
        if (!$room) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $playerId = session("room_player_{$code}") ?? $request->player_id;
        $player = RoomPlayer::where('room_id', $room->id)->where('id', $playerId)->first();

        if ($player) {
            $player->update([
                'x' => $request->input('x', $player->x),
                'y' => $request->input('y', $player->y),
                'score' => $request->input('score', $player->score),
                'last_seen' => now(),
            ]);
        }

        $players = RoomPlayer::where('room_id', $room->id)
            ->where('last_seen', '>=', now()->subSeconds(15))
            ->get(['id', 'name', 'team', 'x', 'y', 'score', 'user_id']);

        return response()->json([
            'room' => $room->code,
            'players' => $players,
            'current_player_id' => $playerId,
        ]);
    }

    public function leave(Request $request, ?string $code)
    {
        $playerId = session("room_player_{$code}");
        if ($playerId) {
            RoomPlayer::where('id', $playerId)->delete();
            session()->forget("room_player_{$code}");
        }
        return redirect()->route('games.index');
    }
}

