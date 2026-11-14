@extends('layouts.app')

@section('content')
<div class="hero">
    <h1 class="hero-title">{{ __('Online Games') }}</h1>
    <p class="hero-subtitle">Play instantly in your browser or create a room for local Wi-Fi multiplayer with up to 20 friends.</p>
</div>

<div class="glass-card" style="margin-bottom: 2.5rem; max-width: 900px; margin-left: auto; margin-right: auto;">
    <h2 style="font-size: 1.25rem; margin-bottom: 1rem; color: #60a5fa;">{{ __('Multiplayer Lobby') }}</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <form action="{{ route('room', ['action' => 'create']) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('Player Name') }}</label>
                <input type="text" name="player_name" class="form-control" value="{{ auth()->user()->name ?? 'Player' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Game</label>
                <select name="game_id" class="form-control" style="background:#0f172a;">
                    @foreach($games as $g)
                        <option value="{{ $g->id }}">{{ $g->title }} ({{ $g->category }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">{{ __('Create Room') }}</button>
        </form>

        <form action="{{ route('room', ['action' => 'join']) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">{{ __('Player Name') }}</label>
                <input type="text" name="player_name" class="form-control" value="{{ auth()->user()->name ?? 'Guest' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('Room Code') }}</label>
                <input type="text" name="code" class="form-control" placeholder="e.g. 7K2A9X" required style="text-transform:uppercase;">
            </div>
            <button type="submit" class="btn btn-outline btn-block" style="margin-top:1.85rem;">{{ __('Join Room') }}</button>
        </form>
    </div>
</div>

@foreach($categories as $categoryName => $categoryGames)
    <div class="category-header">
        <span>{{ $categoryName }}</span>
        <span style="font-size:0.85rem; color:#94a3b8; font-weight:normal;">{{ count($categoryGames) }} games</span>
    </div>
    <div class="games-grid">
        @foreach($categoryGames as $game)
            <div class="glass-card">
                <img src="{{ $game->image }}" alt="{{ $game->title }}" class="card-cover">
                <span class="card-badge">{{ $game->category }}</span>
                <h3 class="card-title">{{ $game->title }}</h3>
                <p class="card-desc">{{ $game->description }}</p>
                <div class="card-actions">
                    <a href="{{ route('games.show', $game->slug) }}" class="btn btn-primary btn-block">{{ __('Play Now') }}</a>
                </div>
            </div>
        @endforeach
    </div>
@endforeach
@endsection

