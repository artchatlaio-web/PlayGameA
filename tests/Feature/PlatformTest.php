<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Game;
use App\Models\Room;
use App\Models\RoomPlayer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_download_catalog_contains_twenty_games(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $games = Game::where('is_downloadable', true)->get();
        $this->assertCount(20, $games);
    }

    public function test_user_registration_and_verification_flow(): void
    {
        $regResponse = $this->post('/auth/register', [
            'name' => 'New Gamer',
            'email' => 'newgamer@test.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $regResponse->assertRedirect('/verify');

        $user = User::where('email', 'newgamer@test.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_active);
        $this->assertNotNull($user->verify_code);

        $verifyResponse = $this->withSession(['pending_user_id' => $user->id])
            ->post('/verify', ['code' => $user->verify_code]);

        $verifyResponse->assertRedirect('/');
        $user->refresh();
        $this->assertTrue($user->is_active);
    }

    public function test_user_login_and_logout(): void
    {
        $loginResponse = $this->post('/auth/login', [
            'email' => 'player@playgame.com',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/');
        $this->assertAuthenticated();

        $logoutResponse = $this->post('/auth/logout');
        $logoutResponse->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_playable_games_catalog_and_arena(): void
    {
        $response = $this->get('/games');
        $response->assertStatus(200);

        $gameResponse = $this->get('/games/cs-shooter');
        $gameResponse->assertStatus(200);
    }

    public function test_multiplayer_room_lifecycle(): void
    {
        $game = Game::where('slug', 'cs-shooter')->first();
        $user = User::where('email', 'player@playgame.com')->first();

        $createResponse = $this->actingAs($user)->post('/room/create', [
            'game_id' => $game->id,
            'player_name' => 'HostPlayer',
        ]);

        $room = Room::first();
        $this->assertNotNull($room);
        $createResponse->assertRedirect("/room/view/{$room->code}");

        $syncResponse = $this->postJson("/room/sync/{$room->code}", [
            'x' => 150,
            'y' => 200,
            'score' => 50,
            'player_id' => $room->players->first()->id,
        ]);

        $syncResponse->assertStatus(200);
        $syncResponse->assertJsonStructure(['room', 'players', 'current_player_id']);
    }

    public function test_admin_crud_authorization_and_actions(): void
    {
        $normalUser = User::where('email', 'player@playgame.com')->first();
        $adminUser = User::where('email', 'admin@playgame.com')->first();

        $this->actingAs($normalUser)->get('/admin/games')->assertStatus(403);

        $this->actingAs($adminUser)->get('/admin/games')->assertStatus(200);

        $createResponse = $this->actingAs($adminUser)->post('/admin/games', [
            'title' => 'Test Admin Game',
            'category' => 'Action',
            'description' => 'Test game description',
            'image' => '/covers/test.svg',
            'download_link' => 'https://example.com/download.zip',
            'is_downloadable' => 1,
        ]);

        $createResponse->assertRedirect('/admin/games');
        $this->assertDatabaseHas('games', ['title' => 'Test Admin Game']);
    }

    public function test_language_switch(): void
    {
        $this->get('/lang/ru')->assertSessionHas('locale', 'ru');
        $this->get('/lang/es')->assertSessionHas('locale', 'es');
        $this->get('/lang/en')->assertSessionHas('locale', 'en');
    }
}

