@extends('layouts.app')

@section('content')
<div class="arena-container">
    <div class="arena-header">
        <div>
            <h1 style="font-size:1.6rem; font-weight:800;">Room: <span style="color:#38bdf8;" id="room-code-display">{{ $room->code }}</span></h1>
            <p style="font-size:0.9rem; color:#94a3b8;">Game: {{ $room->game->title }}</p>
        </div>
        <div style="display:flex; gap:0.6rem; align-items:center;">
            <button onclick="copyRoomCode('{{ $room->code }}')" class="btn btn-outline btn-sm">Copy Code</button>
            <form action="{{ route('room', ['action' => 'leave', 'code' => $room->code]) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-sm" style="background:#ef4444; color:#fff;">{{ __('Leave Room') }}</button>
            </form>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 3fr 1fr; gap:1rem;">
        <div class="arena-canvas-wrap">
            <canvas id="multiplayer-canvas" class="arena-canvas"></canvas>
        </div>

        <div class="glass-card" style="padding:1rem;">
            <h4 style="font-size:0.95rem; margin-bottom:0.6rem; color:#93c5fd;">{{ __('Players Connected') }} (<span id="player-count">1</span>/20)</h4>
            <div id="player-list" style="display:flex; flex-direction:column; gap:0.4rem; font-size:0.85rem;">
                @foreach($room->players as $p)
                    <div style="padding:4px 8px; border-radius:6px; background:rgba(255,255,255,0.05); display:flex; justify-content:space-between;">
                        <span style="color:{{ $p->team === 'red' ? '#f87171' : '#60a5fa' }}; font-weight:600;">{{ $p->name }}</span>
                        <span style="color:#94a3b8;">Score: {{ $p->score }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
window.addEventListener('DOMContentLoaded', () => {
    const cvs = document.getElementById('multiplayer-canvas');
    const ctx = cvs.getContext('2d');
    cvs.width = 800;
    cvs.height = 500;

    const roomCode = '{{ $room->code }}';
    const currentPlayerId = {{ $currentPlayer->id ?? 0 }};
    let localPlayer = {
        id: currentPlayerId,
        x: {{ $currentPlayer->x ?? 100 }},
        y: {{ $currentPlayer->y ?? 100 }},
        score: {{ $currentPlayer->score ?? 0 }},
        name: '{{ $currentPlayer->name ?? "Player" }}',
        team: '{{ $currentPlayer->team ?? "blue" }}'
    };

    let remotePlayers = [];
    let keys = {};
    let ws = null;
    let wsConnected = false;

    window.addEventListener('keydown', e => keys[e.key.toLowerCase()] = true);
    window.addEventListener('keyup', e => keys[e.key.toLowerCase()] = false);

    try {
        ws = new WebSocket('ws://' + window.location.hostname + ':8080');
        ws.onopen = () => {
            wsConnected = true;
        };
        ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                if (data.room === roomCode && data.players) {
                    remotePlayers = data.players;
                    updatePlayerList(remotePlayers);
                }
            } catch (err) {}
        };
        ws.onclose = () => {
            wsConnected = false;
        };
    } catch (err) {
        wsConnected = false;
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function syncHeartbeat() {
        if (keys['w'] || keys['arrowup']) localPlayer.y = Math.max(20, localPlayer.y - 4);
        if (keys['s'] || keys['arrowdown']) localPlayer.y = Math.min(cvs.height - 20, localPlayer.y + 4);
        if (keys['a'] || keys['arrowleft']) localPlayer.x = Math.max(20, localPlayer.x - 4);
        if (keys['d'] || keys['arrowright']) localPlayer.x = Math.min(cvs.width - 20, localPlayer.x + 4);

        if (wsConnected && ws && ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({
                room: roomCode,
                id: localPlayer.id,
                name: localPlayer.name,
                team: localPlayer.team,
                x: localPlayer.x,
                y: localPlayer.y,
                score: localPlayer.score
            }));
        } else {
            fetch(`/room/sync/${roomCode}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    x: localPlayer.x,
                    y: localPlayer.y,
                    score: localPlayer.score,
                    player_id: currentPlayerId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.players) {
                    remotePlayers = data.players;
                    updatePlayerList(remotePlayers);
                }
            })
            .catch(() => {});
        }
    }

    setInterval(syncHeartbeat, 120);

    function updatePlayerList(players) {
        const countEl = document.getElementById('player-count');
        const listEl = document.getElementById('player-list');
        if (countEl) countEl.textContent = players.length;
        if (listEl) {
            listEl.innerHTML = players.map(p => `
                <div style="padding:4px 8px; border-radius:6px; background:rgba(255,255,255,0.05); display:flex; justify-content:space-between;">
                    <span style="color:${p.team === 'red' ? '#f87171' : '#60a5fa'}; font-weight:600;">${p.name} ${p.id === currentPlayerId ? '(You)' : ''}</span>
                    <span style="color:#94a3b8;">${p.score}</span>
                </div>
            `).join('');
        }
    }

    function render() {
        ctx.fillStyle = '#070b14';
        ctx.fillRect(0, 0, cvs.width, cvs.height);

        ctx.strokeStyle = 'rgba(255,255,255,0.05)';
        ctx.lineWidth = 1;
        for (let x = 0; x < cvs.width; x += 40) {
            ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, cvs.height); ctx.stroke();
        }
        for (let y = 0; y < cvs.height; y += 40) {
            ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(cvs.width, y); ctx.stroke();
        }

        remotePlayers.forEach(p => {
            ctx.fillStyle = p.team === 'red' ? '#ef4444' : '#3b82f6';
            ctx.beginPath();
            ctx.arc(p.x, p.y, 16, 0, Math.PI * 2);
            ctx.fill();

            ctx.fillStyle = '#fff';
            ctx.font = '12px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(p.name, p.x, p.y - 22);
        });

        requestAnimationFrame(render);
    }
    render();
});
</script>
@endsection
