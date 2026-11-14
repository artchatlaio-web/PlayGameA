<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@playgame.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $player = User::firstOrCreate(
            ['email' => 'player@playgame.com'],
            [
                'name' => 'Player One',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $downloadGames = config('download_games', []);
        foreach ($downloadGames as $item) {
            Game::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'category' => $item['category'],
                    'description' => $item['description'],
                    'image' => $item['image'],
                    'download_link' => $item['download_link'],
                    'is_downloadable' => true,
                ]
            );
        }

        $playableGames = [
            [
                'title' => 'Counter Strike 3D: Arena',
                'slug' => 'cs-shooter',
                'category' => 'Shooter',
                'description' => 'Tactical 3D first-person shooter with raycast perspective, weapons, arena radar map and 5-minute timer.',
                'image' => '/covers/apex-arena.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Cyber Football 3D Pro',
                'slug' => 'football',
                'category' => 'Football',
                'description' => '3D multiplayer football match with pitch radar map, ball height bounce physics, and 5-minute timer.',
                'image' => '/covers/cyber-soccer.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Worms 3D Battle Arena',
                'slug' => 'worms',
                'category' => 'Worms',
                'description' => '3D glowing worm survival with depth perspective, arena radar map, food dots, and 5-minute timer.',
                'image' => '/covers/dragon-soul.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Neon Cyber 3D Racing',
                'slug' => 'racing',
                'category' => 'Racing',
                'description' => '3D perspective high-speed highway racing with road depth, curves, rival cars, track map and 5-minute timer.',
                'image' => '/covers/cyber-drift.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Math Battle Blitz',
                'slug' => 'math-battle',
                'category' => 'Math Battle',
                'description' => 'High speed arithmetic showdown. Rapid calculation with rating leaderboard and 5-minute timer.',
                'image' => '/covers/quantum-strike.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Retro Snake',
                'slug' => 'snake',
                'category' => 'Mini Games',
                'description' => 'Classic snake arcade with responsive grid movements, 5-minute timer, and rating leaderboard.',
                'image' => '/covers/flappy-droid.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Neon Ping Pong',
                'slug' => 'ping-pong',
                'category' => 'Mini Games',
                'description' => 'Paddle tennis with progressive velocity, curved spins, 5-minute timer, and rating scores.',
                'image' => '/covers/neon-pong.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Cyber Memory Cards',
                'slug' => 'memory',
                'category' => 'Mini Games',
                'description' => 'Memory puzzle matching holographic glyphs, with rating scores and 5-minute timer.',
                'image' => '/covers/starlight-dominion.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Pixel Platformer Dash',
                'slug' => 'platformer',
                'category' => 'Mini Games',
                'description' => '2D platformer with double jumps, moving platforms, star collectibles, and 5-minute timer.',
                'image' => '/covers/pixel-dash.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Reaction Speed Test',
                'slug' => 'reaction-speed',
                'category' => 'Mini Games',
                'description' => 'Millisecond reflex testing with color changes, rating leaderboard, and 5-minute timer.',
                'image' => '/covers/hyperloop-drift.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Cyber 15 Puzzle',
                'slug' => 'puzzle',
                'category' => 'Mini Games',
                'description' => 'Holographic sliding tile puzzle with step rating and 5-minute timer.',
                'image' => '/covers/shadows-sector.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
            [
                'title' => 'Neon Tic-Tac-Toe',
                'slug' => 'tic-tac-toe',
                'category' => 'Mini Games',
                'description' => 'Glow grid strategy game with AI opponent, streak scoring, and 5-minute timer.',
                'image' => '/covers/aether-knight.svg',
                'download_link' => null,
                'is_downloadable' => false,
            ],
        ];

        foreach ($playableGames as $item) {
            $g = Game::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );

            GameScore::firstOrCreate(
                ['user_id' => $admin->id, 'game_id' => $g->id],
                ['score' => rand(350, 950)]
            );
            GameScore::firstOrCreate(
                ['user_id' => $player->id, 'game_id' => $g->id],
                ['score' => rand(150, 600)]
            );
        }
    }
}
