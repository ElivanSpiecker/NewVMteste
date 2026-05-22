<?php

use App\Http\Controllers\Api\ApiHealthController;
use App\Http\Controllers\Api\ApiVideoController;
use App\Http\Controllers\VoiceController;
use Illuminate\Support\Facades\Route;

/*
| API REST consumida por front-ends externos (Lovable, mobile, Postman).
| Prefixo /api é aplicado automaticamente em bootstrap/app.php.
*/

Route::get('/health',                [ApiHealthController::class, 'index']);

Route::get('/videos',                [ApiVideoController::class, 'index']);
Route::post('/videos',               [ApiVideoController::class, 'store']);
Route::get('/videos/{video}',        [ApiVideoController::class, 'show']);
Route::delete('/videos/{video}',     [ApiVideoController::class, 'destroy']);
Route::get('/videos/{video}/download',  [ApiVideoController::class, 'download']);
Route::get('/videos/{video}/subtitles', [ApiVideoController::class, 'subtitles']);

Route::get('/voices',                    [VoiceController::class, 'index']);
Route::get('/voices/{voiceId}/preview',  [VoiceController::class, 'preview']);
