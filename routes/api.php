<?php

use App\Http\Controllers\Api\v1\ApiFooController;
use App\Http\Controllers\Api\v1\ApiPollController;
use App\Http\Controllers\Api\v1\ApiPollOptionController;
use App\Http\Controllers\Api\v1\ApiPollVoteController;
use App\Http\Controllers\Api\v1\ApiPostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// === Posts (existant — bearer tokens avec abilities) ===
Route::apiResource('v1/posts', ApiPostController::class)
    ->middlewareFor(['index', 'show'], ['auth:sanctum', 'abilities:posts:read'])
    ->middlewareFor(['store'], ['auth:sanctum', 'abilities:posts:create'])
    ->middlewareFor(['update'], ['auth:sanctum', 'abilities:posts:update'])
    ->middlewareFor(['destroy'], ['auth:sanctum', 'abilities:posts:delete']);

// === Foo (exemples fournis du squelette) ===
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/v1/foo', [ApiFooController::class, 'show']);
    Route::post('/v1/foo', [ApiFooController::class, 'store']);
});

// === Polls — vue publique par token (anonyme autorisé) ===
Route::prefix('v1/polls/by-token/{token}')->group(function () {
    Route::get('/', [ApiPollController::class, 'showByToken']);
    Route::get('/results', [ApiPollVoteController::class, 'results']);
});

// === Polls — vote (auth requise) ===
Route::middleware('auth:sanctum')->prefix('v1/polls/by-token/{token}')->group(function () {
    Route::post('/votes', [ApiPollVoteController::class, 'store']);
    Route::get('/votes/me', [ApiPollVoteController::class, 'myVote']);
});

// === Polls — gestion (owner only, accès par ID) ===
Route::middleware('auth:sanctum')->prefix('v1/polls')->group(function () {
    Route::get('/', [ApiPollController::class, 'index']);
    Route::post('/', [ApiPollController::class, 'store']);
    Route::get('/{poll}', [ApiPollController::class, 'show']);
    Route::put('/{poll}', [ApiPollController::class, 'update']);
    Route::delete('/{poll}', [ApiPollController::class, 'destroy']);
    Route::post('/{poll}/start', [ApiPollController::class, 'start']);

    Route::post('/{poll}/options', [ApiPollOptionController::class, 'store']);
    Route::put('/{poll}/options/{option}', [ApiPollOptionController::class, 'update']);
    Route::delete('/{poll}/options/{option}', [ApiPollOptionController::class, 'destroy']);
});