<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\AdminGameController;

Route::get('/', [DownloadController::class, 'index'])->name('download.index');
Route::get('/download/{id}', [DownloadController::class, 'download'])->name('download.file');
Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::get('/games/{slug}', [GameController::class, 'show'])->name('games.show');
Route::match(['get', 'post'], '/auth/{action}', [AuthController::class, 'handle'])->name('auth');
Route::match(['get', 'post'], '/verify', [AuthController::class, 'verify'])->name('verify');
Route::post('/score', [GameController::class, 'score'])->name('score.save');
Route::match(['get', 'post'], '/room/{action}/{code?}', [RoomController::class, 'handle'])->name('room');
Route::match(['get', 'post', 'put', 'delete'], '/admin/games/{action?}/{id?}', [AdminGameController::class, 'handle'])->name('admin.games');
Route::get('/lang/{locale}', [GameController::class, 'lang'])->name('lang.switch');
