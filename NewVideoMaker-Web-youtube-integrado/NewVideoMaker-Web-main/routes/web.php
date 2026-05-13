<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\YoutubePostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideoController::class, 'index'])->name('videos.index');
Route::get('/novo', [VideoController::class, 'create'])->name('videos.create');
Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
Route::get('/videos/{video}/status', [VideoController::class, 'status'])->name('videos.status');
Route::get('/videos/{video}/poll', [VideoController::class, 'poll'])->name('videos.poll');
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
Route::get('/videos/{video}/download', [VideoController::class, 'download'])->name('videos.download');
Route::get('/videos/{video}/legenda', [VideoController::class, 'downloadSrt'])->name('videos.download-srt');


Route::get('/youtube', [YoutubePostController::class, 'index'])->name('youtube.index');
Route::get('/youtube/connect', [YoutubePostController::class, 'connect'])->name('youtube.connect');
Route::get('/youtube/callback', [YoutubePostController::class, 'callback'])->name('youtube.callback');
Route::get('/videos/{video}/youtube/agendar', [YoutubePostController::class, 'create'])->name('youtube.create');
Route::post('/videos/{video}/youtube', [YoutubePostController::class, 'store'])->name('youtube.store');
Route::post('/youtube/{post}/publicar-agora', [YoutubePostController::class, 'publishNow'])->name('youtube.publish-now');

Route::get('/dashboard', [VideoController::class, 'dashboard'])->name('dashboard');
Route::view('/pipeline', 'pages.pipeline')->name('pipeline');
Route::view('/config', 'pages.config')->name('config');
Route::view('/sobre', 'pages.sobre')->name('sobre');

Route::get('/health', [HealthController::class, 'index'])->name('health.index');
Route::get('/health/api', [HealthController::class, 'api'])->name('health.api');
