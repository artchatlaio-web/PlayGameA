@extends('layouts.app')

@section('content')
<div class="arena-container">
    <div class="arena-header">
        <div>
            <a href="{{ route('games.index') }}" style="font-size:0.85rem; color:#60a5fa;">&larr; {{ __('Back to Games') }}</a>
            <h1 style="font-size:1.7rem; font-weight:800; margin-top:0.2rem;">{{ $game->title }}</h1>
            <p style="font-size:0.9rem; color:#94a3b8;">{{ $game->description }}</p>
        </div>
        <div style="display:flex; gap:0.6rem; align-items:center;">
            <div class="hud-badge" style="color:#38bdf8; border-color:rgba(56,189,248,0.4);">⏱ 05:00 Match</div>
            <button onclick="toggleSetupModal()" class="btn btn-outline btn-sm">⚙ Настройки / Персонаж</button>
        </div>
    </div>

    <div id="setup-card" class="lobby-box">
        <h2 style="font-size:1.3rem; margin-bottom:1.2rem; color:#38bdf8;">🎮 Подготовка к матчу</h2>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
            <div>
                <label class="form-label">Ваше игровое имя</label>
                <input type="text" id="player-name-input" class="form-control" value="{{ auth()->user()->name ?? 'CyberGamer' }}" placeholder="Введите имя..." required>
            </div>

            <div>
                <label class="form-label">Персонаж (Класс)</label>
                <select id="hero-style" class="form-control" style="background:#0f172a;">
                    <option value="cyborg">Cyber Cyborg (Тяжелая броня)</option>
                    <option value="ninja">Neon Ninja (Высокая скорость)</option>
                    <option value="mech">Mech Trooper (Штурмовой костюм)</option>
                    <option value="assassin">Shadow Assassin (Скрытность)</option>
                </select>
            </div>

            <div>
                <label class="form-label">Цвет костюма и одежды</label>
                <div class="color-picker" id="color-picker-container">
                    <div class="color-dot active" data-color="#38bdf8" style="background:#38bdf8;"></div>
                    <div class="color-dot" data-color="#ec4899" style="background:#ec4899;"></div>
                    <div class="color-dot" data-color="#facc15" style="background:#facc15;"></div>
                    <div class="color-dot" data-color="#10b981" style="background:#10b981;"></div>
                    <div class="color-dot" data-color="#ef4444" style="background:#ef4444;"></div>
                    <div class="color-dot" data-color="#8b5cf6" style="background:#8b5cf6;"></div>
                </div>
            </div>

            <div>
                <label class="form-label">Настройки управления</label>
                <select id="control-style" class="form-control" style="background:#0f172a;">
                    <option value="wasd">WASD + Мышь (Стандарт)</option>
                    <option value="arrows">Стрелки клавиатуры</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom:1.5rem;">
            <label class="form-label">Режим игры</label>
            <div class="mode-tabs">
                <div class="mode-tab active" data-mode="solo" onclick="setMode('solo')">⚔ Одиночная (Solo vs AI)</div>
                <div class="mode-tab" data-mode="create" onclick="setMode('create')">🌐 Создать комнату</div>
                <div class="mode-tab" data-mode="join" onclick="setMode('join')">🔗 Войти в комнату</div>
            </div>

            <div id="join-room-box" style="display:none; max-width:320px; margin-top:0.8rem;">
                <input type="text" id="join-room-code" class="form-control" placeholder="Код комнаты (напр. 7K2A9X)" style="text-transform:uppercase;">
            </div>
        </div>

        <button onclick="launchGame()" class="btn btn-primary btn-block" style="padding:0.9rem; font-size:1.05rem;">
            🚀 НАЧАТЬ ИГРУ
        </button>
    </div>

    <div id="game-section" style="display:none;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem; flex-wrap:wrap; gap:0.5rem;">
            <div style="display:flex; gap:0.6rem;">
                <button onclick="toggleInGameMenu()" class="btn btn-outline btn-sm">⏸ Меню (ESC)</button>
                <button onclick="restartCurrentGame()" class="btn btn-outline btn-sm" style="color:#facc15; border-color:rgba(250,204,21,0.4);">🔄 Начать заново</button>
            </div>
            <div style="font-size:0.85rem; color:#94a3b8;">
                Нажмите <span style="background:rgba(255,255,255,0.12); padding:3px 7px; border-radius:4px; color:#fff; font-weight:700;">ESC</span> для меню паузы
            </div>
        </div>

        <div class="arena-canvas-wrap" style="position:relative;">
            <canvas id="game-canvas" class="arena-canvas"></canvas>

            <div id="in-game-menu-overlay" class="arena-overlay" style="display:none;">
                <div class="glass-card" style="max-width:360px; width:100%; text-align:center; padding:2rem;">
                    <h3 style="font-size:1.45rem; font-weight:800; color:#38bdf8; margin-bottom:1.4rem;">⏸ ИГРОВОЕ МЕНЮ</h3>
                    <div style="display:flex; flex-direction:column; gap:0.8rem;">
                        <button onclick="toggleInGameMenu()" class="btn btn-primary btn-block">▶ Продолжить игру</button>
                        <button onclick="restartCurrentGame()" class="btn btn-outline btn-block" style="color:#facc15; border-color:rgba(250,204,21,0.4);">🔄 Начать игру заново</button>
                        <button onclick="openCustomizationFromGame()" class="btn btn-outline btn-block">⚙ Настройки / Персонаж</button>
                        <a href="{{ route('games.index') }}" class="btn btn-block" style="background:#ef4444; color:#fff;">🚪 Выйти в каталог</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mobile-controls">
            <div></div>
            <button class="ctrl-btn" onpointerdown="window.dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowUp'}))" onpointerup="window.dispatchEvent(new KeyboardEvent('keyup', {key: 'ArrowUp'}))">&uarr;</button>
            <div></div>
            <button class="ctrl-btn" onpointerdown="window.dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowLeft'}))" onpointerup="window.dispatchEvent(new KeyboardEvent('keyup', {key: 'ArrowLeft'}))">&larr;</button>
            <button class="ctrl-btn" onpointerdown="window.dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowDown'}))" onpointerup="window.dispatchEvent(new KeyboardEvent('keyup', {key: 'ArrowDown'}))">&darr;</button>
            <button class="ctrl-btn" onpointerdown="window.dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowRight'}))" onpointerup="window.dispatchEvent(new KeyboardEvent('keyup', {key: 'ArrowRight'}))">&rarr;</button>
        </div>
    </div>

    <div class="glass-card" style="margin-top:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-size:1.15rem; color:#f8fafc;">🏆 Рейтинг игроков (Таблица лидеров)</h3>
            <span style="font-size:0.8rem; color:#94a3b8;">Топ 10 результатов</span>
        </div>
        @if($scores->isEmpty())
            <p style="color:#64748b; font-size:0.88rem;">Пока нет записей рейтинга. Сыграйте раунд, чтобы занять первое место!</p>
        @else
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Место</th>
                        <th>Игрок</th>
                        <th>Очки Рейтинга</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scores as $idx => $sc)
                        <tr>
                            <td style="font-weight:700; color:{{ $idx === 0 ? '#facc15' : ($idx === 1 ? '#cbd5e1' : ($idx === 2 ? '#b45309' : '#94a3b8')) }};">
                                №{{ $idx + 1 }}
                            </td>
                            <td>{{ $sc->user->name ?? 'Guest' }}</td>
                            <td style="font-weight:700; color:#38bdf8;">{{ $sc->score }}</td>
                            <td>{{ $sc->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<form id="create-room-form" action="{{ route('room', ['action' => 'create']) }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="game_id" value="{{ $game->id }}">
    <input type="hidden" name="player_name" id="form-create-name" value="Player">
</form>

<form id="join-room-form" action="{{ route('room', ['action' => 'join']) }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="code" id="form-join-code" value="">
    <input type="hidden" name="player_name" id="form-join-name" value="Player">
</form>
@endsection

@section('scripts')
<script>
let selectedColor = '#38bdf8';
let selectedMode = 'solo';
let isMenuOpen = false;

document.querySelectorAll('.color-dot').forEach(dot => {
    dot.addEventListener('click', () => {
        document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
        dot.classList.add('active');
        selectedColor = dot.getAttribute('data-color');
    });
});

function setMode(mode) {
    selectedMode = mode;
    document.querySelectorAll('.mode-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.mode-tab[data-mode="${mode}"]`)?.classList.add('active');

    const joinBox = document.getElementById('join-room-box');
    if (joinBox) joinBox.style.display = mode === 'join' ? 'block' : 'none';
}

function toggleSetupModal() {
    const setupCard = document.getElementById('setup-card');
    if (setupCard) {
        setupCard.style.display = setupCard.style.display === 'none' ? 'block' : 'none';
        if (setupCard.style.display === 'block') {
            setupCard.scrollIntoView({ behavior: 'smooth' });
        }
    }
}

function toggleInGameMenu() {
    const overlay = document.getElementById('in-game-menu-overlay');
    if (!overlay) return;
    isMenuOpen = !isMenuOpen;
    overlay.style.display = isMenuOpen ? 'flex' : 'none';
}

function restartCurrentGame() {
    const overlay = document.getElementById('in-game-menu-overlay');
    if (overlay) {
        overlay.style.display = 'none';
        isMenuOpen = false;
    }
    PlayGameEngine.restart();
}

function openCustomizationFromGame() {
    toggleInGameMenu();
    document.getElementById('setup-card').style.display = 'block';
    document.getElementById('setup-card').scrollIntoView({ behavior: 'smooth' });
}

window.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        const gameSec = document.getElementById('game-section');
        if (gameSec && gameSec.style.display !== 'none') {
            toggleInGameMenu();
        }
    }
});

function launchGame() {
    const name = document.getElementById('player-name-input')?.value.trim() || 'CyberWarrior';
    const hero = document.getElementById('hero-style')?.value || 'cyborg';
    const controls = document.getElementById('control-style')?.value || 'wasd';

    if (selectedMode === 'create') {
        document.getElementById('form-create-name').value = name;
        document.getElementById('create-room-form').submit();
        return;
    }

    if (selectedMode === 'join') {
        const code = document.getElementById('join-room-code')?.value.trim().toUpperCase();
        if (!code) {
            alert('Пожалуйста, введите код комнаты!');
            return;
        }
        document.getElementById('form-join-code').value = code;
        document.getElementById('form-join-name').value = name;
        document.getElementById('join-room-form').submit();
        return;
    }

    document.getElementById('setup-card').style.display = 'none';
    document.getElementById('game-section').style.display = 'block';

    PlayGameEngine.customSettings = {
        name: name,
        hero: hero,
        outfitColor: selectedColor,
        controls: controls
    };

    PlayGameEngine.init('{{ $game->slug }}', 'game-canvas', {{ $game->id }});
    document.getElementById('game-section').scrollIntoView({ behavior: 'smooth' });
}
</script>
@endsection
