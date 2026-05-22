<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\YoutubeAuthController;
use App\Http\Controllers\YoutubeShortsController;
use Illuminate\Support\Facades\Route;

// Troca de idioma da interface (salva na sessão)
Route::post('/locale', function (\Illuminate\Http\Request $request) {
    $locale = $request->input('locale');
    if (in_array($locale, ['pt-BR', 'en'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('locale.switch');

Route::get('/', [VideoController::class, 'index'])->name('videos.index');
Route::get('/novo', [VideoController::class, 'create'])->name('videos.create');
Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
Route::get('/videos/{video}/status', [VideoController::class, 'status'])->name('videos.status');
Route::get('/videos/{video}/poll', [VideoController::class, 'poll'])->name('videos.poll');
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
Route::get('/videos/{video}/download', [VideoController::class, 'download'])->name('videos.download');
Route::get('/videos/{video}/legenda', [VideoController::class, 'downloadSrt'])->name('videos.download-srt');

Route::get('/dashboard', [VideoController::class, 'dashboard'])->name('dashboard');
Route::view('/pipeline', 'pages.pipeline')->name('pipeline');
Route::view('/config', 'pages.config')->name('config');
Route::view('/sobre', 'pages.sobre')->name('sobre');

Route::get('/health', [HealthController::class, 'index'])->name('health.index');
Route::get('/health/api', [HealthController::class, 'api'])->name('health.api');

// YouTube Shorts — agendamento e publicação direta
Route::prefix('shorts')->name('shorts.')->group(function () {
    Route::get('/',                 [YoutubeShortsController::class, 'index'])->name('index');
    Route::get('/novo',             [YoutubeShortsController::class, 'create'])->name('create');
    Route::post('/',                [YoutubeShortsController::class, 'store'])->name('store');
    Route::get('/{short}',          [YoutubeShortsController::class, 'show'])->name('show');
    Route::get('/{short}/poll',     [YoutubeShortsController::class, 'poll'])->name('poll');
    Route::delete('/{short}',       [YoutubeShortsController::class, 'destroy'])->name('destroy');

    Route::get('/youtube/connect',  [YoutubeAuthController::class, 'redirect'])->name('connect');
    Route::get('/youtube/callback', [YoutubeAuthController::class, 'callback'])->name('callback');
    Route::delete('/youtube/{account}', [YoutubeAuthController::class, 'disconnect'])->name('disconnect');
});
